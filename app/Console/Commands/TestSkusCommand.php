<?php

namespace App\Console\Commands;

use App\Services\TiktokShopProductService;
use App\Models\Product;
use App\Models\TikTokShop;
use Illuminate\Console\Command;

class TestSkusCommand extends Command
{
    protected $signature = 'test:skus {product_id} {shop_id?}';
    protected $description = 'Test SKUs data structure for TikTok API';

    public function handle()
    {
        $this->info('=== TEST SKUS DATA STRUCTURE ===');

        $productId = $this->argument('product_id');
        $shopId = $this->argument('shop_id');

        try {
            // Lấy product từ database
            $product = Product::with([
                'images',
                'productTemplate.variants.optionValues.option',
                'productTemplate.options.values'
            ])->find($productId);
            if (!$product) {
                $this->error("Không tìm thấy sản phẩm với ID: {$productId}");
                return 1;
            }

            // Lấy shop từ database
            $shop = null;
            if ($shopId) {
                $shop = TikTokShop::with('integration')->find($shopId);
            } else {
                $shop = TikTokShop::with('integration')->first();
            }

            if (!$shop) {
                $this->error('Không tìm thấy shop nào trong database');
                return 1;
            }

            $this->info("Testing với:");
            $this->info("  - Product ID: {$product->id}");
            $this->info("  - Product Title: {$product->title}");
            $this->info("  - Shop ID: {$shop->id}");
            $this->info("  - Shop Name: {$shop->shop_name}");

            // Kiểm tra template và variants
            if ($product->productTemplate) {
                $template = $product->productTemplate;
                $this->info("  - Template: {$template->name}");
                $this->info("  - Options: " . $template->options->count());
                $this->info("  - Variants: " . $template->variants->count());

                // Hiển thị options
                foreach ($template->options as $index => $option) {
                    $this->info("    Option " . ($index + 1) . ":");
                    $this->info("      - Name: {$option->name}");
                    $this->info("      - Type: {$option->type}");
                    $this->info("      - Values: " . $option->values->count());
                    foreach ($option->values as $valueIndex => $value) {
                        $this->info("        Value " . ($valueIndex + 1) . ": {$value->value} ({$value->label})");
                    }
                }

                // Hiển thị variants
                foreach ($template->variants as $index => $variant) {
                    $this->info("    Variant " . ($index + 1) . ":");
                    $this->info("      - SKU: {$variant->sku}");
                    $this->info("      - Price: {$variant->price}");
                    $this->info("      - Stock: {$variant->stock_quantity}");
                    $this->info("      - Option Values: " . $variant->optionValues->count());
                    foreach ($variant->optionValues as $optionValueIndex => $optionValue) {
                        $this->info("        Option Value " . ($optionValueIndex + 1) . ": {$optionValue->value} (from {$optionValue->option->name})");
                    }
                    $this->info("      - Variant Data: " . json_encode($variant->variant_data));
                }
            } else {
                $this->info("  - Template: None");
            }

            // Test prepareSkusData
            $productService = new TiktokShopProductService();
            $reflection = new \ReflectionClass($productService);
            $method = $reflection->getMethod('prepareSkusData');
            $method->setAccessible(true);

            $warehouseId = '7540452453539350295'; // Sales Warehouse
            $skus = $method->invoke($productService, $product, $product->productTemplate, $warehouseId);

            $this->info("\n=== SKUS DATA STRUCTURE ===");
            $this->info(json_encode($skus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            $this->error('❌ Lỗi hệ thống!');
            $this->error("Chi tiết: {$e->getMessage()}");
            return 1;
        }

        $this->info('=== END TEST ===');

        return 0;
    }
}
