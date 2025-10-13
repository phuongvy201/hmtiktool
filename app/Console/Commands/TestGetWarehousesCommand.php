<?php

namespace App\Console\Commands;

use App\Services\TikTokShopService;
use App\Models\TikTokShopIntegration;
use Illuminate\Console\Command;

class TestGetWarehousesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:get-warehouses {integration_id? : ID của integration (optional)} {shop_id? : ID của shop (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test hàm getWarehouses đã được cập nhật để sử dụng shop cipher';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TEST GET WAREHOUSES (UPDATED) ===');

        $integrationId = $this->argument('integration_id');
        $shopId = $this->argument('shop_id');

        try {
            // Lấy integration
            $integration = null;
            if ($integrationId) {
                $integration = TikTokShopIntegration::find($integrationId);
                if (!$integration) {
                    $this->error("Không tìm thấy integration với ID: {$integrationId}");
                    return 1;
                }
            } else {
                $integration = TikTokShopIntegration::first();
                if (!$integration) {
                    $this->error('Không tìm thấy integration nào trong database');
                    return 1;
                }
            }

            $this->info("Sử dụng integration ID: {$integration->id}");
            $this->info("Team ID: {$integration->team_id}");

            if ($shopId) {
                $this->info("Testing với shop ID: {$shopId}");
            } else {
                $this->info('Testing với shop đầu tiên của integration');
            }

            $tiktokService = new TikTokShopService();

            // Gọi hàm getWarehouses đã được cập nhật
            $result = $tiktokService->getWarehouses($integration, $shopId);

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
        } catch (\Exception $e) {
            $this->error('❌ Lỗi hệ thống!');
            $this->error("Chi tiết: {$e->getMessage()}");
            return 1;
        }

        $this->info('=== END TEST ===');

        return $result['success'] ? 0 : 1;
    }
}
