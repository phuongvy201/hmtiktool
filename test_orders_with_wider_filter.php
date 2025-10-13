<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST LẤY ĐƠN HÀNG VỚI FILTER THỜI GIAN RỘNG ===\n\n";

try {
    // Lấy shop có integration hoạt động
    $shop = TikTokShop::find(12);
    $integration = $shop->integration;

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Integration Status: {$integration->status}\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Filter 90 ngày gần đây
    echo "📝 Test 1: Filter 90 ngày gần đây\n";
    $ninetyDaysAgo = strtotime('-90 days');
    $result1 = $orderService->searchOrders($shop, ['create_time_ge' => $ninetyDaysAgo], 20);

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
            echo "   📭 Không có đơn hàng nào trong 90 ngày gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result1['message']}\n";
    }

    echo "\n";

    // Test 2: Filter 1 năm gần đây
    echo "📝 Test 2: Filter 1 năm gần đây\n";
    $oneYearAgo = strtotime('-1 year');
    $result2 = $orderService->searchOrders($shop, ['create_time_ge' => $oneYearAgo], 20);

    if ($result2['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList2 = $result2['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList2) . "\n";
        echo "   Có thêm trang: " . ($result2['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList2)) {
            echo "📋 Thông tin đơn hàng:\n";
            foreach ($orderList2 as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 1 năm gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result2['message']}\n";
    }

    echo "\n";

    // Test 3: Không có filter thời gian (chỉ có filter mặc định)
    echo "📝 Test 3: Không có filter thời gian (chỉ có filter mặc định 30 ngày)\n";
    $result3 = $orderService->searchOrders($shop, [], 20);

    if ($result3['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList3 = $result3['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList3) . "\n";
        echo "   Có thêm trang: " . ($result3['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList3)) {
            echo "📋 Thông tin đơn hàng:\n";
            foreach ($orderList3 as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 30 ngày gần đây (filter mặc định)\n";
        }
    } else {
        echo "❌ Lỗi: {$result3['message']}\n";
    }

    echo "\n";

    // Test 4: Kiểm tra database sau khi gọi API
    echo "📝 Test 4: Kiểm tra database sau khi gọi API\n";
    $ordersInDB = \App\Models\TikTokOrder::where('tiktok_shop_id', $shop->id)->count();
    echo "   Số đơn hàng trong database: {$ordersInDB}\n";

    if ($ordersInDB > 0) {
        echo "   ✅ Có đơn hàng đã được lưu vào database\n";

        $latestOrder = \App\Models\TikTokOrder::where('tiktok_shop_id', $shop->id)
            ->orderBy('created_at', 'DESC')
            ->first();

        echo "   Đơn hàng mới nhất:\n";
        echo "      Order ID: {$latestOrder->order_id}\n";
        echo "      Status: {$latestOrder->order_status}\n";
        echo "      Created: {$latestOrder->created_at}\n";
    } else {
        echo "   📭 Chưa có đơn hàng nào được lưu vào database\n";
        echo "   Lý do: API trả về 0 đơn hàng, không có gì để lưu\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
