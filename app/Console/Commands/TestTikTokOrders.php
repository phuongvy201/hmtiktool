<?php

namespace App\Console\Commands;

use App\Models\TikTokShop;
use App\Models\TikTokOrder;
use App\Services\TikTokOrderService;
use App\Services\TikTokShopService;
use Illuminate\Console\Command;
use Exception;

class TestTikTokOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:test-orders 
                            {--shop-id= : ID của shop cần test}
                            {--status= : Trạng thái đơn hàng cần lọc}
                            {--days=7 : Số ngày gần đây để lọc đơn hàng}
                            {--limit=20 : Số lượng đơn hàng tối đa}
                            {--sync : Đồng bộ đơn hàng vào database}
                            {--list-shops : Hiển thị danh sách shops}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test lấy danh sách đơn hàng từ TikTok Shop API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TIKTOK ORDERS API TEST ===');
        $this->newLine();

        try {
            // Hiển thị danh sách shops nếu được yêu cầu
            if ($this->option('list-shops')) {
                $this->listShops();
                return;
            }

            // Lấy shop để test
            $shop = $this->getShop();
            if (!$shop) {
                return;
            }

            $this->displayShopInfo($shop);

            // Khởi tạo services
            $orderService = new TikTokOrderService();
            $shopService = new TikTokShopService();

            // Lấy các options
            $status = $this->option('status');
            $days = (int) $this->option('days');
            $limit = (int) $this->option('limit');
            $shouldSync = $this->option('sync');

            // Chuẩn bị filters
            $filters = $this->prepareFilters($status, $days);

            $this->newLine();
            $this->info("🔍 Bắt đầu test với filters:");
            $this->displayFilters($filters);
            $this->newLine();

            // Test 1: Tìm kiếm đơn hàng từ API
            $this->testSearchOrders($orderService, $shop, $filters, $limit);

            // Test 2: Đồng bộ đơn hàng nếu được yêu cầu
            if ($shouldSync) {
                $this->testSyncOrders($orderService, $shop, $filters);
            }

            // Test 3: Lấy đơn hàng từ database
            $this->testStoredOrders($orderService, $shop, $filters, $limit);

            // Test 4: Thống kê đơn hàng
            $this->testOrderStatistics($shop);

            // Test 5: Sử dụng TikTokShopService
            $this->testShopService($shopService, $shop, $filters, $limit);

            $this->newLine();
            $this->info('🎉 Hoàn thành tất cả test!');
            $this->info('========================');
        } catch (Exception $e) {
            $this->error('❌ Lỗi: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
        }
    }

    /**
     * Hiển thị danh sách shops
     */
    private function listShops(): void
    {
        $this->info('📋 Danh sách TikTok Shops:');
        $this->newLine();

        $shops = TikTokShop::with('integration')->get();

        if ($shops->isEmpty()) {
            $this->warn('Không tìm thấy shop nào trong database');
            return;
        }

        $headers = ['ID', 'Shop Name', 'Shop ID', 'Integration', 'Status'];
        $rows = [];

        foreach ($shops as $shop) {
            $integration = $shop->integration;
            $integrationStatus = $integration ?
                ($integration->isActive() ? '✅ Active' : '❌ Inactive') :
                '❌ No Integration';

            $rows[] = [
                $shop->id,
                $shop->shop_name,
                $shop->shop_id,
                $integration ? $integration->app_name : 'N/A',
                $integrationStatus
            ];
        }

        $this->table($headers, $rows);
    }

    /**
     * Lấy shop để test
     */
    private function getShop(): ?TikTokShop
    {
        $shopId = $this->option('shop-id');

        if ($shopId) {
            $shop = TikTokShop::with('integration')->find($shopId);
            if (!$shop) {
                $this->error("❌ Không tìm thấy shop với ID: {$shopId}");
                return null;
            }
        } else {
            $shop = TikTokShop::with('integration')->first();
            if (!$shop) {
                $this->error('❌ Không tìm thấy TikTok Shop nào trong database');
                $this->info('💡 Sử dụng --list-shops để xem danh sách shops');
                return null;
            }
        }

        if (!$shop->integration) {
            $this->error('❌ Shop không có integration');
            return null;
        }

        if (!$shop->integration->isActive()) {
            $this->error('❌ Integration không hoạt động hoặc token đã hết hạn');
            return null;
        }

        return $shop;
    }

    /**
     * Hiển thị thông tin shop
     */
    private function displayShopInfo(TikTokShop $shop): void
    {
        $this->info('✅ Shop được chọn:');
        $this->line("   - ID: {$shop->id}");
        $this->line("   - Tên: {$shop->shop_name}");
        $this->line("   - Shop ID: {$shop->shop_id}");
        $this->line("   - Integration: {$shop->integration->app_name}");
        $this->line("   - Status: " . ($shop->integration->isActive() ? 'Active' : 'Inactive'));
    }

    /**
     * Chuẩn bị filters
     */
    private function prepareFilters(?string $status, int $days): array
    {
        $filters = [];

        if ($status) {
            $filters['order_status'] = $status;
        }

        $filters['create_time_ge'] = strtotime("-{$days} days");
        $filters['create_time_lt'] = time();

        return $filters;
    }

    /**
     * Hiển thị filters
     */
    private function displayFilters(array $filters): void
    {
        foreach ($filters as $key => $value) {
            if ($key === 'create_time_ge' || $key === 'create_time_lt') {
                $this->line("   - {$key}: " . date('Y-m-d H:i:s', $value));
            } else {
                $this->line("   - {$key}: {$value}");
            }
        }
    }

    /**
     * Test tìm kiếm đơn hàng từ API
     */
    private function testSearchOrders(TikTokOrderService $orderService, TikTokShop $shop, array $filters, int $limit): void
    {
        $this->info('🔍 Test 1: Tìm kiếm đơn hàng từ API');
        $this->line('----------------------------------------');

        $result = $orderService->searchOrders($shop, $filters, $limit);

        if ($result['success']) {
            $orderList = $result['data']['order_list'] ?? [];
            $this->info("✅ Tìm thấy " . count($orderList) . " đơn hàng");

            if (!empty($orderList)) {
                $this->newLine();
                $this->info('📋 Danh sách đơn hàng:');

                $headers = ['STT', 'Order ID', 'Status', 'Amount', 'Currency', 'Buyer', 'Created'];
                $rows = [];

                foreach ($orderList as $index => $order) {
                    $rows[] = [
                        $index + 1,
                        substr($order['order_id'] ?? 'N/A', 0, 20) . '...',
                        $order['order_status'] ?? 'N/A',
                        $order['order_amount'] ?? '0',
                        $order['currency'] ?? 'GBP',
                        $order['buyer_username'] ?? 'N/A',
                        isset($order['create_time']) ? date('Y-m-d H:i', $order['create_time']) : 'N/A'
                    ];
                }

                $this->table($headers, $rows);
            }
        } else {
            $this->error("❌ Lỗi: " . $result['message']);
        }

        $this->newLine();
    }

    /**
     * Test đồng bộ đơn hàng
     */
    private function testSyncOrders(TikTokOrderService $orderService, TikTokShop $shop, array $filters): void
    {
        $this->info('🔄 Test 2: Đồng bộ đơn hàng vào database');
        $this->line('----------------------------------------');

        $this->warn('⚠️  Bắt đầu đồng bộ đơn hàng (có thể mất vài phút)...');

        $result = $orderService->syncAllOrders($shop, $filters);

        if ($result['success']) {
            $this->info("✅ Đồng bộ thành công: " . $result['total_orders'] . " đơn hàng");
        } else {
            $this->error("❌ Lỗi đồng bộ: " . $result['message']);
        }

        $this->newLine();
    }

    /**
     * Test lấy đơn hàng từ database
     */
    private function testStoredOrders(TikTokOrderService $orderService, TikTokShop $shop, array $filters, int $limit): void
    {
        $this->info('💾 Test 3: Lấy đơn hàng từ database');
        $this->line('----------------------------------------');

        $result = $orderService->getStoredOrders($shop, array_merge($filters, ['limit' => $limit]));

        if ($result['success']) {
            $orders = $result['data'];
            $this->info("✅ Tìm thấy " . $orders->count() . " đơn hàng trong database");

            if ($orders->count() > 0) {
                $this->newLine();
                $this->info('📋 Đơn hàng đã lưu:');

                $headers = ['STT', 'Order ID', 'Status (VN)', 'Amount', 'Currency', 'Created', 'Synced'];
                $rows = [];

                foreach ($orders as $index => $order) {
                    $rows[] = [
                        $index + 1,
                        substr($order->order_id, 0, 20) . '...',
                        $order->status_in_vietnamese,
                        $order->order_amount,
                        $order->currency,
                        $order->create_time ? $order->create_time->format('Y-m-d H:i') : 'N/A',
                        $order->last_synced_at ? $order->last_synced_at->format('Y-m-d H:i') : 'N/A'
                    ];
                }

                $this->table($headers, $rows);
            }
        } else {
            $this->error("❌ Lỗi: " . $result['message']);
        }

        $this->newLine();
    }

    /**
     * Test thống kê đơn hàng
     */
    private function testOrderStatistics(TikTokShop $shop): void
    {
        $this->info('📊 Test 4: Thống kê đơn hàng');
        $this->line('----------------------------------------');

        $totalOrders = TikTokOrder::where('tiktok_shop_id', $shop->id)->count();
        $ordersByStatus = TikTokOrder::where('tiktok_shop_id', $shop->id)
            ->selectRaw('order_status, COUNT(*) as count, SUM(order_amount) as total_amount')
            ->groupBy('order_status')
            ->get();

        $this->info("✅ Tổng số đơn hàng: {$totalOrders}");

        if ($ordersByStatus->count() > 0) {
            $this->newLine();
            $this->info('📊 Phân bố theo trạng thái:');

            $headers = ['Status', 'Count', 'Total Amount'];
            $rows = [];

            foreach ($ordersByStatus as $status) {
                $rows[] = [
                    $status->order_status,
                    $status->count,
                    number_format($status->total_amount, 2) . ' ' . ($status->currency ?? 'GBP')
                ];
            }

            $this->table($headers, $rows);
        }

        $this->newLine();
    }

    /**
     * Test TikTokShopService
     */
    private function testShopService(TikTokShopService $shopService, TikTokShop $shop, array $filters, int $limit): void
    {
        $this->info('🏪 Test 5: Sử dụng TikTokShopService');
        $this->line('----------------------------------------');

        $result = $shopService->searchOrders(
            $shop->integration,
            $shop->id,
            $filters,
            $limit
        );

        if ($result['success']) {
            $orderList = $result['data']['order_list'] ?? [];
            $this->info("✅ TikTokShopService: Tìm thấy " . count($orderList) . " đơn hàng");
        } else {
            $this->error("❌ TikTokShopService: " . $result['message']);
        }

        $this->newLine();
    }
}
