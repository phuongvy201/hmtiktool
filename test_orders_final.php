<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST LẤY ĐƠN HÀNG CUỐI CÙNG ===\n\n";

try {
    // Lấy shop có integration hoạt động
    $shop = TikTokShop::find(12);
    $integration = $shop->integration;

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Status: {$shop->status}\n\n";

    echo "🔗 Integration:\n";
    echo "   ID: {$integration->id}\n";
    echo "   Status: {$integration->status}\n";
    echo "   App Key: {$integration->getAppKey()}\n";
    echo "   Access Token: " . substr($integration->access_token, 0, 30) . "...\n";
    echo "   Token Expires: " . date('Y-m-d H:i:s', $integration->access_token_expires_at) . "\n";
    echo "   Is Expired: " . ($integration->isAccessTokenExpired() ? 'YES' : 'NO') . "\n\n";

    // Test với timestamp mới nhất
    $timestamp = time();
    echo "⏰ Current Timestamp: {$timestamp}\n";
    echo "   Current Time: " . date('Y-m-d H:i:s', $timestamp) . "\n\n";

    // Test 1: Thử với format signature đơn giản nhất
    echo "📝 Test 1: Format signature đơn giản nhất\n";

    $appKey = $integration->getAppKey();
    $appSecret = $integration->getAppSecret();
    $shopCipher = $shop->getShopCipher();

    // Tạo signature theo format đơn giản nhất
    $apiPath = '/order/202309/orders/search';
    $params = [
        'app_key' => $appKey,
        'timestamp' => (string)$timestamp
    ];
    ksort($params);

    $paramString = '';
    foreach ($params as $key => $value) {
        $paramString .= $key . $value;
    }

    $input = $apiPath . $paramString;
    $stringToSign = $appSecret . $input . $appSecret;
    $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
    $hexSignature = bin2hex($signature);

    echo "   Signature: {$hexSignature}\n";

    // Test API call
    $result1 = testOrderAPIDirect($shop, $integration, $hexSignature, $timestamp, false);
    echo "   Result: " . ($result1['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    if ($result1['success']) {
        echo "   🎉 LẤY ĐƠN HÀNG THÀNH CÔNG!\n";
        $orderList = $result1['data']['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        if (!empty($orderList)) {
            echo "   📋 Đơn hàng đầu tiên:\n";
            $firstOrder = $orderList[0];
            echo "      Order ID: " . ($firstOrder['order_id'] ?? 'N/A') . "\n";
            echo "      Status: " . ($firstOrder['order_status'] ?? 'N/A') . "\n";
            echo "      Amount: " . ($firstOrder['total_amount'] ?? 'N/A') . "\n";
        }
    } else {
        echo "   Error: {$result1['error']}\n";
    }
    echo "\n";

    // Test 2: Thử với shop_cipher trong signature
    echo "📝 Test 2: Với shop_cipher trong signature\n";

    $params2 = [
        'app_key' => $appKey,
        'shop_cipher' => $shopCipher,
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

    echo "   Signature: {$hexSignature2}\n";

    $result2 = testOrderAPIDirect($shop, $integration, $hexSignature2, $timestamp, true);
    echo "   Result: " . ($result2['success'] ? '✅ SUCCESS' : '❌ FAILED') . "\n";
    if (!$result2['success']) {
        echo "   Error: {$result2['error']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

function testOrderAPIDirect($shop, $integration, $signature, $timestamp, $includeShopCipher)
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
            'page_size' => 5
        ];

        if ($includeShopCipher) {
            $queryParams['shop_cipher'] = $shop->getShopCipher();
        }

        ksort($queryParams);
        $queryString = http_build_query($queryParams);

        echo "   📤 Request URL: {$url}?{$queryString}\n";
        echo "   📤 Headers: " . json_encode($headers, JSON_PRETTY_PRINT) . "\n";

        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->withBody('{}', 'application/json')
            ->post($url . '?' . $queryString);

        $httpCode = $response->status();
        $responseData = $response->json();

        echo "   📥 Response Code: {$httpCode}\n";
        echo "   📥 Response: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

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
