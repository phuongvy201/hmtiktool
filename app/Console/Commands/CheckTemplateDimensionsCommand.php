<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;

class CheckTemplateDimensionsCommand extends Command
{
    protected $signature = 'check:template-dimensions {product_id}';
    protected $description = 'Check template dimensions for a product';

    public function handle()
    {
        $productId = $this->argument('product_id');
        
        $product = Product::find($productId);
        if (!$product) {
            $this->error("Product not found: {$productId}");
            return;
        }
        
        $this->info("Product: {$product->title}");
        
        if ($product->productTemplate) {
            $template = $product->productTemplate;
            $this->info("Template ID: {$template->id}");
            $this->info("Template Name: {$template->name}");
            $this->info("Template Length: " . ($template->length ?? 'null'));
            $this->info("Template Height: " . ($template->height ?? 'null'));
            $this->info("Template Width: " . ($template->width ?? 'null'));
            
            $this->info("\nAfter round():");
            $this->info("Rounded Length: " . round($template->length ?? 10.00));
            $this->info("Rounded Height: " . round($template->height ?? 10.00));
            $this->info("Rounded Width: " . round($template->width ?? 10.00));
        } else {
            $this->info("No template found");
        }
    }
}
