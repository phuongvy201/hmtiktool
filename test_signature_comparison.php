<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokSignatureService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST SIGNATURE COMPARISON ===\n\n";

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

    // Test 1: Signature hiện tại
    echo "📝 Test 1: Signature hiện tại\n";
    $signature1 = TikTokSignatureService::generateOrderSearchSignature(
        $appKey,
        $appSecret,
        (string)$timestamp,
        [],
        $shopCipher
    );
    echo "   Signature: {$signature1}\n\n";

    // Test 2: Thử signature đơn giản hơn (không có shop_cipher trong signature generation)
    echo "📝 Test 2: Signature không có shop_cipher trong generation\n";
    $apiPath = '/order/202309/orders/search';
    $queryParams = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    $signature2 = TikTokSignatureService::generateSignature(
        $appKey,
        $appSecret,
        $apiPath,
        $queryParams,
        [],
        'application/json'
    );
    echo "   Signature: {$signature2}\n\n";

    // Test 3: Thử với thứ tự khác
    echo "📝 Test 3: Signature với thứ tự khác\n";
    $queryParams3 = [
        'timestamp' => (string)$timestamp,
        'app_key' => $appKey
    ];
    ksort($queryParams3);
    $signature3 = TikTokSignatureService::generateSignature(
        $appKey,
        $appSecret,
        $apiPath,
        $queryParams3,
        [],
        'application/json'
    );
    echo "   Signature: {$signature3}\n\n";

    // Test 4: Manual signature generation theo format TikTok
    echo "📝 Test 4: Manual signature generation\n";

    // Theo tài liệu TikTok, format thường là:
    // string_to_sign = app_secret + api_path + sorted_query_params + body + app_secret

    $filteredParams = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    ksort($filteredParams);

    $paramString = '';
    foreach ($filteredParams as $key => $value) {
        $paramString .= $key . $value;
    }

    $input = $apiPath . $paramString;
    $stringToSign = $appSecret . $input . $appSecret;
    $signature4 = hash_hmac('sha256', $stringToSign, $appSecret, true);
    $hexSignature4 = bin2hex($signature4);

    echo "   Filtered Params: " . json_encode($filteredParams) . "\n";
    echo "   Param String: {$paramString}\n";
    echo "   Input: {$input}\n";
    echo "   String to Sign: {$stringToSign}\n";
    echo "   Signature: {$hexSignature4}\n\n";

    // Test 5: Thử với shop_cipher trong query params nhưng không trong signature
    echo "📝 Test 5: shop_cipher trong query nhưng không trong signature\n";
    $queryParams5 = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    ksort($queryParams5);
    $signature5 = TikTokSignatureService::generateSignature(
        $appKey,
        $appSecret,
        $apiPath,
        $queryParams5,
        [],
        'application/json'
    );
    echo "   Signature: {$signature5}\n\n";

    // Test API call với signature 5 (không có shop_cipher trong signature)
    echo "🌐 Test API call với signature 5...\n";

    $queryParamsForAPI = [
        'shop_cipher' => $shopCipher,
        'app_key' => $appKey,
        'timestamp' => $timestamp,
        'sign' => $signature5,
        'page_size' => 5
    ];

    ksort($queryParamsForAPI);
    $queryString = http_build_query($queryParamsForAPI);

    $url = 'https://open-api.tiktokglobalshop.com/order/202309/orders/search';
    $headers = [
        'Content-Type' => 'application/json',
        'x-tts-access-token' => $integration->access_token
    ];

    $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
        ->timeout(30)
        ->withBody('{}', 'application/json')
        ->post($url . '?' . $queryString);

    $httpCode = $response->status();
    $responseData = $response->json();

    echo "   Status Code: {$httpCode}\n";
    if ($httpCode === 200) {
        echo "   ✅ SUCCESS!\n";
        echo "   Signature đúng là: {$signature5}\n";
    } else {
        echo "   ❌ Still failed\n";
        echo "   Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
