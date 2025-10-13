<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Services\TikTokOrderService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncTikTokOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:sync-orders 
                            {--shop= : ID của shop cụ thể để sync}
                            {--status= : Trạng thái đơn hàng cần sync (ví dụ: AWAITING_SHIPMENT, IN_TRANSIT)}
                            {--hours=24 : Số giờ gần đây để sync (mặc định 24h)}
                            {--force : Bỏ qua kiểm tra integration status}
                            {--dry-run : Chỉ hiển thị thông tin, không thực hiện sync}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ đơn hàng từ TikTok Shop API theo lịch';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== BẮT ĐẦU SYNC ORDERS TỪ TIKTOK ===');

        $shopId = $this->option('shop');
        $status = $this->option('status');
        $hours = (int) $this->option('hours');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        try {
            // Lấy danh sách shops cần sync
            $shops = $this->getShopsToSync($shopId, $force);

            if ($shops->isEmpty()) {
                $this->warn('Không tìm thấy shop nào để sync');
                return Command::SUCCESS;
            }

            $this->info("Tìm thấy {$shops->count()} shop(s) để sync");

            // Tạo filters
            $filters = $this->buildFilters($status, $hours);

            $totalOrders = 0;
            $successCount = 0;
            $errorCount = 0;

            foreach ($shops as $shop) {
                $this->info("Đang sync shop: {$shop->shop_name} (ID: {$shop->id})");

                if ($dryRun) {
                    $this->info("DRY RUN - Không thực hiện sync thật");
                    continue;
                }

                $result = $this->syncShopOrders($shop, $filters);

                if ($result['success']) {
                    $successCount++;
                    $totalOrders += $result['total_orders'];
                    $this->info("✓ Sync thành công: {$result['total_orders']} đơn hàng");
                } else {
                    $errorCount++;
                    $this->error("✗ Sync thất bại: {$result['message']}");
                }

                // Nghỉ 2 giây giữa các shop để tránh rate limit
                sleep(2);
            }

            // Báo cáo kết quả
            $this->info("\n=== KẾT QUẢ SYNC ===");
            $this->info("Shops thành công: {$successCount}");
            $this->info("Shops thất bại: {$errorCount}");
            $this->info("Tổng đơn hàng: {$totalOrders}");

            Log::info('TikTok Orders Sync Completed', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'total_orders' => $totalOrders,
                'filters' => $filters
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Lỗi trong quá trình sync: {$e->getMessage()}");
            Log::error('TikTok Orders Sync Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        } finally {
            $this->info('=== KẾT THÚC SYNC ORDERS TỪ TIKTOK ===');
        }
    }

    /**
     * Lấy danh sách shops cần sync
     */
    private function getShopsToSync(?string $shopId, bool $force)
    {
        $query = TikTokShop::with(['integration']);

        if ($shopId) {
            $query->where('id', $shopId);
        }

        $shops = $query->get();

        if (!$force) {
            // Lọc bỏ các shop không có integration hoặc integration không hoạt động
            $shops = $shops->filter(function ($shop) {
                if (!$shop->integration) {
                    $this->warn("Shop {$shop->shop_name} không có integration");
                    return false;
                }

                if (!$shop->integration->isActive()) {
                    $this->warn("Shop {$shop->shop_name} integration không hoạt động");
                    return false;
                }

                return true;
            });
        }

        return $shops;
    }

    /**
     * Tạo filters cho việc sync
     */
    private function buildFilters(?string $status, int $hours): array
    {
        $filters = [];

        // Filter theo trạng thái
        if ($status) {
            $filters['order_status'] = $status;
        }

        // Filter theo thời gian (mặc định 24h gần đây)
        $startTime = Carbon::now()->subHours($hours)->timestamp;
        $filters['create_time_ge'] = $startTime;

        $this->info("Filters: " . json_encode($filters));

        return $filters;
    }

    /**
     * Sync orders cho một shop cụ thể
     */
    private function syncShopOrders(TikTokShop $shop, array $filters): array
    {
        try {
            $orderService = new TikTokOrderService();

            // Sử dụng syncAllOrders để lấy tất cả đơn hàng với pagination
            $result = $orderService->syncAllOrders($shop, $filters);

            if ($result['success']) {
                Log::info('Shop orders synced successfully', [
                    'shop_id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'total_orders' => $result['total_orders'] ?? 0,
                    'filters' => $filters
                ]);

                return [
                    'success' => true,
                    'total_orders' => $result['total_orders'] ?? 0,
                    'message' => 'Sync thành công'
                ];
            } else {
                Log::error('Shop orders sync failed', [
                    'shop_id' => $shop->id,
                    'shop_name' => $shop->shop_name,
                    'error' => $result['message'],
                    'filters' => $filters
                ]);

                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
        } catch (\Exception $e) {
            Log::error('Exception during shop orders sync', [
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
