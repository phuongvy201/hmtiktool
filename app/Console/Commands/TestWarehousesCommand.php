<?php

namespace App\Console\Commands;

use App\Services\TikTokShopService;
use Illuminate\Console\Command;

class TestWarehousesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:warehouses {shop_id? : ID của shop trong database (optional)} {--all : Test tất cả shops trong database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test lấy warehouses từ TikTok Shop API sử dụng shop cipher từ TikTokShop model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TEST WAREHOUSES WITH SHOP CIPHER ===');

        $shopId = $this->argument('shop_id');
        $testAll = $this->option('all');

        $tiktokService = new TikTokShopService();

        if ($testAll) {
            $this->info('Testing tất cả shops trong database...');
            $result = $tiktokService->testGetWarehousesForAllShops();

            if ($result['success']) {
                $this->info('✅ Test tất cả shops hoàn thành!');

                $summary = $result['summary'];
                $this->info("📊 Tổng kết:");
                $this->info("   - Tổng số shops: {$summary['total_shops']}");
                $this->info("   - Thành công: {$summary['success_count']}");
                $this->info("   - Thất bại: {$summary['error_count']}");

                $this->info('📋 Chi tiết từng shop:');
                $headers = ['Shop ID', 'Shop Name', 'TikTok Shop ID', 'Cipher', 'Status', 'Warehouses'];
                $rows = [];

                foreach ($result['data'] as $shopResult) {
                    $warehousesCount = 0;
                    $status = '❌ Lỗi';

                    if ($shopResult['result']['success']) {
                        $status = '✅ Thành công';
                        if (isset($shopResult['result']['data'])) {
                            $warehousesCount = count($shopResult['result']['data']);
                        }
                    } else {
                        $status = "❌ {$shopResult['result']['error']}";
                    }

                    $rows[] = [
                        $shopResult['shop_id'],
                        $shopResult['shop_name'],
                        $shopResult['tiktok_shop_id'],
                        $shopResult['cipher'] ?? 'N/A',
                        $status,
                        $warehousesCount
                    ];
                }

                $this->table($headers, $rows);
            } else {
                $this->error('❌ Test tất cả shops thất bại!');
                $this->error("Lỗi: {$result['error']}");
            }
        } else {
            if ($shopId) {
                $this->info("Testing với shop ID: {$shopId}");
            } else {
                $this->info('Testing với shop đầu tiên trong database');
            }

            $result = $tiktokService->testGetWarehousesWithShopCipher($shopId);

            if ($result['success']) {
                $this->info('✅ Test thành công!');

                if (isset($result['data']) && is_array($result['data'])) {
                    $warehousesCount = count($result['data']);
                    $this->info("📦 Tìm thấy {$warehousesCount} warehouses");

                    if ($warehousesCount > 0) {
                        $this->info('📋 Danh sách warehouses:');
                        $headers = ['Warehouse ID', 'Name', 'Type', 'Sub Type', 'Status', 'Default'];
                        $rows = [];

                        foreach ($result['data'] as $warehouse) {
                            $rows[] = [
                                $warehouse['id'] ?? 'N/A',
                                $warehouse['name'] ?? 'N/A',
                                $warehouse['type'] ?? 'N/A',
                                $warehouse['sub_type'] ?? 'N/A',
                                $warehouse['effect_status'] ?? 'N/A',
                                $warehouse['is_default'] ? 'Yes' : 'No'
                            ];
                        }

                        $this->table($headers, $rows);
                    }
                } else {
                    $this->warn('⚠️ Không có warehouses nào được trả về');
                    if (isset($result['message'])) {
                        $this->info("Thông báo: {$result['message']}");
                    }
                }

                if (isset($result['request_id'])) {
                    $this->info("Request ID: {$result['request_id']}");
                }
            } else {
                $this->error('❌ Test thất bại!');
                $this->error("Lỗi: {$result['error']}");
            }
        }

        $this->info('=== END TEST ===');

        return $result['success'] ? 0 : 1;
    }
}
