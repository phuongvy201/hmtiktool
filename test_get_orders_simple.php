<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST LẤY ĐƠN HÀNG ĐỀN GIẢN ===\n\n";

try {
    // Lấy shop đầu tiên
    $shop = TikTokShop::with('integration')->first();
    
    if (!$shop) {
        echo "❌ Không tìm thấy shop nào\n";
        exit(1);
    }

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Status: {$shop->status}\n\n";

    $integration = $shop->integration;
    if (!$integration) {
        echo "❌ Shop không có integration\n";
        exit(1);
    }

    echo "🔗 Integration:\n";
    echo "   Status: {$integration->status}\n";
    echo "   App Key: {$integration->getAppKey()}\n";
    echo "   Access Token: " . substr($integration->access_token, 0, 30) . "...\n";
    echo "   Token Expires: " . date('Y-m-d H:i:s', $integration->access_token_expires_at) . "\n\n";

    // Test 1: Sử dụng TikTokOrderService
    echo "📝 Test 1: Sử dụng TikTokOrderService\n";
    $orderService = new TikTokOrderService();
    
    // Tạm thời comment out check integration status
    $result = $orderService->searchOrders($shop, [], 5);
    
    if ($result['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList = $result['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        echo "   Có thêm trang: " . ($result['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList)) {
            echo "📋 Thông tin đơn hàng:\n";
            foreach (array_slice($orderList, 0, 3) as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        }
    } else {
        echo "❌ Lỗi: {$result['message']}\n\n";
        
        // Test 2: Thử gọi API trực tiếp với signature đơn giản
        echo "📝 Test 2: Gọi API trực tiếp\n";
        
        $timestamp = time();
        $appKey = $integration->getAppKey();
        $appSecret = $integration->getAppSecret();
        $shopCipher = $shop->getShopCipher();
        
        // Tạo signature đơn giản
        $params = [
            'app_key' => $appKey,
            'timestamp' => (string)$timestamp
        ];
        ksort($params);
        
        $paramString = '';
        foreach ($params as $key => $value) {
            $paramString .= $key . $value;
        }
        
        $apiPath = '/order/202309/orders/search';
        $input = $apiPath . $paramString;
        $stringToSign = $appSecret . $input . $appSecret;
        $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
        $hexSignature = bin2hex($signature);
        
        echo "   Signature: {$hexSignature}\n";
        
        // Gọi API
        $url = 'https://open-api.tiktokglobalshop.com/order/202309/orders/search';
        $headers = [
            'Content-Type' => 'application/json',
            'x-tts-access-token' => $integration->access_token
        ];
        
        $queryParams = [
            'shop_cipher' => $shopCipher,
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'sign' => $hexSignature,
            'page_size' => 5
        ];
        
        ksort($queryParams);
        $queryString = http_build_query($queryParams);
        
        $response = Http::withHeaders($headers)
            ->timeout(30)
            ->withBody('{}', 'application/json')
            ->post($url . '?' . $queryString);
        
        $httpCode = $response->status();
        $responseData = $response->json();
        
        echo "   Status Code: {$httpCode}\n";
        echo "   Response: " . json_encode($responseData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
            echo "   ✅ API call thành công!\n";
            $orderList = $responseData['data']['order_list'] ?? [];
            echo "   Số đơn hàng: " . count($orderList) . "\n";
        } else {
            echo "   ❌ API call thất bại\n";
        }
    }

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
