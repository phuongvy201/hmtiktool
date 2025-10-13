<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST ORDERS VỚI BODY PARAMETERS ===\n\n";

try {
    // Lấy shop có integration hoạt động
    $shop = TikTokShop::find(12);
    $integration = $shop->integration;

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Integration Status: {$integration->status}\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Không có body parameters (rỗng)
    echo "📝 Test 1: Không có body parameters (rỗng)\n";
    $result1 = $orderService->searchOrders($shop, [], 5);

    if ($result1['success']) {
        echo "✅ Thành công!\n";
        $orderList = $result1['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
    } else {
        echo "❌ Thất bại: {$result1['message']}\n";
    }
    echo "\n";

    // Test 2: Có body parameters - filter order_status
    echo "📝 Test 2: Có body parameters - filter order_status = UNPAID\n";
    $result2 = $orderService->searchOrders($shop, ['order_status' => 'UNPAID'], 5);

    if ($result2['success']) {
        echo "✅ Thành công!\n";
        $orderList2 = $result2['data']['order_list'] ?? [];
        echo "   Số đơn hàng UNPAID: " . count($orderList2) . "\n";
    } else {
        echo "❌ Thất bại: {$result2['message']}\n";
    }
    echo "\n";

    // Test 3: Có body parameters - filter order_status khác
    echo "📝 Test 3: Có body parameters - filter order_status = PAID\n";
    $result3 = $orderService->searchOrders($shop, ['order_status' => 'PAID'], 5);

    if ($result3['success']) {
        echo "✅ Thành công!\n";
        $orderList3 = $result3['data']['order_list'] ?? [];
        echo "   Số đơn hàng PAID: " . count($orderList3) . "\n";
    } else {
        echo "❌ Thất bại: {$result3['message']}\n";
    }
    echo "\n";

    // Test 4: Có body parameters - filter thời gian
    echo "📝 Test 4: Có body parameters - filter thời gian (7 ngày gần đây)\n";
    $sevenDaysAgo = strtotime('-7 days');
    $result4 = $orderService->searchOrders($shop, ['create_time_ge' => $sevenDaysAgo], 5);

    if ($result4['success']) {
        echo "✅ Thành công!\n";
        $orderList4 = $result4['data']['order_list'] ?? [];
        echo "   Số đơn hàng 7 ngày gần đây: " . count($orderList4) . "\n";
    } else {
        echo "❌ Thất bại: {$result4['message']}\n";
    }
    echo "\n";

    // Test 5: Có body parameters - filter kết hợp
    echo "📝 Test 5: Có body parameters - filter kết hợp (UNPAID + thời gian)\n";
    $result5 = $orderService->searchOrders($shop, [
        'order_status' => 'UNPAID',
        'create_time_ge' => $sevenDaysAgo
    ], 5);

    if ($result5['success']) {
        echo "✅ Thành công!\n";
        $orderList5 = $result5['data']['order_list'] ?? [];
        echo "   Số đơn hàng UNPAID trong 7 ngày: " . count($orderList5) . "\n";
    } else {
        echo "❌ Thất bại: {$result5['message']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
