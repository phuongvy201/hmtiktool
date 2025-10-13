<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class CheckProductTemplateCommand extends Command
{
    protected $signature = 'check:product-template {product_id}';
    protected $description = 'Check which template a product is using';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $product = Product::find($productId);

        if (!$product) {
            $this->error("Product not found: {$productId}");
            return;
        }

        $this->info("=== PRODUCT INFORMATION ===");
        $this->info("Product ID: {$product->id}");
        $this->info("Product Name: {$product->name}");
        $this->info("Template ID: {$product->product_template_id}");

        if ($product->productTemplate) {
            $template = $product->productTemplate;
            $this->info("=== TEMPLATE INFORMATION ===");
            $this->info("Template Name: {$template->name}");
            $this->info("Template Description: {$template->description}");
            $this->info("Template Category ID: {$template->category_id}");
            $this->info("Template Base Price: {$template->base_price}");
            $this->info("Template List Price: {$template->list_price}");
            $this->info("Template Weight: {$template->weight}");
            $this->info("Template Dimensions: {$template->height} x {$template->width} x {$template->length}");
            $this->info("Template Images: " . json_encode($template->images));
            $this->info("Template Variants Count: " . $template->variants()->count());
            $this->info("Template Options Count: " . $template->options()->count());
        } else {
            $this->error("No template found for this product");
        }
    }
}
