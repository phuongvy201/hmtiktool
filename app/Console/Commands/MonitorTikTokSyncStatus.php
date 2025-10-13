<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Models\TikTokOrder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class MonitorTikTokSyncStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:monitor-sync 
                            {--shop= : ID của shop cụ thể để monitor}
                            {--hours=24 : Số giờ gần đây để kiểm tra}
                            {--alert-threshold=2 : Số giờ không sync để cảnh báo}
                            {--send-alerts : Gửi cảnh báo nếu có vấn đề}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor trạng thái sync TikTok orders và cảnh báo nếu có vấn đề';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== MONITORING TIKTOK SYNC STATUS ===');

        $shopId = $this->option('shop');
        $hours = (int) $this->option('hours');
        $alertThreshold = (int) $this->option('alert-threshold');
        $sendAlerts = $this->option('send-alerts');

        try {
            // Lấy danh sách shops
            $shops = $this->getShopsToMonitor($shopId);

            if ($shops->isEmpty()) {
                $this->warn('Không tìm thấy shop nào để monitor');
                return Command::SUCCESS;
            }

            $this->info("Monitoring {$shops->count()} shop(s)");

            $totalOrders = 0;
            $totalShopsWithOrders = 0;
            $alerts = [];

            foreach ($shops as $shop) {
                $shopStats = $this->getShopSyncStats($shop, $hours);
                $totalOrders += $shopStats['total_orders'];

                if ($shopStats['total_orders'] > 0) {
                    $totalShopsWithOrders++;
                }

                // Hiển thị thống kê shop
                $this->displayShopStats($shop, $shopStats);

                // Kiểm tra cảnh báo
                $shopAlerts = $this->checkShopAlerts($shop, $shopStats, $alertThreshold);
                $alerts = array_merge($alerts, $shopAlerts);
            }

            // Hiển thị tổng kết
            $this->displaySummaryStats($totalOrders, $totalShopsWithOrders, $shops->count());

            // Xử lý cảnh báo
            if (!empty($alerts)) {
                $this->displayAlerts($alerts);

                if ($sendAlerts) {
                    $this->sendAlerts($alerts);
                }
            } else {
                $this->info('✓ Không có cảnh báo nào');
            }

            // Lưu báo cáo
            $this->saveMonitoringReport($shops, $totalOrders, $alerts);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Lỗi trong quá trình monitoring: {$e->getMessage()}");
            Log::error('TikTok Sync Monitoring Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return Command::FAILURE;
        } finally {
            $this->info('=== KẾT THÚC MONITORING ===');
        }
    }

    /**
     * Lấy danh sách shops cần monitor
     */
    private function getShopsToMonitor(?string $shopId)
    {
        $query = TikTokShop::with(['integration', 'orders']);

        if ($shopId) {
            $query->where('id', $shopId);
        }

        return $query->get();
    }

    /**
     * Lấy thống kê sync cho một shop
     */
    private function getShopSyncStats(TikTokShop $shop, int $hours): array
    {
        $cutoffTime = Carbon::now()->subHours($hours);

        // Đếm orders được sync trong khoảng thời gian
        $recentOrders = $shop->orders()
            ->where('last_synced_at', '>=', $cutoffTime)
            ->count();

        // Đếm orders mới được tạo
        $newOrders = $shop->orders()
            ->where('create_time', '>=', $cutoffTime)
            ->count();

        // Lấy order cuối cùng được sync
        $lastSyncOrder = $shop->orders()
            ->whereNotNull('last_synced_at')
            ->orderBy('last_synced_at', 'desc')
            ->first();

        // Kiểm tra lần sync cuối cùng
        $lastSyncTime = $lastSyncOrder ? $lastSyncOrder->last_synced_at : null;
        $hoursSinceLastSync = $lastSyncTime ?
            Carbon::now()->diffInHours($lastSyncTime) : null;

        // Kiểm tra cache để xem job có đang chạy không
        $isJobRunning = Cache::has("tiktok_sync_last_run_{$shop->id}");

        return [
            'total_orders' => $shop->orders()->count(),
            'recent_synced_orders' => $recentOrders,
            'new_orders' => $newOrders,
            'last_sync_time' => $lastSyncTime,
            'hours_since_last_sync' => $hoursSinceLastSync,
            'is_job_running' => $isJobRunning,
            'integration_active' => $shop->integration?->isActive() ?? false,
            'last_order_created' => $shop->orders()->latest('create_time')->first()?->create_time
        ];
    }

    /**
     * Hiển thị thống kê của một shop
     */
    private function displayShopStats(TikTokShop $shop, array $stats): void
    {
        $this->line("\n📊 Shop: {$shop->shop_name} (ID: {$shop->id})");
        $this->line("   Tổng orders: {$stats['total_orders']}");
        $this->line("   Orders sync gần đây: {$stats['recent_synced_orders']}");
        $this->line("   Orders mới: {$stats['new_orders']}");
        $this->line("   Lần sync cuối: " . ($stats['last_sync_time'] ?? 'Chưa có'));
        $this->line("   Giờ từ lần sync cuối: " . ($stats['hours_since_last_sync'] ?? 'N/A'));
        $this->line("   Job đang chạy: " . ($stats['is_job_running'] ? 'Có' : 'Không'));
        $this->line("   Integration active: " . ($stats['integration_active'] ? 'Có' : 'Không'));
    }

    /**
     * Kiểm tra cảnh báo cho một shop
     */
    private function checkShopAlerts(TikTokShop $shop, array $stats, int $alertThreshold): array
    {
        $alerts = [];

        // Cảnh báo nếu không sync quá lâu
        if (
            $stats['hours_since_last_sync'] !== null &&
            $stats['hours_since_last_sync'] > $alertThreshold
        ) {
            $alerts[] = [
                'type' => 'sync_stale',
                'level' => 'warning',
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'message' => "Không sync trong {$stats['hours_since_last_sync']} giờ",
                'hours_since_sync' => $stats['hours_since_last_sync']
            ];
        }

        // Cảnh báo nếu integration không hoạt động
        if (!$stats['integration_active']) {
            $alerts[] = [
                'type' => 'integration_inactive',
                'level' => 'error',
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'message' => 'Integration không hoạt động'
            ];
        }

        // Cảnh báo nếu có orders mới nhưng không sync
        if ($stats['new_orders'] > 0 && $stats['recent_synced_orders'] == 0) {
            $alerts[] = [
                'type' => 'new_orders_not_synced',
                'level' => 'warning',
                'shop_id' => $shop->id,
                'shop_name' => $shop->shop_name,
                'message' => "Có {$stats['new_orders']} orders mới nhưng chưa sync",
                'new_orders_count' => $stats['new_orders']
            ];
        }

        return $alerts;
    }

    /**
     * Hiển thị tổng kết
     */
    private function displaySummaryStats(int $totalOrders, int $shopsWithOrders, int $totalShops): void
    {
        $this->info("\n📈 TỔNG KẾT:");
        $this->info("   Tổng orders: {$totalOrders}");
        $this->info("   Shops có orders: {$shopsWithOrders}/{$totalShops}");
        $this->info("   Tỷ lệ shops hoạt động: " . round(($shopsWithOrders / $totalShops) * 100, 1) . "%");
    }

    /**
     * Hiển thị cảnh báo
     */
    private function displayAlerts(array $alerts): void
    {
        $this->warn("\n⚠️  CẢNH BÁO:");

        foreach ($alerts as $alert) {
            $icon = $alert['level'] === 'error' ? '❌' : '⚠️';
            $this->line("{$icon} {$alert['shop_name']}: {$alert['message']}");
        }
    }

    /**
     * Gửi cảnh báo (có thể mở rộng để gửi email, Slack, etc.)
     */
    private function sendAlerts(array $alerts): void
    {
        // Log cảnh báo
        Log::warning('TikTok Sync Alerts', [
            'alerts' => $alerts,
            'alert_count' => count($alerts)
        ]);

        $this->info("✓ Đã gửi {count($alerts)} cảnh báo");
    }

    /**
     * Lưu báo cáo monitoring
     */
    private function saveMonitoringReport($shops, int $totalOrders, array $alerts): void
    {
        $report = [
            'timestamp' => Carbon::now()->toISOString(),
            'total_shops' => $shops->count(),
            'total_orders' => $totalOrders,
            'alert_count' => count($alerts),
            'alerts' => $alerts
        ];

        // Lưu vào cache để có thể truy cập từ web interface
        Cache::put('tiktok_sync_monitoring_report', $report, 3600); // 1 giờ

        Log::info('TikTok Sync Monitoring Report Generated', $report);
    }
}
