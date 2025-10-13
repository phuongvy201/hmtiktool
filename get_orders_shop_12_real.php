<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== LẤY ĐƠN HÀNG THẬT SHOP 12 ===\n\n";

try {
    // Lấy shop ID 12
    $shop = TikTokShop::find(12);
    
    if (!$shop) {
        echo "❌ Không tìm thấy shop ID 12\n";
        exit(1);
    }

    echo "🏪 Shop: {$shop->shop_name}\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Status: {$shop->status}\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    // Test 1: Không có filter gì cả (chỉ có filter mặc định 30 ngày)
    echo "📝 Test 1: Không có filter gì cả (filter mặc định 30 ngày)\n";
    $result1 = $orderService->searchOrders($shop, [], 100); // Tăng page_size lên 100
    
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
            echo "   📭 Không có đơn hàng nào trong 30 ngày gần đây\n";
        }
    } else {
        echo "❌ Lỗi: {$result1['message']}\n";
    }

    echo "\n";

    // Test 2: Thử với page_size nhỏ hơn
    echo "📝 Test 2: Với page_size = 10\n";
    $result2 = $orderService->searchOrders($shop, [], 10);
    
    if ($result2['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList2 = $result2['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList2) . "\n";
        echo "   Có thêm trang: " . ($result2['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList2)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList2 as $index => $order) {
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
        echo "❌ Lỗi: {$result2['message']}\n";
    }

    echo "\n";

    // Test 3: Thử với page_size = 1
    echo "📝 Test 3: Với page_size = 1\n";
    $result3 = $orderService->searchOrders($shop, [], 1);
    
    if ($result3['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList3 = $result3['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList3) . "\n";
        echo "   Có thêm trang: " . ($result3['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList3)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList3 as $index => $order) {
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
        echo "❌ Lỗi: {$result3['message']}\n";
    }

    echo "\n";

    // Test 4: Thử với page_size = 5
    echo "📝 Test 4: Với page_size = 5\n";
    $result4 = $orderService->searchOrders($shop, [], 5);
    
    if ($result4['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList4 = $result4['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList4) . "\n";
        echo "   Có thêm trang: " . ($result4['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList4)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList4 as $index => $order) {
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
        echo "❌ Lỗi: {$result4['message']}\n";
    }

    echo "\n";

    // Test 5: Thử với page_size = 20 (mặc định)
    echo "📝 Test 5: Với page_size = 20 (mặc định)\n";
    $result5 = $orderService->searchOrders($shop, [], 20);
    
    if ($result5['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList5 = $result5['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList5) . "\n";
        echo "   Có thêm trang: " . ($result5['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList5)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList5 as $index => $order) {
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
        echo "❌ Lỗi: {$result5['message']}\n";
    }

    echo "\n";

    // Tổng kết
    echo "📊 TỔNG KẾT:\n";
    $totalOrders = count($orderList1 ?? []) + count($orderList2 ?? []) + count($orderList3 ?? []) + count($orderList4 ?? []) + count($orderList5 ?? []);
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
        echo "   📭 Vẫn không tìm thấy đơn hàng nào\n";
        echo "   Có thể:\n";
        echo "   - Đơn hàng cũ hơn 30 ngày (filter mặc định)\n";
        echo "   - Có vấn đề với API hoặc shop\n";
        echo "   - Cần kiểm tra lại thông tin shop\n";
    }

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
