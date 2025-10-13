<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST SIGNATURE VARIATIONS ===\n\n";

try {
    // Lấy shop đầu tiên
    $shop = TikTokShop::with('integration')->first();
    $integration = $shop->integration;

    $appKey = $integration->getAppKey();
    $appSecret = $integration->getAppSecret();
    $shopCipher = $shop->getShopCipher();
    $timestamp = time();

    echo "🔧 Parameters:\n";
    echo "   App Key: {$appKey}\n";
    echo "   App Secret: {$appSecret}\n";
    echo "   Shop Cipher: {$shopCipher}\n";
    echo "   Timestamp: {$timestamp}\n\n";

    // Setup for API calls
    $url = 'https://open-api.tiktokglobalshop.com/order/202309/orders/search';
    $headers = [
        'Content-Type' => 'application/json',
        'x-tts-access-token' => $integration->access_token
    ];

    // Test các variation khác nhau của signature generation
    $apiPath = '/order/202309/orders/search';

    // Variation 1: Theo format TikTok chính thức - shop_cipher trong signature
    echo "📝 Variation 1: shop_cipher trong signature generation\n";
    $params1 = [
        'app_key' => $appKey,
        'shop_cipher' => $shopCipher,
        'timestamp' => (string)$timestamp
    ];
    ksort($params1);

    $paramString1 = '';
    foreach ($params1 as $key => $value) {
        $paramString1 .= $key . $value;
    }

    $input1 = $apiPath . $paramString1;
    $stringToSign1 = $appSecret . $input1 . $appSecret;
    $signature1 = hash_hmac('sha256', $stringToSign1, $appSecret, true);
    $hexSignature1 = bin2hex($signature1);

    echo "   Params: " . json_encode($params1) . "\n";
    echo "   Signature: {$hexSignature1}\n";

    // Test API call
    $queryParams1 = [
        'shop_cipher' => $shopCipher,
        'app_key' => $appKey,
        'timestamp' => $timestamp,
        'sign' => $hexSignature1,
        'page_size' => 5
    ];

    $result1 = testAPICall($url, $headers, $queryParams1);
    echo "   API Result: " . ($result1['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n\n";

    // Variation 2: Không có shop_cipher trong signature generation
    echo "📝 Variation 2: KHÔNG có shop_cipher trong signature generation\n";
    $params2 = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    ksort($params2);

    $paramString2 = '';
    foreach ($params2 as $key => $value) {
        $paramString2 .= $key . $value;
    }

    $input2 = $apiPath . $paramString2;
    $stringToSign2 = $appSecret . $input2 . $appSecret;
    $signature2 = hash_hmac('sha256', $stringToSign2, $appSecret, true);
    $hexSignature2 = bin2hex($signature2);

    echo "   Params: " . json_encode($params2) . "\n";
    echo "   Signature: {$hexSignature2}\n";

    // Test API call
    $queryParams2 = [
        'shop_cipher' => $shopCipher,
        'app_key' => $appKey,
        'timestamp' => $timestamp,
        'sign' => $hexSignature2,
        'page_size' => 5
    ];

    $result2 = testAPICall($url, $headers, $queryParams2);
    echo "   API Result: " . ($result2['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n\n";

    // Variation 3: Theo format khác - có thể TikTok yêu cầu format khác
    echo "📝 Variation 3: Format khác - có thể TikTok yêu cầu\n";
    $params3 = [
        'app_key' => $appKey,
        'shop_cipher' => $shopCipher,
        'timestamp' => (string)$timestamp
    ];
    ksort($params3);

    // Thử với format query string
    $queryString = http_build_query($params3);
    $input3 = $apiPath . '?' . $queryString;
    $stringToSign3 = $appSecret . $input3 . $appSecret;
    $signature3 = hash_hmac('sha256', $stringToSign3, $appSecret, true);
    $hexSignature3 = bin2hex($signature3);

    echo "   Query String: {$queryString}\n";
    echo "   Input: {$input3}\n";
    echo "   Signature: {$hexSignature3}\n";

    // Test API call
    $queryParams3 = [
        'shop_cipher' => $shopCipher,
        'app_key' => $appKey,
        'timestamp' => $timestamp,
        'sign' => $hexSignature3,
        'page_size' => 5
    ];

    $result3 = testAPICall($url, $headers, $queryParams3);
    echo "   API Result: " . ($result3['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n\n";

    // Variation 4: Kiểm tra xem có phải do access token không
    echo "📝 Variation 4: Kiểm tra access token\n";
    echo "   Access Token: " . substr($integration->access_token, 0, 30) . "...\n";
    echo "   Token Expires: " . date('Y-m-d H:i:s', $integration->access_token_expires_at) . "\n";
    echo "   Is Expired: " . ($integration->isAccessTokenExpired() ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

function testAPICall($url, $headers, $queryParams)
{
    try {
        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
            ->timeout(30)
            ->withBody('{}', 'application/json')
            ->post($url . '?' . $queryString);

        $httpCode = $response->status();
        $responseData = $response->json();

        if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
            return ['success' => true, 'data' => $responseData];
        } else {
            return ['success' => false, 'error' => $responseData['message'] ?? 'Unknown error'];
        }
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

echo "\n=== TEST HOÀN THÀNH ===\n";
