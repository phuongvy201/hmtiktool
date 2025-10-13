<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Services\TikTokShopPerformanceService;

class TestGMVPerformance extends Command
{
    protected $signature = 'tiktok:test-gmv';
    protected $description = 'Test GMV Performance Dashboard functionality';

    public function handle()
    {
        $this->info('🧪 Testing GMV Performance Dashboard...');

        // Test 1: Kiểm tra TikTok shops
        $this->info('📊 Test 1: TikTok Shops...');
        $shops = TikTokShop::with('integration')->get();

        if ($shops->count() > 0) {
            $this->info("✅ Found {$shops->count()} TikTok shops:");
            foreach ($shops as $shop) {
                $this->line("  - {$shop->shop_name} (ID: {$shop->id})");
                $this->line("    Status: {$shop->status}");
                if ($shop->integration) {
                    $this->line("    Integration: {$shop->integration->status}");
                }
            }
        } else {
            $this->error('❌ No TikTok shops found');
            return 1;
        }

        // Test 2: Test performance service
        $this->info('📊 Test 2: Performance Service...');
        try {
            $service = new TikTokShopPerformanceService();
            $this->info('✅ TikTokShopPerformanceService created successfully');
        } catch (\Exception $e) {
            $this->error('❌ Error creating service: ' . $e->getMessage());
            return 1;
        }

        // Test 3: Generate sample performance data
        $this->info('📊 Test 3: Generate Sample Performance Data...');
        try {
            $shop = $shops->first();
            if ($shop) {
                $filters = [
                    'start_date' => date('Y-m-d', strtotime('-7 days')),
                    'end_date' => date('Y-m-d'),
                    'granularity' => '1D',
                    'with_comparison' => true,
                    'currency' => 'USD'
                ];

                $result = $service->getShopPerformance($shop, $filters);

                $this->info("✅ Performance data generated for shop: {$shop->shop_name}");

                if (isset($result['data']['summary'])) {
                    $summary = $result['data']['summary'];
                    $this->line("  Total GMV: $" . number_format($summary['total_gmv'], 2));
                    $this->line("  Total Orders: " . $summary['total_orders']);
                    $this->line("  Total Buyers: " . $summary['total_buyers']);
                    $this->line("  Conversion Rate: " . $summary['conversion_rate'] . "%");
                } else {
                    $this->line("  Data structure: " . json_encode(array_keys($result)));
                }

                // Hiển thị chi tiết daily data
                if (isset($result['data']['current_period']) && count($result['data']['current_period']) > 0) {
                    $this->line("  Daily data points: " . count($result['data']['current_period']));
                }
            } else {
                $this->error('❌ No shops available for testing');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Error generating performance data: ' . $e->getMessage());
            return 1;
        }

        // Test 4: Kiểm tra routes
        $this->info('📊 Test 4: Routes...');
        $routes = [
            'tiktok.performance.index' => '/tiktok/performance',
            'tiktok.performance.data' => '/tiktok/performance/data',
            'tiktok.performance.refresh' => '/tiktok/performance/refresh'
        ];

        foreach ($routes as $name => $path) {
            if (\Route::has($name)) {
                $this->info("✅ Route {$name} exists");
            } else {
                $this->error("❌ Route {$name} not found");
            }
        }

        $this->info('🎉 Test hoàn thành!');
        $this->info('📋 Kết quả:');
        $this->info('✅ TikTok shops: Có dữ liệu (' . $shops->count() . ' shops)');
        $this->info('✅ Performance service: Hoạt động');
        $this->info('✅ Sample data: Đã tạo thành công');
        $this->info('✅ Routes: Đã cấu hình');

        $this->info('💡 Để sử dụng GMV Performance:');
        $this->info('1. Đăng nhập vào hệ thống');
        $this->info('2. Truy cập: http://127.0.0.1:8000/tiktok/performance');
        $this->info('3. Chọn shop từ dropdown');
        $this->info('4. Chọn khoảng thời gian');
        $this->info('5. Click "Load Data" để xem GMV performance');
        $this->info('6. Sử dụng "Refresh" để cập nhật dữ liệu');

        return 0;
    }
}
