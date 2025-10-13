<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create 
                            {--description= : Mô tả cho backup}
                            {--compression=gzip : Loại nén (gzip, none)}
                            {--encrypt : Mã hóa backup}
                            {--key= : Khóa mã hóa}
                            {--tables=* : Danh sách bảng cụ thể để backup}
                            {--exclude=* : Danh sách bảng loại trừ}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo backup database cho HMTIK';

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
        $this->info('Bắt đầu tạo backup database...');

        try {
            $options = [
                'description' => $this->option('description') ?? 'Backup tự động từ command line',
                'compression_type' => $this->option('compression'),
                'is_encrypted' => $this->option('encrypt'),
                'encryption_key' => $this->option('key'),
                'excluded_tables' => $this->option('exclude'),
            ];

            if ($this->option('tables')) {
                $backup = $this->backupService->createPartialBackup($this->option('tables'), $options);
            } else {
                $backup = $this->backupService->createBackup($options);
            }

            $this->info('✅ Backup đã được tạo thành công!');
            $this->table(
                ['Thông tin', 'Giá trị'],
                [
                    ['Filename', $backup->filename],
                    ['Kích thước', $backup->formatted_file_size],
                    ['Số bảng', $backup->tables_count],
                    ['Số records', $backup->records_count],
                    ['Thời gian', $backup->formatted_duration],
                    ['Trạng thái', $backup->status],
                ]
            );

            Log::info('Backup created via command line', [
                'filename' => $backup->filename,
                'size' => $backup->file_size,
                'duration' => $backup->duration_seconds,
            ]);

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Không thể tạo backup: ' . $e->getMessage());

            Log::error('Backup command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return self::FAILURE;
        }
    }
}
