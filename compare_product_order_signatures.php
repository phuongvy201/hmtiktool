<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokSignatureService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== SO SÁNH SIGNATURE PRODUCT vs ORDER API ===\n\n";

try {
    // Lấy shop có integration hoạt động
    $shop = TikTokShop::find(12);
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

    // Test 1: Product Upload Signature (đang hoạt động)
    echo "📝 Test 1: Product Upload Signature (ĐANG HOẠT ĐỘNG)\n";
    $productSignature = TikTokSignatureService::generateProductUploadSignature(
        $appKey,
        $appSecret,
        (string)$timestamp,
        [],
        $shopCipher
    );
    echo "   Signature: {$productSignature}\n\n";

    // Test 2: Order Search Signature (không hoạt động)
    echo "📝 Test 2: Order Search Signature (KHÔNG HOẠT ĐỘNG)\n";
    $orderSignature = TikTokSignatureService::generateOrderSearchSignature(
        $appKey,
        $appSecret,
        (string)$timestamp,
        [],
        $shopCipher
    );
    echo "   Signature: {$orderSignature}\n\n";

    // Test 3: So sánh chi tiết cách tạo signature
    echo "📝 Test 3: So sánh chi tiết cách tạo signature\n";

    // Product API path
    $productApiPath = '/product/202309/products';
    $productQueryParams = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    if ($shopCipher) {
        $productQueryParams['shop_cipher'] = $shopCipher;
    }
    ksort($productQueryParams);

    $productParamString = '';
    foreach ($productQueryParams as $key => $value) {
        $productParamString .= $key . $value;
    }
    $productInput = $productApiPath . $productParamString;
    $productStringToSign = $appSecret . $productInput . $appSecret;
    $productSignatureManual = hash_hmac('sha256', $productStringToSign, $appSecret, true);
    $productSignatureManualHex = bin2hex($productSignatureManual);

    echo "   Product API Path: {$productApiPath}\n";
    echo "   Product Query Params: " . json_encode($productQueryParams) . "\n";
    echo "   Product Param String: {$productParamString}\n";
    echo "   Product Input: {$productInput}\n";
    echo "   Product String to Sign: {$productStringToSign}\n";
    echo "   Product Signature (Manual): {$productSignatureManualHex}\n";
    echo "   Product Signature (Service): {$productSignature}\n";
    echo "   Match: " . ($productSignature === $productSignatureManualHex ? '✅' : '❌') . "\n\n";

    // Order API path
    $orderApiPath = '/order/202309/orders/search';
    $orderQueryParams = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    if ($shopCipher) {
        $orderQueryParams['shop_cipher'] = $shopCipher;
    }
    ksort($orderQueryParams);

    $orderParamString = '';
    foreach ($orderQueryParams as $key => $value) {
        $orderParamString .= $key . $value;
    }
    $orderInput = $orderApiPath . $orderParamString;
    $orderStringToSign = $appSecret . $orderInput . $appSecret;
    $orderSignatureManual = hash_hmac('sha256', $orderStringToSign, $appSecret, true);
    $orderSignatureManualHex = bin2hex($orderSignatureManual);

    echo "   Order API Path: {$orderApiPath}\n";
    echo "   Order Query Params: " . json_encode($orderQueryParams) . "\n";
    echo "   Order Param String: {$orderParamString}\n";
    echo "   Order Input: {$orderInput}\n";
    echo "   Order String to Sign: {$orderStringToSign}\n";
    echo "   Order Signature (Manual): {$orderSignatureManualHex}\n";
    echo "   Order Signature (Service): {$orderSignature}\n";
    echo "   Match: " . ($orderSignature === $orderSignatureManualHex ? '✅' : '❌') . "\n\n";

    // Test 4: Thử các variation khác cho Order API
    echo "📝 Test 4: Thử các variation khác cho Order API\n";

    // Variation 1: Không có shop_cipher trong signature
    $orderParams1 = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    ksort($orderParams1);

    $orderParamString1 = '';
    foreach ($orderParams1 as $key => $value) {
        $orderParamString1 .= $key . $value;
    }
    $orderInput1 = $orderApiPath . $orderParamString1;
    $orderStringToSign1 = $appSecret . $orderInput1 . $appSecret;
    $orderSignature1 = hash_hmac('sha256', $orderStringToSign1, $appSecret, true);
    $orderSignature1Hex = bin2hex($orderSignature1);

    echo "   Variation 1 (không có shop_cipher): {$orderSignature1Hex}\n";

    // Test API call với variation 1
    $result1 = testOrderAPI($shop, $integration, $orderSignature1Hex, $timestamp, false);
    echo "   API Result: " . ($result1['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    if (!$result1['success']) {
        echo "   Error: {$result1['error']}\n";
    }
    echo "\n";

    // Variation 2: Có shop_cipher trong signature
    echo "   Variation 2 (có shop_cipher): {$orderSignature}\n";
    $result2 = testOrderAPI($shop, $integration, $orderSignature, $timestamp, true);
    echo "   API Result: " . ($result2['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    if (!$result2['success']) {
        echo "   Error: {$result2['error']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
}

function testOrderAPI($shop, $integration, $signature, $timestamp, $includeShopCipher)
{
    try {
        $url = 'https://open-api.tiktokglobalshop.com/order/202309/orders/search';
        $headers = [
            'Content-Type' => 'application/json',
            'x-tts-access-token' => $integration->access_token
        ];

        $queryParams = [
            'app_key' => $integration->getAppKey(),
            'timestamp' => $timestamp,
            'sign' => $signature,
            'page_size' => 3
        ];

        if ($includeShopCipher) {
            $queryParams['shop_cipher'] = $shop->getShopCipher();
        }

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

echo "\n=== SO SÁNH HOÀN THÀNH ===\n";
