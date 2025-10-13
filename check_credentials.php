<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TikTokShop;

echo "🔍 Kiểm tra TikTok Shop credentials...\n\n";

try {
    $shop = TikTokShop::with('integration')->find(15);
    
    if (!$shop) {
        echo "❌ Không tìm thấy shop với ID 15\n";
        exit(1);
    }
    
    echo "✅ Shop ID: " . $shop->id . "\n";
    echo "✅ Integration ID: " . $shop->integration->id . "\n";
    echo "✅ Access Token: " . ($shop->integration->access_token ? 'SET (' . strlen($shop->integration->access_token) . ' chars)' : 'NOT SET') . "\n";
    echo "✅ Token Expires: " . ($shop->integration->access_token_expires_at ? date('Y-m-d H:i:s', $shop->integration->access_token_expires_at) : 'NOT SET') . "\n";
    echo "✅ Is Expired: " . ($shop->integration->isAccessTokenExpired() ? 'YES' : 'NO') . "\n";
    
    // Kiểm tra app credentials
    echo "\n🔑 App Credentials:\n";
    echo "✅ App Key: " . config('tiktok-shop.app_key') . "\n";
    echo "✅ App Secret: " . (config('tiktok-shop.app_secret') ? 'SET (' . strlen(config('tiktok-shop.app_secret')) . ' chars)' : 'NOT SET') . "\n";
    
    // Kiểm tra shop cipher
    echo "\n🏪 Shop Info:\n";
    echo "✅ Shop Cipher: " . $shop->shop_cipher . "\n";
    echo "✅ Shop Name: " . $shop->shop_name . "\n";
    echo "✅ Status: " . $shop->status . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🎉 Kiểm tra hoàn thành!\n";
