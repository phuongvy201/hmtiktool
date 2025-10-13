<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\TikTokShop;
use App\Services\TikTokShopProductService;

class TestProductUploadCommand extends Command
{
    protected $signature = 'test:product-upload {product_id} {shop_id}';
    protected $description = 'Test product upload and show full JSON request';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $shopId = $this->argument('shop_id');

        $product = Product::find($productId);
        $shop = TikTokShop::find($shopId);

        if (!$product) {
            $this->error("Product not found: {$productId}");
            return;
        }

        if (!$shop) {
            $this->error("Shop not found: {$shopId}");
            return;
        }

        $this->info("Testing product upload for Product ID: {$productId}, Shop ID: {$shopId}");

        $service = new TikTokShopProductService();

        // Prepare product data using reflection
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('prepareProductData');
        $method->setAccessible(true);
        $productData = $method->invoke($service, $product, $shop);

        $this->info("=== FULL PRODUCT DATA JSON ===");
        $this->line(json_encode($productData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $this->info("=== END PRODUCT DATA JSON ===");
    }
}
