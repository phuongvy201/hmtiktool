<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== LẤY ĐƠN HÀNG SHOP 12 ===\n\n";

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

    // Lấy đơn hàng với filter thời gian rộng (1 năm)
    echo "📝 Lấy đơn hàng với filter 1 năm gần đây...\n";
    $oneYearAgo = strtotime('-1 year');
    $result = $orderService->searchOrders($shop, ['create_time_ge' => $oneYearAgo], 20);

    if ($result['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList = $result['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        echo "   Có thêm trang: " . ($result['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        if (!empty($orderList)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach ($orderList as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Order Number: " . ($order['order_number'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      Update Time: " . (isset($order['update_time']) ? date('Y-m-d H:i:s', $order['update_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }

            // Kiểm tra database sau khi lấy đơn hàng
            echo "\n📊 Kiểm tra database:\n";
            $ordersInDB = \App\Models\TikTokOrder::where('tiktok_shop_id', $shop->id)->count();
            echo "   Số đơn hàng trong database: {$ordersInDB}\n";

            if ($ordersInDB > 0) {
                echo "   ✅ Đơn hàng đã được lưu vào database\n";
            } else {
                echo "   📭 Chưa có đơn hàng nào trong database\n";
            }
        } else {
            echo "   📭 Không có đơn hàng nào trong 1 năm gần đây\n";
            echo "   Có thể shop này chưa có đơn hàng hoặc đang ở chế độ test\n";
        }
    } else {
        echo "❌ Lỗi: {$result['message']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
