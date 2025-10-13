<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== LẤY TẤT CẢ ĐƠN HÀNG SHOP 12 ===\n\n";

try {
    // Lấy shop ID 12
    $shop = TikTokShop::find(12);

    if (!$shop) {
        echo "❌ Không tìm thấy shop ID 12\n";
        exit(1);
    }

    echo "🏪 Shop: {$shop->shop_name}\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Lấy đơn hàng với filter thời gian rộng (không có status filter)
    echo "📝 Test 1: Lấy đơn hàng với filter thời gian rộng (2 năm)\n";
    $twoYearsAgo = strtotime('-2 years');
    $result1 = $orderService->searchOrders($shop, ['create_time_ge' => $twoYearsAgo], 50);

    if ($result1['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList1 = $result1['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList1) . "\n";
        echo "   Có thêm trang: " . ($result1['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList1)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList1 as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Order Number: " . ($order['order_number'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 2 năm gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result1['message']}\n";
    }

    echo "\n";

    // Test 2: Lấy đơn hàng với filter thời gian rộng hơn (5 năm)
    echo "📝 Test 2: Lấy đơn hàng với filter thời gian rộng hơn (5 năm)\n";
    $fiveYearsAgo = strtotime('-5 years');
    $result2 = $orderService->searchOrders($shop, ['create_time_ge' => $fiveYearsAgo], 50);

    if ($result2['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList2 = $result2['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList2) . "\n";
        echo "   Có thêm trang: " . ($result2['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList2)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList2 as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Order Number: " . ($order['order_number'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 5 năm gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result2['message']}\n";
    }

    echo "\n";

    // Test 3: Lấy đơn hàng với filter thời gian rộng nhất (10 năm)
    echo "📝 Test 3: Lấy đơn hàng với filter thời gian rộng nhất (10 năm)\n";
    $tenYearsAgo = strtotime('-10 years');
    $result3 = $orderService->searchOrders($shop, ['create_time_ge' => $tenYearsAgo], 50);

    if ($result3['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList3 = $result3['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList3) . "\n";
        echo "   Có thêm trang: " . ($result3['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList3)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList3 as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Order Number: " . ($order['order_number'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 10 năm gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result3['message']}\n";
    }

    echo "\n";

    // Test 4: Lấy đơn hàng với filter thời gian rộng nhất có thể (20 năm)
    echo "📝 Test 4: Lấy đơn hàng với filter thời gian rộng nhất có thể (20 năm)\n";
    $twentyYearsAgo = strtotime('-20 years');
    $result4 = $orderService->searchOrders($shop, ['create_time_ge' => $twentyYearsAgo], 50);

    if ($result4['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList4 = $result4['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList4) . "\n";
        echo "   Có thêm trang: " . ($result4['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList4)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList4 as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Order Number: " . ($order['order_number'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 20 năm gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result4['message']}\n";
    }

    echo "\n";

    // Tổng kết
    echo "📊 TỔNG KẾT:\n";
    $totalOrders = count($orderList1 ?? []) + count($orderList2 ?? []) + count($orderList3 ?? []) + count($orderList4 ?? []);
    echo "   Tổng số đơn hàng tìm thấy: {$totalOrders}\n";

    if ($totalOrders > 0) {
        echo "   ✅ Shop này có đơn hàng!\n";

        // Kiểm tra database
        echo "\n📊 Kiểm tra database:\n";
        $ordersInDB = \App\Models\TikTokOrder::where('tiktok_shop_id', $shop->id)->count();
        echo "   Số đơn hàng trong database: {$ordersInDB}\n";

        if ($ordersInDB > 0) {
            echo "   ✅ Đơn hàng đã được lưu vào database\n";
        } else {
            echo "   📭 Chưa có đơn hàng nào trong database\n";
        }
    } else {
        echo "   📭 Shop này không có đơn hàng nào trong bất kỳ khoảng thời gian nào\n";
        echo "   Có thể shop này chưa có đơn hàng hoặc đang ở chế độ test\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
