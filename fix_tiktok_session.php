<?php

/**
 * Script để khắc phục lỗi "Session không hợp lệ" cho TikTok Shop
 * Tạo lại authorization link mới và kiểm tra cấu hình
 */

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\TikTokShopIntegration;
use App\Models\TikTokShop;

echo "=== SCRIPT KHẮC PHỤC LỖI TIKTOK SHOP SESSION ===\n\n";

// 1. Kiểm tra cấu hình TikTok Shop
echo "1. Kiểm tra cấu hình TikTok Shop:\n";
echo "   - App Key: " . (config('tiktok-shop.app_key') ? '✓ Có' : '✗ Thiếu') . "\n";
echo "   - App Secret: " . (config('tiktok-shop.app_secret') ? '✓ Có' : '✗ Thiếu') . "\n";
echo "   - API Base URL: " . config('tiktok-shop.api_base_url') . "\n";
echo "   - Authorization URL: " . config('tiktok-shop.oauth.authorization_url') . "\n\n";

// 2. Kiểm tra các integration có lỗi
echo "2. Kiểm tra các TikTok Shop Integration:\n";
$integrations = TikTokShopIntegration::all();

foreach ($integrations as $integration) {
    echo "   - Integration ID: {$integration->id}\n";
    echo "     Team ID: {$integration->team_id}\n";
    echo "     Status: {$integration->status}\n";
    echo "     Access Token: " . ($integration->access_token ? '✓ Có' : '✗ Không có') . "\n";
    echo "     Refresh Token: " . ($integration->refresh_token ? '✓ Có' : '✗ Không có') . "\n";

    if ($integration->access_token_expires_at) {
        $isExpired = $integration->isAccessTokenExpired();
        echo "     Token Expired: " . ($isExpired ? '✗ Hết hạn' : '✓ Còn hạn') . "\n";
    }

    if ($integration->error_message) {
        echo "     Error: {$integration->error_message}\n";
    }
    echo "\n";
}

// 3. Tạo authorization link mới cho các integration có lỗi
echo "3. Tạo authorization link mới:\n";

foreach ($integrations as $integration) {
    if (
        $integration->status === 'error' || $integration->status === 'pending' ||
        ($integration->access_token && $integration->isAccessTokenExpired())
    ) {

        echo "   - Integration ID {$integration->id} cần tạo link mới:\n";

        try {
            // Reset status về pending
            $integration->update([
                'status' => 'pending',
                'error_message' => null,
                'access_token' => null,
                'refresh_token' => null,
                'access_token_expires_at' => null,
                'refresh_token_expires_at' => null,
            ]);

            // Tạo authorization URL mới
            $authUrl = $integration->getAuthorizationUrl();
            echo "     ✓ Đã reset integration\n";
            echo "     ✓ Authorization URL: {$authUrl}\n";
            echo "     ✓ Hướng dẫn: Truy cập URL trên để ủy quyền lại\n\n";
        } catch (Exception $e) {
            echo "     ✗ Lỗi: {$e->getMessage()}\n\n";
        }
    }
}

// 4. Kiểm tra TikTok Shops
echo "4. Kiểm tra TikTok Shops:\n";
$shops = TikTokShop::all();

foreach ($shops as $shop) {
    echo "   - Shop ID: {$shop->id}\n";
    echo "     Shop Name: {$shop->shop_name}\n";
    echo "     Integration ID: {$shop->integration_id}\n";
    echo "     Status: {$shop->status}\n";
    echo "     Access Token: " . ($shop->access_token ? '✓ Có' : '✗ Không có') . "\n";
    echo "     Refresh Token: " . ($shop->refresh_token ? '✓ Có' : '✗ Không có') . "\n";

    if ($shop->access_token_expires_at) {
        $isExpired = $shop->isAccessTokenExpired();
        echo "     Token Expired: " . ($isExpired ? '✗ Hết hạn' : '✓ Còn hạn') . "\n";
    }
    echo "\n";
}

// 5. Tạo script để test API
echo "5. Tạo script test API:\n";
$testScript = 'test_tiktok_api_fix.php';
$testContent = '<?php
require_once "vendor/autoload.php";

use App\Services\TikTokShopService;
use App\Models\TikTokShopIntegration;

echo "=== TEST TIKTOK SHOP API ===\n";

$service = new TikTokShopService();
$integrations = TikTokShopIntegration::where("status", "active")->get();

foreach ($integrations as $integration) {
    echo "Testing Integration ID: {$integration->id}\n";
    
    try {
        $result = $service->getAuthorizedShops($integration);
        if ($result["success"]) {
            echo "✓ API hoạt động bình thường\n";
        } else {
            echo "✗ API lỗi: {$result["error"]}\n";
        }
    } catch (Exception $e) {
        echo "✗ Exception: {$e->getMessage()}\n";
    }
    echo "\n";
}
?>';

file_put_contents($testScript, $testContent);
echo "   ✓ Đã tạo script test: {$testScript}\n\n";

echo "=== HOÀN THÀNH ===\n";
echo "Để khắc phục lỗi:\n";
echo "1. Kiểm tra cấu hình trong .env file\n";
echo "2. Chạy script test: php {$testScript}\n";
echo "3. Truy cập authorization URLs được tạo ở trên\n";
echo "4. Hoàn thành quá trình ủy quyền trên TikTok Shop\n";
