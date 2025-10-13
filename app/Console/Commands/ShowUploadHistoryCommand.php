<?php

namespace App\Console\Commands;

use App\Models\TikTokProductUploadHistory;
use Illuminate\Console\Command;

class ShowUploadHistoryCommand extends Command
{
    protected $signature = 'tiktok:upload-history 
                            {--user= : Filter by user ID}
                            {--product= : Filter by product ID}
                            {--shop= : Filter by shop ID}
                            {--status= : Filter by status (success, failed, pending)}
                            {--days=30 : Number of days to look back}
                            {--limit=20 : Number of records to show}
                            {--format=table : Output format (table, json)}';

    protected $description = 'Hiển thị lịch sử upload sản phẩm lên TikTok Shop';

    public function handle()
    {
        $query = TikTokProductUploadHistory::query();

        // Apply filters
        if ($userId = $this->option('user')) {
            $query->byUser($userId);
        }

        if ($productId = $this->option('product')) {
            $query->byProduct($productId);
        }

        if ($shopId = $this->option('shop')) {
            $query->byShop($shopId);
        }

        if ($status = $this->option('status')) {
            $query->where('status', $status);
        }

        $days = (int) $this->option('days');
        $query->recent($days);

        $limit = (int) $this->option('limit');
        $histories = $query->with(['user', 'product', 'tiktokShop'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();

        if ($histories->isEmpty()) {
            $this->info('Không tìm thấy lịch sử upload nào.');
            return;
        }

        $format = $this->option('format');

        if ($format === 'json') {
            $this->outputJson($histories);
        } else {
            $this->outputTable($histories);
        }

        // Show summary
        $this->showSummary($histories);
    }

    private function outputTable($histories)
    {
        $headers = [
            'ID',
            'User',
            'Product',
            'Shop',
            'Status',
            'TikTok Product ID',
            'Created At',
            'Duration'
        ];

        $rows = $histories->map(function ($history) {
            return [
                $history->id,
                $history->user_name ?? 'N/A',
                substr($history->product_name, 0, 30) . '...',
                substr($history->shop_name, 0, 20) . '...',
                $this->getStatusBadge($history->status),
                $history->tiktok_product_id ?? 'N/A',
                $history->created_at->format('Y-m-d H:i:s'),
                $history->duration ?? 'N/A'
            ];
        });

        $this->table($headers, $rows);
    }

    private function outputJson($histories)
    {
        $data = $histories->map(function ($history) {
            return [
                'id' => $history->id,
                'user_id' => $history->user_id,
                'user_name' => $history->user_name,
                'product_id' => $history->product_id,
                'product_name' => $history->product_name,
                'shop_id' => $history->tiktok_shop_id,
                'shop_name' => $history->shop_name,
                'shop_cipher' => $history->shop_cipher,
                'status' => $history->status,
                'tiktok_product_id' => $history->tiktok_product_id,
                'error_message' => $history->error_message,
                'created_at' => $history->created_at->toISOString(),
                'uploaded_at' => $history->uploaded_at?->toISOString(),
                'duration' => $history->duration,
                'idempotency_key' => $history->idempotency_key,
            ];
        });

        $this->line(json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    private function getStatusBadge($status)
    {
        return match ($status) {
            'success' => '<fg=green>✓ Thành công</>',
            'failed' => '<fg=red>✗ Thất bại</>',
            'pending' => '<fg=yellow>⏳ Đang xử lý</>',
            default => '<fg=gray>? Không xác định</>'
        };
    }

    private function showSummary($histories)
    {
        $this->newLine();
        $this->info('📊 Tổng kết:');

        $total = $histories->count();
        $success = $histories->where('status', 'success')->count();
        $failed = $histories->where('status', 'failed')->count();
        $pending = $histories->where('status', 'pending')->count();

        $this->line("Tổng số: {$total}");
        $this->line("Thành công: <fg=green>{$success}</>");
        $this->line("Thất bại: <fg=red>{$failed}</>");
        $this->line("Đang xử lý: <fg=yellow>{$pending}</>");

        if ($total > 0) {
            $successRate = round(($success / $total) * 100, 1);
            $this->line("Tỷ lệ thành công: <fg=green>{$successRate}%</>");
        }
    }
}
