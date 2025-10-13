<?php

namespace App\Console\Commands;

use App\Services\TiktokShopProductService;
use App\Models\TikTokShop;
use Illuminate\Console\Command;

class TestProductUploadWarehousesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:product-upload-warehouses {shop_id? : ID của shop trong database (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test lấy warehouses trong quá trình upload product';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TEST PRODUCT UPLOAD WAREHOUSES ===');

        $shopId = $this->argument('shop_id');

        try {
            // Lấy shop từ database
            $shop = null;
            if ($shopId) {
                $shop = TikTokShop::find($shopId);
            } else {
                $shop = TikTokShop::first();
            }

            if (!$shop) {
                $this->error('Không tìm thấy shop nào trong database');
                return 1;
            }

            $this->info("Testing với shop:");
            $this->info("  - Database ID: {$shop->id}");
            $this->info("  - TikTok Shop ID: {$shop->shop_id}");
            $this->info("  - Shop Name: {$shop->shop_name}");
            $this->info("  - Shop Cipher: " . ($shop->getShopCipher() ?? 'N/A'));

            $productService = new TiktokShopProductService();

            // Sử dụng reflection để gọi private method
            $reflection = new \ReflectionClass($productService);
            $method = $reflection->getMethod('getDefaultWarehouseId');
            $method->setAccessible(true);

            $warehouseId = $method->invoke($productService, $shop);

            $this->info("✅ Warehouse ID được chọn: {$warehouseId}");

            if ($warehouseId === 'UK_WAREHOUSE_001') {
                $this->warn('⚠️ Đang sử dụng warehouse mặc định (có thể không lấy được từ TikTok API)');
            } else {
                $this->info('🎉 Đã lấy được warehouse từ TikTok API thành công!');
            }
        } catch (\Exception $e) {
            $this->error('❌ Lỗi hệ thống!');
            $this->error("Chi tiết: {$e->getMessage()}");
            return 1;
        }

        $this->info('=== END TEST ===');

        return 0;
    }
}
