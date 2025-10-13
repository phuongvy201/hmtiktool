<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST ORDERS VỚI BODY PARAMETER MẶC ĐỊNH ===\n\n";

try {
    // Lấy shop có integration hoạt động
    $shop = TikTokShop::find(12);
    $integration = $shop->integration;

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Integration Status: {$integration->status}\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Không có filter nào (sẽ tự động thêm filter mặc định)
    echo "📝 Test 1: Không có filter nào (tự động thêm filter mặc định)\n";
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
            echo "   📭 Không có đơn hàng nào trong 30 ngày gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result1['message']}\n";
    }

    echo "\n";

    // Test 2: Có filter cụ thể
    echo "📝 Test 2: Có filter cụ thể (UNPAID)\n";
    $result2 = $orderService->searchOrders($shop, ['order_status' => 'UNPAID'], 3);

    if ($result2['success']) {
        echo "✅ Lấy đơn hàng UNPAID thành công!\n";
        $orderList2 = $result2['data']['order_list'] ?? [];
        echo "   Số đơn hàng UNPAID: " . count($orderList2) . "\n";
    } else {
        echo "❌ Lỗi UNPAID: {$result2['message']}\n";
    }

    echo "\n";

    // Test 3: Test với page size khác
    echo "📝 Test 3: Test với page_size = 10\n";
    $result3 = $orderService->searchOrders($shop, [], 10);

    if ($result3['success']) {
        echo "✅ Lấy đơn hàng với page_size = 10 thành công!\n";
        $orderList3 = $result3['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList3) . "\n";
    } else {
        echo "❌ Lỗi với page_size = 10: {$result3['message']}\n";
    }

    echo "\n";

    // Test 4: Test syncAllOrders
    echo "📝 Test 4: Test syncAllOrders (đồng bộ tất cả đơn hàng)\n";
    $result4 = $orderService->syncAllOrders($shop, []);

    if ($result4['success']) {
        echo "✅ Đồng bộ tất cả đơn hàng thành công!\n";
        echo "   Tổng số đơn hàng: {$result4['total_orders']}\n";
    } else {
        echo "❌ Lỗi syncAllOrders: {$result4['message']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
