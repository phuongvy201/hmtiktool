<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST ACCESS TOKEN VALIDITY ===\n\n";

try {
    // Lấy shop đầu tiên
    $shop = TikTokShop::with('integration')->first();
    $integration = $shop->integration;

    echo "🏪 Shop: {$shop->shop_name}\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Integration Status: {$integration->status}\n\n";

    echo "🔑 Access Token Info:\n";
    echo "   Token: " . substr($integration->access_token, 0, 50) . "...\n";
    echo "   Expires At: " . date('Y-m-d H:i:s', $integration->access_token_expires_at) . "\n";
    echo "   Is Expired: " . ($integration->isAccessTokenExpired() ? 'YES' : 'NO') . "\n";
    echo "   Current Time: " . date('Y-m-d H:i:s') . "\n\n";

    // Test 1: Thử gọi API đơn giản để kiểm tra token
    echo "📝 Test 1: Kiểm tra token với API đơn giản\n";

    // Thử gọi API get shop info (nếu có)
    $url = 'https://open-api.tiktokglobalshop.com/shop/' . '202309' . '/get';
    $headers = [
        'Content-Type' => 'application/json',
        'x-tts-access-token' => $integration->access_token
    ];

    $queryParams = [
        'shop_cipher' => $shop->getShopCipher(),
        'app_key' => $integration->getAppKey(),
        'timestamp' => time(),
        'sign' => 'test_signature' // Dùng signature giả để test token
    ];

    $response = Http::withHeaders($headers)
        ->timeout(30)
        ->get($url . '?' . http_build_query($queryParams));

    $httpCode = $response->status();
    $responseData = $response->json();

    echo "   URL: {$url}\n";
    echo "   Status Code: {$httpCode}\n";
    echo "   Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n\n";

    // Test 2: Thử refresh token
    echo "📝 Test 2: Thử refresh token\n";
    $refreshResult = $integration->refreshAccessToken();

    echo "   Refresh Result: " . json_encode($refreshResult, JSON_PRETTY_PRINT) . "\n";

    if ($refreshResult['success']) {
        echo "   ✅ Token refresh thành công!\n";
        echo "   New Token: " . substr($integration->fresh()->access_token, 0, 50) . "...\n";
        echo "   New Expires: " . date('Y-m-d H:i:s', $integration->fresh()->access_token_expires_at) . "\n";
    } else {
        echo "   ❌ Token refresh thất bại: {$refreshResult['message']}\n";
    }

    // Test 3: Kiểm tra integration có đúng app_key/app_secret không
    echo "\n📝 Test 3: Kiểm tra app credentials\n";
    echo "   App Key: {$integration->getAppKey()}\n";
    echo "   App Secret Length: " . strlen($integration->getAppSecret()) . "\n";
    echo "   Team ID: {$integration->team_id}\n";
    echo "   Additional Data: " . json_encode($integration->additional_data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
