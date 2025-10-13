<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class CheckProductsCommand extends Command
{
    protected $signature = 'check:products';
    protected $description = 'Check products and their images';

    public function handle()
    {
        $this->info('=== CHECKING PRODUCTS ===');

        $products = Product::with(['images', 'productTemplate'])->get();

        foreach ($products as $product) {
            $this->info("Product ID: {$product->id}");
            $this->info("  Title: {$product->title}");
            $this->info("  Images: " . $product->images->count());

            if ($product->productTemplate) {
                $this->info("  Template: {$product->productTemplate->name}");
                $this->info("  Template Images: " . (is_array($product->productTemplate->images) ? count($product->productTemplate->images) : ($product->productTemplate->images ? 1 : 0)));
            } else {
                $this->info("  Template: None");
            }

            $this->info("  ---");
        }

        return 0;
    }
}
