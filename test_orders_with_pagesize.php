<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST ORDERS VỚI PAGE_SIZE TRONG SIGNATURE ===\n\n";

try {
    // Lấy shop có integration hoạt động
    $shop = TikTokShop::find(12);
    $integration = $shop->integration;

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Integration Status: {$integration->status}\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Lấy đơn hàng với page_size = 5
    echo "📝 Test 1: Lấy đơn hàng với page_size = 5\n";
    $result1 = $orderService->searchOrders($shop, [], 5);

    if ($result1['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList = $result1['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        echo "   Có thêm trang: " . ($result1['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList)) {
            echo "📋 Thông tin đơn hàng:\n";
            foreach ($orderList as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
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
        echo "❌ Lỗi: {$result1['message']}\n\n";

        // Test 2: Thử với page_size khác
        echo "📝 Test 2: Thử với page_size = 3\n";
        $result2 = $orderService->searchOrders($shop, [], 3);

        if ($result2['success']) {
            echo "✅ Lấy đơn hàng thành công với page_size = 3!\n";
            $orderList2 = $result2['data']['order_list'] ?? [];
            echo "   Số đơn hàng: " . count($orderList2) . "\n";
        } else {
            echo "❌ Lỗi với page_size = 3: {$result2['message']}\n";
        }

        echo "\n📝 Test 3: Thử với page_size = 1\n";
        $result3 = $orderService->searchOrders($shop, [], 1);

        if ($result3['success']) {
            echo "✅ Lấy đơn hàng thành công với page_size = 1!\n";
            $orderList3 = $result3['data']['order_list'] ?? [];
            echo "   Số đơn hàng: " . count($orderList3) . "\n";
        } else {
            echo "❌ Lỗi với page_size = 1: {$result3['message']}\n";
        }
    }

    // Test 4: Test với filters
    echo "\n📝 Test 4: Lấy đơn hàng với filter trạng thái UNPAID\n";
    $result4 = $orderService->searchOrders($shop, ['order_status' => 'UNPAID'], 3);

    if ($result4['success']) {
        echo "✅ Lấy đơn hàng UNPAID thành công!\n";
        $orderList4 = $result4['data']['order_list'] ?? [];
        echo "   Số đơn hàng UNPAID: " . count($orderList4) . "\n";
    } else {
        echo "❌ Lỗi UNPAID: {$result4['message']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
