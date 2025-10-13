<?php

namespace App\Console\Commands;

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;
use Illuminate\Console\Command;
use Exception;

class QuickTestTikTokOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:quick-test 
                            {--shop-id= : ID của shop cần test}
                            {--status=UNPAID : Trạng thái đơn hàng}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test nhanh lấy danh sách đơn hàng từ TikTok Shop API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 QUICK TEST TIKTOK ORDERS API');
        $this->newLine();

        try {
            // Lấy shop
            $shop = $this->getShop();
            if (!$shop) {
                return;
            }

            $this->info("✅ Testing shop: {$shop->shop_name} (ID: {$shop->id})");

            // Khởi tạo service
            $orderService = new TikTokOrderService();

            // Lấy options
            $status = $this->option('status') ?: 'UNPAID';
            $shopId = $this->option('shop-id');

            // Chuẩn bị filters
            $filters = [
                'order_status' => $status,
                'create_time_ge' => strtotime('-7 days'),
                'create_time_lt' => time()
            ];

            $this->newLine();
            $this->info("🔍 Tìm kiếm đơn hàng với status: {$status}");

            // Gọi API
            $result = $orderService->searchOrders($shop, $filters, 10);

            if ($result['success']) {
                $orderList = $result['data']['order_list'] ?? [];
                $this->info("✅ Tìm thấy " . count($orderList) . " đơn hàng");

                if (!empty($orderList)) {
                    $this->newLine();
                    foreach ($orderList as $index => $order) {
                        $this->line(sprintf(
                            "%d. Order ID: %s | Status: %s | Amount: %s %s | Buyer: %s",
                            $index + 1,
                            substr($order['order_id'] ?? 'N/A', 0, 20) . '...',
                            $order['order_status'] ?? 'N/A',
                            $order['order_amount'] ?? '0',
                            $order['currency'] ?? 'GBP',
                            $order['buyer_username'] ?? 'N/A'
                        ));
                    }
                } else {
                    $this->warn('Không có đơn hàng nào tìm thấy');
                }
            } else {
                $this->error("❌ Lỗi: " . $result['message']);
            }

            $this->newLine();
            $this->info('🎉 Test hoàn thành!');
        } catch (Exception $e) {
            $this->error('❌ Lỗi: ' . $e->getMessage());
        }
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
                $this->info('💡 Sử dụng --shop-id=1 để chỉ định shop cụ thể');
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
}
