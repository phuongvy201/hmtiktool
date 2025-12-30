<?php

namespace App\Http\Controllers;

use App\Models\BackupLog;
use App\Services\BackupService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Exception;

class BackupController extends Controller
{
    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        $this->middleware('auth');
        $this->middleware('role:system-admin');
        $this->backupService = $backupService;
    }

    /**
     * Display backup management page
     */
    public function index(): View
    {
        $backups = BackupLog::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        $status = $this->backupService->getBackupStatus();

        return view('backups.index', compact('backups', 'status'));
    }

    /**
     * Create new backup
     */
    public function create(): View
    {
        $tables = $this->backupService->getTables();
        $status = $this->backupService->getBackupStatus();

        return view('backups.create', compact('tables', 'status'));
    }

    /**
     * Save new backup
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'compression_type' => 'in:gzip,none',
            'is_encrypted' => 'boolean',
            'encryption_key' => 'required_if:is_encrypted,1|string|min:8',
            'excluded_tables' => 'array',
            'excluded_tables.*' => 'string|exists:information_schema.tables,table_name',
        ]);

        try {
            $options = [
                'description' => $request->description,
                'compression_type' => $request->compression_type ?? 'gzip',
                'is_encrypted' => $request->boolean('is_encrypted'),
                'encryption_key' => $request->encryption_key,
                'excluded_tables' => $request->excluded_tables ?? [],
            ];

            $backup = $this->backupService->createBackup($options);

            return redirect()->route('backups.index')
                ->with('success', 'Backup created successfully: ' . $backup->filename);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Unable to create backup: ' . $e->getMessage()]);
        }
    }

    /**
     * Display backup details
     */
    public function show(BackupLog $backup): View
    {
        $backup->load('creator');

        return view('backups.show', compact('backup'));
    }

    /**
     * Download backup file
     */
    public function download(BackupLog $backup)
    {
        if (!$backup->fileExists()) {
            return back()->withErrors(['error' => 'Backup file does not exist']);
        }

        $filename = $backup->filename;
        if (pathinfo($backup->file_path, PATHINFO_EXTENSION) === 'gz') {
            $filename .= '.gz';
        }

        return response()->download($backup->file_path, $filename);
    }

    /**
     * Restore from backup
     */
    public function restore(BackupLog $backup, Request $request): RedirectResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'confirm_restore' => 'required|accepted',
        ]);

        if (!$backup->isCompleted()) {
            return back()->withErrors(['error' => 'Can only restore from completed backup']);
        }

        try {
            $options = [
                'description' => $request->description ?? 'Restore from backup: ' . $backup->filename,
            ];

            $restoreLog = $this->backupService->restoreBackup($backup, $options);

            return redirect()->route('backups.index')
                        ->with('success', 'Restore completed successfully from backup: ' . $backup->filename);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Unable to restore: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete backup
     */
    public function destroy(BackupLog $backup): RedirectResponse
    {
        try {
            if ($backup->fileExists()) {
                unlink($backup->file_path);
            }

            $backup->delete();

            return redirect()->route('backups.index')
                ->with('success', 'Backup deleted successfully');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Unable to delete backup: ' . $e->getMessage()]);
        }
    }

    /**
     * Cleanup old backups
     */
    public function cleanup(Request $request): RedirectResponse
    {
        $request->validate([
            'days_to_keep' => 'required|integer|min:1|max:365',
        ]);

        try {
            $deletedCount = $this->backupService->cleanupOldBackups($request->days_to_keep);

            return redirect()->route('backups.index')
                ->with('success', "Deleted {$deletedCount} old backups (keeping {$request->days_to_keep} days)");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Unable to cleanup: ' . $e->getMessage()]);
        }
    }

    /**
     * Create automatic backup
     */
    public function autoBackup(): RedirectResponse
    {
        try {
            $backup = $this->backupService->createBackup([
                'description' => 'Daily automatic backup',
                'compression_type' => 'gzip',
                'is_encrypted' => false,
            ]);

            return redirect()->route('backups.index')
                ->with('success', 'Automatic backup created: ' . $backup->filename);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Unable to create automatic backup: ' . $e->getMessage()]);
        }
    }

    /**
            * Check backup status
     */
    public function status(): View
    {
        $status = $this->backupService->getBackupStatus();
        $recentBackups = BackupLog::with('creator')
            ->where('type', 'backup')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('backups.status', compact('status', 'recentBackups'));
    }

    /**
     * Export backup list
     */
    public function export(Request $request)
    {
        $backups = BackupLog::with('creator')
            ->when($request->type, function ($query, $type) {
                return $query->where('type', $type);
            })
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                return $query->whereDate('created_at', '>=', $date);
            })
            ->when($request->date_to, function ($query, $date) {
                return $query->whereDate('created_at', '<=', $date);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'backup_logs_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($backups) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'ID',
                'Filename',
                'Type',
                'Status',
                'Description',
                'File Size',
                'Tables Count',
                'Records Count',
                'Duration',
                'Created By',
                'Created At'
            ]);

            // Data
            foreach ($backups as $backup) {
                fputcsv($file, [
                    $backup->id,
                    $backup->filename,
                    $backup->type,
                    $backup->status,
                    $backup->description,
                    $backup->formatted_file_size,
                    $backup->tables_count,
                    $backup->records_count,
                    $backup->formatted_duration,
                    $backup->creator?->name ?? 'System',
                    $backup->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
