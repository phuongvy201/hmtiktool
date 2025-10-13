<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CHECKING UPLOAD HISTORY ===\n";

$histories = App\Models\TikTokProductUploadHistory::with(['product', 'tiktokShop'])->get();

echo "Total histories: " . $histories->count() . "\n\n";

foreach ($histories as $h) {
    echo "ID: {$h->id}\n";
    echo "Product ID: {$h->product_id}\n";
    echo "Product Name: " . ($h->product ? $h->product->title : 'NULL') . "\n";
    echo "Shop: " . ($h->tiktokShop ? $h->tiktokShop->shop_name : 'NULL') . "\n";
    echo "Status: {$h->status}\n";
    echo "User ID: {$h->user_id}\n";
    echo "User Name: {$h->user_name}\n";
    echo "Created: {$h->created_at}\n";
    echo "---\n";
}

// Check products
echo "\n=== CHECKING PRODUCTS ===\n";
$products = App\Models\Product::with(['user'])->get();
echo "Total products: " . $products->count() . "\n";

foreach ($products as $p) {
    echo "Product ID: {$p->id}, Title: {$p->title}, User: {$p->user->name}\n";
}

