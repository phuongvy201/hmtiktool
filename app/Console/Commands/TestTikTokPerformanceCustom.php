<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Services\TikTokShopPerformanceService;

class TestTikTokPerformanceCustom extends Command
{
    protected $signature = 'tiktok:test-performance-custom 
                            {--shop-id= : Shop ID để test}
                            {--start-date= : Ngày bắt đầu (YYYY-MM-DD)}
                            {--end-date= : Ngày kết thúc (YYYY-MM-DD)}
                            {--granularity=1D : Granularity (1D, 1W, 1M)}
                            {--currency=USD : Currency}';

    protected $description = 'Test TikTok Performance API với khoảng thời gian tùy chỉnh';

    public function handle()
    {
        $this->info('🧪 Testing TikTok Performance API với khoảng thời gian tùy chỉnh...');

        // Lấy tham số
        $shopId = $this->option('shop-id');
        $startDate = $this->option('start-date');
        $endDate = $this->option('end-date');
        $granularity = $this->option('granularity');
        $currency = $this->option('currency');

        // Validation
        if (!$startDate || !$endDate) {
            $this->error('❌ Vui lòng cung cấp start-date và end-date');
            $this->line('Ví dụ: php artisan tiktok:test-performance-custom --start-date=2025-09-01 --end-date=2025-09-15');
            return 1;
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $this->error('❌ Format ngày không đúng. Sử dụng YYYY-MM-DD');
            return 1;
        }

        // Validate date range
        if (strtotime($startDate) >= strtotime($endDate)) {
            $this->error('❌ start-date phải nhỏ hơn end-date');
            return 1;
        }

        // Lấy shop
        $shop = null;
        if ($shopId) {
            $shop = TikTokShop::with('integration')->find($shopId);
            if (!$shop) {
                $this->error("❌ Không tìm thấy shop với ID: {$shopId}");
                return 1;
            }
        } else {
            $shop = TikTokShop::with('integration')->where('status', 'active')->first();
            if (!$shop) {
                $this->error('❌ Không có TikTok shops active');
                return 1;
            }
        }

        $this->info("✅ Sử dụng shop: {$shop->shop_name} (ID: {$shop->id})");

        // Kiểm tra integration
        if (!$shop->integration || empty($shop->integration->access_token)) {
            $this->error('❌ Shop không có access token');
            return 1;
        }

        $this->info("✅ Integration: {$shop->integration->status}");
        $this->info("✅ Access Token: EXISTS");

        // Tạo filters
        $filters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'granularity' => $granularity,
            'with_comparison' => true,
            'currency' => $currency
        ];

        $this->info("📅 Khoảng thời gian: {$startDate} đến {$endDate}");
        $this->info("📊 Granularity: {$granularity}");
        $this->info("💰 Currency: {$currency}");

        // Test API
        $this->info('📊 Test TikTok Performance API...');
        
        try {
            $service = new TikTokShopPerformanceService();
            $result = $service->getShopPerformance($shop, $filters);

            if ($result['success']) {
                $this->info('✅ Performance Service thành công!');
                
                $data = $result['data'];
                if (isset($data['summary'])) {
                    $summary = $data['summary'];
                    $this->info("  Total GMV: $" . number_format($summary['total_gmv'], 2));
                    $this->info("  Total Orders: " . number_format($summary['total_orders']));
                    $this->info("  Total Buyers: " . number_format($summary['total_buyers']));
                    $this->info("  Total Impressions: " . number_format($summary['total_impressions']));
                    $this->info("  Total Page Views: " . number_format($summary['total_page_views']));
                    $this->info("  Avg Order Value: $" . number_format($summary['avg_order_value'], 2));
                    $this->info("  Conversion Rate: " . number_format($summary['conversion_rate'], 2) . "%");
                    $this->info("  Refund Rate: " . number_format($summary['refund_rate'], 2) . "%");
                }

                if (isset($data['current_period']) && is_array($data['current_period'])) {
                    $this->info("📈 Current Period Data Points: " . count($data['current_period']));
                }

                if (isset($data['comparison_period']) && is_array($data['comparison_period'])) {
                    $this->info("📊 Comparison Period Data Points: " . count($data['comparison_period']));
                }

                $this->info("💬 Message: " . $result['message']);
            } else {
                $this->error('❌ Performance Service thất bại!');
                $this->error("  Error: " . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Exception: ' . $e->getMessage());
            return 1;
        }

        $this->info('🎉 Test hoàn thành!');
        return 0;
    }
}
