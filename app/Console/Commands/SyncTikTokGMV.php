<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Services\TikTokShopPerformanceService;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SyncTikTokGMV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:sync-gmv 
                            {--shop= : ID của shop cụ thể để sync}
                            {--days=7 : Số ngày gần đây để sync (mặc định 7 ngày)}
                            {--all : Sync tất cả shops}
                            {--force : Bỏ qua kiểm tra integration status}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đồng bộ dữ liệu GMV (Gross Merchandise Value) từ TikTok Shop Performance API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== BẮT ĐẦU SYNC GMV TỪ TIKTOK ===');

        $shopId = $this->option('shop');
        $days = (int) $this->option('days');
        $all = $this->option('all');
        $force = $this->option('force');

        try {
            // Lấy danh sách shops cần sync
            $shops = $this->getShopsToSync($shopId, $all, $force);

            if ($shops->isEmpty()) {
                $this->warn('Không tìm thấy shop nào để sync');
                return Command::SUCCESS;
            }

            $this->info("Tìm thấy {$shops->count()} shop(s) để sync GMV");

            // Tính toán ngày bắt đầu và kết thúc
            $endDate = Carbon::now()->format('Y-m-d');
            $startDate = Carbon::now()->subDays($days)->format('Y-m-d');

            $this->info("Khoảng thời gian: {$startDate} đến {$endDate} ({$days} ngày)");

            $performanceService = new TikTokShopPerformanceService();
            $successCount = 0;
            $errorCount = 0;

            foreach ($shops as $shop) {
                $this->info("Đang sync GMV cho shop: {$shop->shop_name} (ID: {$shop->id})");

                try {
                    $filters = [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'granularity' => '1D',
                        'with_comparison' => true,
                        'currency' => 'USD'
                    ];

                    $result = $performanceService->getShopPerformance($shop, $filters);

                    if ($result['success']) {
                        $successCount++;
                        $summary = $result['data']['summary'] ?? [];
                        $totalGMV = $summary['total_gmv'] ?? 0;
                        $this->info("✓ Sync thành công: GMV = $" . number_format($totalGMV, 2));
                    } else {
                        $errorCount++;
                        $this->error("✗ Sync thất bại: {$result['message']}");
                        Log::error('GMV sync failed', [
                            'shop_id' => $shop->id,
                            'shop_name' => $shop->shop_name,
                            'error' => $result['message']
                        ]);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("✗ Lỗi: {$e->getMessage()}");
                    Log::error('GMV sync exception', [
                        'shop_id' => $shop->id,
                        'shop_name' => $shop->shop_name,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

                // Nghỉ 1 giây giữa các shop để tránh rate limit
                if ($shops->count() > 1) {
                    sleep(1);
                }
            }

            // Báo cáo kết quả
            $this->info("\n=== KẾT QUẢ SYNC GMV ===");
            $this->info("Shops thành công: {$successCount}");
            $this->info("Shops thất bại: {$errorCount}");

            Log::info('TikTok GMV Sync Completed', [
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'days' => $days,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Lỗi trong quá trình sync GMV: {$e->getMessage()}");
            Log::error('TikTok GMV Sync Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        } finally {
            $this->info('=== KẾT THÚC SYNC GMV TỪ TIKTOK ===');
        }
    }

    /**
     * Lấy danh sách shops cần sync
     */
    private function getShopsToSync(?string $shopId, bool $all, bool $force)
    {
        $query = TikTokShop::with(['integration']);

        if ($shopId) {
            $query->where('id', $shopId);
        }

        $shops = $query->get();

        if (!$force && !$all) {
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
}
