<?php

namespace App\Console\Commands;

use App\Services\TiktokShopProductService;
use App\Models\Product;
use App\Models\TikTokShop;
use Illuminate\Console\Command;

class TestProductImageUploadCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:product-image-upload {product_id} {shop_id? : ID của shop trong database (optional)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test logic upload ảnh sản phẩm và template lên TikTok trước khi upload sản phẩm';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== TEST PRODUCT IMAGE UPLOAD LOGIC ===');

        $productId = $this->argument('product_id');
        $shopId = $this->argument('shop_id');

        try {
            // Lấy product từ database
            $product = Product::with(['images', 'productTemplate'])->find($productId);
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
            $this->info("  - Shop Cipher: " . ($shop->getShopCipher() ?? 'N/A'));

            // Kiểm tra ảnh hiện tại
            $this->info("\n=== KIỂM TRA ẢNH HIỆN TẠI ===");
            $productImages = $product->images;
            $this->info("Số lượng ảnh sản phẩm: " . $productImages->count());

            foreach ($productImages as $index => $image) {
                $this->info("  Ảnh " . ($index + 1) . ":");
                $this->info("    - ID: {$image->id}");
                $this->info("    - File Name: {$image->file_name}");
                $this->info("    - File Path: {$image->file_path}");
                $this->info("    - TikTok URI: " . ($image->tiktok_uri ?? 'Chưa có'));
                $this->info("    - Is Uploaded: " . ($image->is_uploaded_to_tiktok ? 'Có' : 'Chưa'));
            }

            // Kiểm tra template
            if ($product->productTemplate) {
                $this->info("\n=== KIỂM TRA TEMPLATE ===");
                $template = $product->productTemplate;
                $this->info("Template ID: {$template->id}");
                $this->info("Template Name: {$template->name}");

                if ($template->images) {
                    $templateImages = is_array($template->images) ? $template->images : [$template->images];
                    $this->info("Số lượng ảnh template: " . count($templateImages));

                    foreach ($templateImages as $index => $templateImage) {
                        $this->info("  Template ảnh " . ($index + 1) . ":");
                        if (is_array($templateImage)) {
                            $this->info("    - File Path: " . ($templateImage['file_path'] ?? 'N/A'));
                            $this->info("    - File Name: " . ($templateImage['file_name'] ?? 'N/A'));
                        } else {
                            $this->info("    - File Path: {$templateImage}");
                        }
                    }
                } else {
                    $this->info("Template không có ảnh");
                }
            } else {
                $this->info("\n=== TEMPLATE ===");
                $this->info("Sản phẩm không có template");
            }

            // Test upload ảnh
            $this->info("\n=== TEST UPLOAD ẢNH ===");
            $productService = new TiktokShopProductService();

            // Sử dụng reflection để gọi private method
            $reflection = new \ReflectionClass($productService);
            $method = $reflection->getMethod('ensureProductImagesHaveTikTokUri');
            $method->setAccessible(true);

            $this->info("Đang upload ảnh lên TikTok...");
            $method->invoke($productService, $product, $shop);

            // Kiểm tra kết quả sau khi upload
            $this->info("\n=== KẾT QUẢ SAU KHI UPLOAD ===");
            $product->refresh(); // Refresh để lấy dữ liệu mới
            $product->load('images');

            $updatedImages = $product->images;
            $this->info("Số lượng ảnh sau upload: " . $updatedImages->count());

            $uploadedCount = 0;
            foreach ($updatedImages as $index => $image) {
                $this->info("  Ảnh " . ($index + 1) . ":");
                $this->info("    - ID: {$image->id}");
                $this->info("    - File Name: {$image->file_name}");
                $this->info("    - TikTok URI: " . ($image->tiktok_uri ?? 'Chưa có'));
                $this->info("    - Is Uploaded: " . ($image->is_uploaded_to_tiktok ? 'Có' : 'Chưa'));
                $this->info("    - Source: " . ($image->source ?? 'product'));

                if ($image->tiktok_uri) {
                    $uploadedCount++;
                }
            }

            $this->info("\n=== TỔNG KẾT ===");
            $this->info("Tổng số ảnh có TikTok URI: {$uploadedCount}/" . $updatedImages->count());

            if ($uploadedCount > 0) {
                $this->info('✅ Upload ảnh thành công!');
            } else {
                $this->warn('⚠️ Không có ảnh nào được upload thành công');
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
