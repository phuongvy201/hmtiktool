<?php

/**
 * Script test kết nối TikTok Shop
 */

require_once 'vendor/autoload.php';

use App\Models\TikTokShopIntegration;
use App\Models\Team;
use App\Services\TikTokShopService;

echo "=== TEST TIKTOK SHOP CONNECTION ===\n\n";

// 1. Kiểm tra integrations
echo "1. Kiểm tra TikTok Shop Integrations:\n";
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

// 2. Test API cho integrations active
echo "2. Test API cho integrations active:\n";
$service = new TikTokShopService();
$activeIntegrations = TikTokShopIntegration::where('status', 'active')->get();

foreach ($activeIntegrations as $integration) {
    echo "   - Testing Integration ID: {$integration->id}\n";

    try {
        $result = $service->getAuthorizedShops($integration);
        if ($result['success']) {
            $shopCount = isset($result['data']['shops']) ? count($result['data']['shops']) : 0;
            echo "     ✅ API hoạt động bình thường - Số shops: {$shopCount}\n";
        } else {
            echo "     ❌ API lỗi: {$result['error']}\n";
        }
    } catch (Exception $e) {
        echo "     ❌ Exception: {$e->getMessage()}\n";
    }
    echo "\n";
}

// 3. Tạo integration mới để test
echo "3. Tạo integration mới để test:\n";
$team = Team::find(7);
if ($team) {
    $newIntegration = TikTokShopIntegration::create([
        'team_id' => $team->id,
        'status' => 'pending',
    ]);

    echo "   ✅ Đã tạo integration mới với ID: {$newIntegration->id}\n";

    // Tạo authorization URL
    $authUrl = $newIntegration->getAuthorizationUrl();
    echo "   🔗 Authorization URL: {$authUrl}\n";
    echo "   📋 Hướng dẫn: Truy cập URL trên để kết nối TikTok Shop\n\n";
} else {
    echo "   ❌ Không tìm thấy team với ID: 7\n\n";
}

// 4. Tạo customer authorization URL
echo "4. Tạo customer authorization URL:\n";
if (isset($newIntegration)) {
    $customerAuthUrl = 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query([
        'app_key' => $newIntegration->getAppKey(),
        'state' => base64_encode(json_encode([
            'team_id' => $newIntegration->team_id,
            'auth_token' => 'test_token_' . time(),
            'type' => 'customer_auth'
        ])),
        'redirect_uri' => 'http://localhost/team/tiktok-shop/customer-callback',
        'scope' => 'seller.authorization.info,seller.shop.info,seller.product.basic,seller.order.info,seller.fulfillment.basic,seller.logistics,seller.delivery.status.write,seller.finance.info,seller.product.delete,seller.product.write,seller.product.optimize',
    ]);

    echo "   🔗 Customer Authorization URL: {$customerAuthUrl}\n";
    echo "   📋 Hướng dẫn: Khách hàng sử dụng URL này để lấy authorization code\n\n";
}

echo "=== HOÀN THÀNH ===\n";
echo "Để test kết nối TikTok Shop:\n";
echo "1. Sử dụng Authorization URL để kết nối trực tiếp\n";
echo "2. Hoặc sử dụng Customer Authorization URL để khách hàng lấy code\n";
echo "3. Kiểm tra log để xem chi tiết quá trình authorization\n";
