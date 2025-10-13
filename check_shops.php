<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\TikTokShop;

echo "🔍 Kiểm tra tất cả TikTok Shops...\n\n";

try {
    $shops = TikTokShop::with('integration')->where('status', 'active')->get();

    if ($shops->count() === 0) {
        echo "❌ Không có shop nào active\n";
        exit(1);
    }

    echo "✅ Found {$shops->count()} active TikTok shops:\n\n";

    foreach ($shops as $shop) {
        echo "🏪 Shop ID: {$shop->id}\n";
        echo "   Name: {$shop->shop_name}\n";
        echo "   Cipher: {$shop->cipher}\n";
        echo "   Status: {$shop->status}\n";

        if ($shop->integration) {
            echo "   Integration: {$shop->integration->status}\n";
            echo "   Access Token: " . (empty($shop->integration->access_token) ? 'MISSING' : 'EXISTS') . "\n";
        } else {
            echo "   Integration: MISSING\n";
        }
        echo "\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "🎉 Kiểm tra hoàn thành!\n";
