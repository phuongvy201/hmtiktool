<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupBackups extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup 
                            {--days=30 : Số ngày giữ lại backup}
                            {--dry-run : Chỉ hiển thị những gì sẽ bị xóa, không thực hiện xóa}
                            {--force : Bỏ qua xác nhận}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa backup cũ để tiết kiệm dung lượng';

    protected BackupService $backupService;

    public function __construct(BackupService $backupService)
    {
        parent::__construct();
        $this->backupService = $backupService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $daysToKeep = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info("Bắt đầu cleanup backup cũ (giữ lại {$daysToKeep} ngày)...");

        try {
            // Lấy thông tin backup sẽ bị xóa
            $cutoffDate = now()->subDays($daysToKeep);
            $oldBackups = \App\Models\BackupLog::where('created_at', '<', $cutoffDate)
                ->where('type', 'backup')
                ->get();

            if ($oldBackups->isEmpty()) {
                $this->info('✅ Không có backup cũ nào cần xóa.');
                return self::SUCCESS;
            }

            $this->warn("Tìm thấy {$oldBackups->count()} backup cũ sẽ bị xóa:");

            $this->table(
                ['ID', 'Filename', 'Kích thước', 'Ngày tạo', 'Trạng thái'],
                $oldBackups->map(function ($backup) {
                    return [
                        $backup->id,
                        $backup->filename,
                        $backup->formatted_file_size,
                        $backup->created_at->format('Y-m-d H:i:s'),
                        $backup->status,
                    ];
                })->toArray()
            );

            if ($dryRun) {
                $this->info('🔍 Dry run mode - Không có file nào bị xóa.');
                return self::SUCCESS;
            }

            if (!$force) {
                if (!$this->confirm('Bạn có chắc chắn muốn xóa các backup này?')) {
                    $this->info('❌ Đã hủy thao tác.');
                    return self::SUCCESS;
                }
            }

            // Thực hiện cleanup
            $deletedCount = $this->backupService->cleanupOldBackups($daysToKeep);

            $this->info("✅ Đã xóa thành công {$deletedCount} backup cũ.");

            Log::info('Backup cleanup completed', [
                'deleted_count' => $deletedCount,
                'days_to_keep' => $daysToKeep,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Không thể cleanup backup: ' . $e->getMessage());

            Log::error('Backup cleanup failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
