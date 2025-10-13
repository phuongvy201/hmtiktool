<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST GỌI ORDERS API ===\n\n";

try {
    // Lấy shop có integration hoạt động (ID: 12)
    $shop = TikTokShop::find(12);

    if (!$shop) {
        echo "❌ Không tìm thấy shop ID 12\n";
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
    echo "   ID: {$integration->id}\n";
    echo "   Status: {$integration->status}\n";
    echo "   App Key: {$integration->getAppKey()}\n";
    echo "   Access Token: " . substr($integration->access_token, 0, 30) . "...\n";
    echo "   Token Expires: " . date('Y-m-d H:i:s', $integration->access_token_expires_at) . "\n";
    echo "   Is Expired: " . ($integration->isAccessTokenExpired() ? 'YES' : 'NO') . "\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Lấy đơn hàng cơ bản
    echo "📝 Test 1: Lấy đơn hàng cơ bản (5 đơn hàng)\n";
    $result = $orderService->searchOrders($shop, [], 5);

    if ($result['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList = $result['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        echo "   Có thêm trang: " . ($result['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList)) {
            echo "📋 Thông tin đơn hàng:\n";
            foreach ($orderList as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Order Number: " . ($order['order_number'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào\n";
        }
    } else {
        echo "❌ Lỗi: {$result['message']}\n\n";

        // Test 2: Thử với filters khác
        echo "📝 Test 2: Thử với filter trạng thái UNPAID\n";
        $result2 = $orderService->searchOrders($shop, ['order_status' => 'UNPAID'], 3);

        if ($result2['success']) {
            echo "✅ Lấy đơn hàng UNPAID thành công!\n";
            $orderList2 = $result2['data']['order_list'] ?? [];
            echo "   Số đơn hàng UNPAID: " . count($orderList2) . "\n";
        } else {
            echo "❌ Lỗi UNPAID: {$result2['message']}\n";
        }

        echo "\n📝 Test 3: Thử với filter thời gian (7 ngày gần đây)\n";
        $sevenDaysAgo = strtotime('-7 days');
        $result3 = $orderService->searchOrders($shop, ['create_time_ge' => $sevenDaysAgo], 3);

        if ($result3['success']) {
            echo "✅ Lấy đơn hàng 7 ngày gần đây thành công!\n";
            $orderList3 = $result3['data']['order_list'] ?? [];
            echo "   Số đơn hàng 7 ngày gần đây: " . count($orderList3) . "\n";
        } else {
            echo "❌ Lỗi 7 ngày: {$result3['message']}\n";
        }
    }

    // Test 4: Lấy đơn hàng từ database
    echo "\n📝 Test 4: Lấy đơn hàng từ database (đã lưu)\n";
    $storedResult = $orderService->getStoredOrders($shop, ['limit' => 10]);

    if ($storedResult['success']) {
        echo "✅ Lấy đơn hàng từ database thành công!\n";
        echo "   Số đơn hàng đã lưu: {$storedResult['count']}\n";

        if ($storedResult['count'] > 0) {
            echo "   📋 Danh sách đơn hàng đã lưu:\n";
            foreach ($storedResult['data']->take(3) as $order) {
                echo "      - Order ID: {$order->order_id}, Status: {$order->order_status}, Amount: {$order->total_amount}\n";
            }
        }
    } else {
        echo "❌ Lỗi database: Không thể lấy đơn hàng từ database\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
