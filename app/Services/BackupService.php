<?php

namespace App\Services;

use App\Models\BackupLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Exception;
use ZipArchive;

class BackupService
{
    protected string $backupPath;
    protected array $excludedTables;
    protected array $excludedColumns;

    public function __construct()
    {
        $this->backupPath = storage_path('app/backups');
        $this->excludedTables = [
            'migrations',
            'failed_jobs',
            'password_reset_tokens',
            'personal_access_tokens',
            'sessions',
        ];
        $this->excludedColumns = [
            'password',
            'remember_token',
            'email_verification_token',
        ];

        // Tạo thư mục backup nếu chưa tồn tại
        if (!file_exists($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }

    /**
     * Tạo backup database
     */
    public function createBackup(array $options = []): BackupLog
    {
        $startTime = now();
        $filename = $this->generateFilename();
        $filePath = $this->backupPath . '/' . $filename;

        // Tạo log entry
        $backupLog = BackupLog::create([
            'filename' => $filename,
            'type' => 'backup',
            'status' => 'in_progress',
            'description' => $options['description'] ?? 'Backup tự động',
            'file_path' => $filePath,
            'started_at' => $startTime,
            'created_by' => Auth::id(),
            'compression_type' => $options['compression_type'] ?? 'gzip',
            'is_encrypted' => $options['is_encrypted'] ?? false,
            'excluded_tables' => $options['excluded_tables'] ?? $this->excludedTables,
        ]);

        try {
            // Lấy danh sách bảng
            $tables = $this->getTables();
            $backupLog->update(['tables_list' => $tables]);

            // Tạo file SQL
            $sqlContent = $this->generateSqlBackup($tables, $options);

            // Nén file
            if (($options['compression_type'] ?? 'gzip') === 'gzip') {
                $filePath .= '.gz';
                $backupLog->update(['file_path' => $filePath]);
                file_put_contents('compress.zlib://' . $filePath, $sqlContent);
            } else {
                file_put_contents($filePath, $sqlContent);
            }

            // Mã hóa nếu cần
            if ($options['is_encrypted'] ?? false) {
                $this->encryptFile($filePath, $options['encryption_key'] ?? 'default_key');
            }

            // Cập nhật thông tin
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);
            $fileSize = filesize($filePath);

            $backupLog->update([
                'status' => 'success',
                'completed_at' => $endTime,
                'duration_seconds' => $duration,
                'file_size' => $fileSize,
                'tables_count' => count($tables),
                'records_count' => $this->countTotalRecords($tables),
            ]);

            Log::info('Backup created successfully', [
                'filename' => $filename,
                'size' => $fileSize,
                'duration' => $duration,
                'tables' => count($tables),
            ]);

            return $backupLog;
        } catch (Exception $e) {
            $backupLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_seconds' => $startTime->diffInSeconds(now()),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Backup failed', [
                'filename' => $filename,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Restore database từ backup
     */
    public function restoreBackup(BackupLog $backupLog, array $options = []): BackupLog
    {
        if (!$backupLog->fileExists()) {
            throw new Exception('Backup file not found');
        }

        $startTime = now();
        $filename = 'restore_' . $this->generateFilename();
        $filePath = $this->backupPath . '/' . $filename;

        // Tạo log entry cho restore
        $restoreLog = BackupLog::create([
            'filename' => $filename,
            'type' => 'restore',
            'status' => 'in_progress',
            'description' => $options['description'] ?? 'Restore từ backup: ' . $backupLog->filename,
            'file_path' => $filePath,
            'started_at' => $startTime,
            'created_by' => Auth::id(),
        ]);

        try {
            // Đọc file backup
            $sqlContent = $this->readBackupFile($backupLog->file_path, $backupLog->is_encrypted);

            // Thực hiện restore
            $this->executeSqlRestore($sqlContent, $options);

            // Cập nhật thông tin
            $endTime = now();
            $duration = $startTime->diffInSeconds($endTime);

            $restoreLog->update([
                'status' => 'success',
                'completed_at' => $endTime,
                'duration_seconds' => $duration,
            ]);

            Log::info('Restore completed successfully', [
                'from_backup' => $backupLog->filename,
                'duration' => $duration,
            ]);

            return $restoreLog;
        } catch (Exception $e) {
            $restoreLog->update([
                'status' => 'failed',
                'completed_at' => now(),
                'duration_seconds' => $startTime->diffInSeconds(now()),
                'error_message' => $e->getMessage(),
            ]);

            Log::error('Restore failed', [
                'from_backup' => $backupLog->filename,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Tạo backup cho các bảng cụ thể
     */
    public function createPartialBackup(array $tables, array $options = []): BackupLog
    {
        $options['tables'] = $tables;
        return $this->createBackup($options);
    }

    /**
     * Xóa backup cũ
     */
    public function cleanupOldBackups(int $daysToKeep = 30): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        $oldBackups = BackupLog::where('created_at', '<', $cutoffDate)
            ->where('type', 'backup')
            ->get();

        $deletedCount = 0;

        foreach ($oldBackups as $backup) {
            if ($backup->fileExists()) {
                unlink($backup->file_path);
            }
            $backup->delete();
            $deletedCount++;
        }

        Log::info('Cleanup old backups', [
            'deleted_count' => $deletedCount,
            'days_to_keep' => $daysToKeep,
        ]);

        return $deletedCount;
    }

    /**
     * Kiểm tra trạng thái backup
     */
    public function getBackupStatus(): array
    {
        $totalBackups = BackupLog::where('type', 'backup')->count();
        $successfulBackups = BackupLog::where('type', 'backup')->where('status', 'success')->count();
        $failedBackups = BackupLog::where('type', 'backup')->where('status', 'failed')->count();
        $totalSize = BackupLog::where('type', 'backup')
            ->where('status', 'success')
            ->sum(DB::raw('CAST(file_size AS BIGINT)'));

        $latestBackup = BackupLog::where('type', 'backup')
            ->where('status', 'success')
            ->latest()
            ->first();

        return [
            'total_backups' => $totalBackups,
            'successful_backups' => $successfulBackups,
            'failed_backups' => $failedBackups,
            'total_size' => $this->formatBytes($totalSize),
            'latest_backup' => $latestBackup,
            'backup_directory' => $this->backupPath,
            'available_space' => $this->getAvailableSpace(),
        ];
    }

    /**
     * Tạo filename
     */
    protected function generateFilename(): string
    {
        return 'hmtik_backup_' . now()->format('Y-m-d_H-i-s') . '_' . Str::random(8);
    }

    /**
     * Lấy danh sách bảng
     */
    public function getTables(): array
    {
        $tables = DB::select('SHOW TABLES');
        $tableNames = [];

        foreach ($tables as $table) {
            $tableName = array_values((array) $table)[0];
            if (!in_array($tableName, $this->excludedTables)) {
                $tableNames[] = $tableName;
            }
        }

        return $tableNames;
    }

    /**
     * Tạo SQL backup
     */
    protected function generateSqlBackup(array $tables, array $options): string
    {
        $sql = "-- HMTIK Database Backup\n";
        $sql .= "-- Generated: " . now()->toDateTimeString() . "\n";
        $sql .= "-- Version: " . config('app.version', '1.0.0') . "\n\n";

        foreach ($tables as $table) {
            $sql .= $this->backupTable($table, $options);
        }

        return $sql;
    }

    /**
     * Backup một bảng
     */
    protected function backupTable(string $table, array $options): string
    {
        $sql = "-- Table structure for table `{$table}`\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";

        // Lấy cấu trúc bảng
        $createTable = DB::select("SHOW CREATE TABLE `{$table}`")[0];
        $sql .= $createTable->{'Create Table'} . ";\n\n";

        // Lấy dữ liệu
        $records = DB::table($table)->get();
        if ($records->count() > 0) {
            $sql .= "-- Data for table `{$table}`\n";
            $sql .= "INSERT INTO `{$table}` VALUES\n";

            $values = [];
            foreach ($records as $record) {
                $rowValues = [];
                foreach ((array) $record as $value) {
                    if ($value === null) {
                        $rowValues[] = 'NULL';
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }
                $values[] = '(' . implode(', ', $rowValues) . ')';
            }

            $sql .= implode(",\n", $values) . ";\n\n";
        }

        return $sql;
    }

    /**
     * Đếm tổng số records
     */
    protected function countTotalRecords(array $tables): int
    {
        $total = 0;
        foreach ($tables as $table) {
            $total += DB::table($table)->count();
        }
        return $total;
    }

    /**
     * Mã hóa file
     */
    protected function encryptFile(string $filePath, string $key): void
    {
        $content = file_get_contents($filePath);
        $encrypted = openssl_encrypt($content, 'AES-256-CBC', $key, 0, substr(hash('sha256', $key), 0, 16));
        file_put_contents($filePath, $encrypted);
    }

    /**
     * Đọc file backup
     */
    protected function readBackupFile(string $filePath, bool $isEncrypted): string
    {
        $content = file_get_contents($filePath);

        if ($isEncrypted) {
            $content = openssl_decrypt($content, 'AES-256-CBC', 'default_key', 0, substr(hash('sha256', 'default_key'), 0, 16));
        }

        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'gz') {
            $content = gzdecode($content);
        }

        return $content;
    }

    /**
     * Thực hiện restore SQL
     */
    protected function executeSqlRestore(string $sqlContent, array $options): void
    {
        // Tắt foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Chia SQL thành các câu lệnh
        $statements = array_filter(array_map('trim', explode(';', $sqlContent)));

        foreach ($statements as $statement) {
            if (!empty($statement) && !str_starts_with($statement, '--')) {
                DB::statement($statement);
            }
        }

        // Bật lại foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Format bytes
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Lấy dung lượng còn trống
     */
    protected function getAvailableSpace(): string
    {
        $freeSpace = disk_free_space($this->backupPath);
        return $this->formatBytes($freeSpace);
    }
}
