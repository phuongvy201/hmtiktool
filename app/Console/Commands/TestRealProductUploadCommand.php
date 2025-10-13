<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokShopProductService;
use App\Models\Product;
use App\Models\TikTokShop;

class TestRealProductUploadCommand extends Command
{
    protected $signature = 'test:real-product-upload {product_id} {shop_id} {--user_id=1 : User ID thực hiện upload}';
    protected $description = 'Test real product upload to TikTok Shop';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $shopId = $this->argument('shop_id');

        $userId = $this->option('user_id');

        $this->info("=== TESTING REAL PRODUCT UPLOAD ===");
        $this->info("Product ID: {$productId}");
        $this->info("Shop ID: {$shopId}");
        $this->info("User ID: {$userId}");

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

        $this->info("Product: {$product->title}");
        $this->info("Shop: {$shop->shop_name}");
        $this->info("Shop Cipher: {$shop->getShopCipher()}");

        $service = new TikTokShopProductService();
        $result = $service->uploadProduct($product, $shop, $userId);

        $this->info("\n=== RESULT ===");
        $this->info("Success: " . ($result['success'] ? 'YES' : 'NO'));
        $this->info("Message: " . $result['message']);

        if (isset($result['data'])) {
            $this->info("Data: " . json_encode($result['data'], JSON_PRETTY_PRINT));
        }

        if (isset($result['upload_history_id'])) {
            $this->info("Upload History ID: {$result['upload_history_id']}");
        }

        $this->info("\n=== COMPLETED ===");
    }
}
