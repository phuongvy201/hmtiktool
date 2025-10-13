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
     * Hiển thị trang quản lý backup
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
     * Tạo backup mới
     */
    public function create(): View
    {
        $tables = $this->backupService->getTables();
        $status = $this->backupService->getBackupStatus();

        return view('backups.create', compact('tables', 'status'));
    }

    /**
     * Lưu backup mới
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
                ->with('success', 'Backup đã được tạo thành công: ' . $backup->filename);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Không thể tạo backup: ' . $e->getMessage()]);
        }
    }

    /**
     * Hiển thị chi tiết backup
     */
    public function show(BackupLog $backup): View
    {
        $backup->load('creator');

        return view('backups.show', compact('backup'));
    }

    /**
     * Tải xuống file backup
     */
    public function download(BackupLog $backup)
    {
        if (!$backup->fileExists()) {
            return back()->withErrors(['error' => 'File backup không tồn tại']);
        }

        $filename = $backup->filename;
        if (pathinfo($backup->file_path, PATHINFO_EXTENSION) === 'gz') {
            $filename .= '.gz';
        }

        return response()->download($backup->file_path, $filename);
    }

    /**
     * Restore từ backup
     */
    public function restore(BackupLog $backup, Request $request): RedirectResponse
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
            'confirm_restore' => 'required|accepted',
        ]);

        if (!$backup->isCompleted()) {
            return back()->withErrors(['error' => 'Chỉ có thể restore từ backup đã hoàn thành']);
        }

        try {
            $options = [
                'description' => $request->description ?? 'Restore từ backup: ' . $backup->filename,
            ];

            $restoreLog = $this->backupService->restoreBackup($backup, $options);

            return redirect()->route('backups.index')
                ->with('success', 'Restore đã hoàn thành thành công từ backup: ' . $backup->filename);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Không thể restore: ' . $e->getMessage()]);
        }
    }

    /**
     * Xóa backup
     */
    public function destroy(BackupLog $backup): RedirectResponse
    {
        try {
            if ($backup->fileExists()) {
                unlink($backup->file_path);
            }

            $backup->delete();

            return redirect()->route('backups.index')
                ->with('success', 'Backup đã được xóa thành công');
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Không thể xóa backup: ' . $e->getMessage()]);
        }
    }

    /**
     * Cleanup backup cũ
     */
    public function cleanup(Request $request): RedirectResponse
    {
        $request->validate([
            'days_to_keep' => 'required|integer|min:1|max:365',
        ]);

        try {
            $deletedCount = $this->backupService->cleanupOldBackups($request->days_to_keep);

            return redirect()->route('backups.index')
                ->with('success', "Đã xóa {$deletedCount} backup cũ (giữ lại {$request->days_to_keep} ngày)");
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Không thể cleanup: ' . $e->getMessage()]);
        }
    }

    /**
     * Tạo backup tự động
     */
    public function autoBackup(): RedirectResponse
    {
        try {
            $backup = $this->backupService->createBackup([
                'description' => 'Backup tự động hàng ngày',
                'compression_type' => 'gzip',
                'is_encrypted' => false,
            ]);

            return redirect()->route('backups.index')
                ->with('success', 'Backup tự động đã được tạo: ' . $backup->filename);
        } catch (Exception $e) {
            return back()->withErrors(['error' => 'Không thể tạo backup tự động: ' . $e->getMessage()]);
        }
    }

    /**
     * Kiểm tra trạng thái backup
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
     * Export danh sách backup
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
