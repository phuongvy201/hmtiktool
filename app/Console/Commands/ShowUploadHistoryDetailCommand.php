<?php

namespace App\Console\Commands;

use App\Models\TikTokProductUploadHistory;
use Illuminate\Console\Command;

class ShowUploadHistoryDetailCommand extends Command
{
    protected $signature = 'tiktok:upload-history-detail {id : ID của lịch sử upload}';

    protected $description = 'Hiển thị chi tiết lịch sử upload sản phẩm lên TikTok Shop';

    public function handle()
    {
        $id = $this->argument('id');

        $history = TikTokProductUploadHistory::with(['user', 'product', 'tiktokShop'])
            ->find($id);

        if (!$history) {
            $this->error("Không tìm thấy lịch sử upload với ID: {$id}");
            return;
        }

        $this->info("📋 Chi tiết lịch sử upload #{$history->id}");
        $this->newLine();

        // Thông tin cơ bản
        $this->line("👤 <fg=cyan>Thông tin User:</>");
        $this->line("   ID: {$history->user_id}");
        $this->line("   Tên: {$history->user_name}");
        $this->newLine();

        $this->line("📦 <fg=cyan>Thông tin Sản phẩm:</>");
        $this->line("   ID: {$history->product_id}");
        $this->line("   Tên: {$history->product_name}");
        $this->newLine();

        $this->line("🏪 <fg=cyan>Thông tin TikTok Shop:</>");
        $this->line("   ID: {$history->tiktok_shop_id}");
        $this->line("   Tên: {$history->shop_name}");
        $this->line("   Cipher: {$history->shop_cipher}");
        $this->newLine();

        // Trạng thái
        $this->line("📊 <fg=cyan>Trạng thái:</>");
        $statusBadge = match ($history->status) {
            'success' => '<fg=green>✓ Thành công</>',
            'failed' => '<fg=red>✗ Thất bại</>',
            'pending' => '<fg=yellow>⏳ Đang xử lý</>',
            default => '<fg=gray>? Không xác định</>'
        };
        $this->line("   Trạng thái: {$statusBadge}");

        if ($history->tiktok_product_id) {
            $this->line("   TikTok Product ID: <fg=green>{$history->tiktok_product_id}</>");
        }

        if ($history->duration) {
            $this->line("   Thời gian xử lý: {$history->duration}");
        }
        $this->newLine();

        // Thời gian
        $this->line("⏰ <fg=cyan>Thời gian:</>");
        $this->line("   Tạo lúc: {$history->created_at->format('Y-m-d H:i:s')}");
        if ($history->uploaded_at) {
            $this->line("   Hoàn thành lúc: {$history->uploaded_at->format('Y-m-d H:i:s')}");
        }
        $this->newLine();

        // Lỗi (nếu có)
        if ($history->error_message) {
            $this->line("❌ <fg=cyan>Lỗi:</>");
            $this->line("   {$history->error_message}");
            $this->newLine();
        }

        // Idempotency key
        if ($history->idempotency_key) {
            $this->line("🔑 <fg=cyan>Idempotency Key:</>");
            $this->line("   {$history->idempotency_key}");
            $this->newLine();
        }

        // TikTok SKUs (nếu có)
        if ($history->tiktok_skus && is_array($history->tiktok_skus)) {
            $count = count($history->tiktok_skus);
            $this->line("📋 <fg=cyan>TikTok SKUs ({$count}):</>");
            foreach ($history->tiktok_skus as $index => $sku) {
                $this->line("   " . ($index + 1) . ". ID: {$sku['id']} - SKU: {$sku['seller_sku']}");
            }
            $this->newLine();
        }

        // Response data (nếu có)
        if ($history->response_data) {
            $this->line("📄 <fg=cyan>Response Data:</>");
            $this->line(json_encode($history->response_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            $this->newLine();
        }

        // Request data (nếu có và user muốn xem)
        if ($this->confirm('Bạn có muốn xem Request Data (có thể rất dài)?', false)) {
            if ($history->request_data) {
                $this->line("📤 <fg=cyan>Request Data:</>");
                $this->line(json_encode($history->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            } else {
                $this->line("Không có Request Data.");
            }
        }
    }
}
