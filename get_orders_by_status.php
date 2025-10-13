<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== LẤY ĐƠN HÀNG THEO STATUS - SHOP 12 ===\n\n";

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

    // Danh sách các status có thể có
    $orderStatuses = [
        'UNPAID' => 'Chưa thanh toán',
        'PAID' => 'Đã thanh toán',
        'SHIPPED' => 'Đã vận chuyển',
        'DELIVERED' => 'Đã giao hàng',
        'CANCELLED' => 'Đã hủy',
        'REFUNDED' => 'Đã hoàn tiền',
        'COMPLETED' => 'Hoàn thành',
        'PENDING' => 'Chờ xử lý',
        'PROCESSING' => 'Đang xử lý'
    ];

    $totalOrders = 0;
    $foundOrders = [];

    echo "📝 Lấy đơn hàng theo từng status...\n\n";

    foreach ($orderStatuses as $status => $description) {
        echo "🔍 Kiểm tra status: {$status} ({$description})\n";

        $result = $orderService->searchOrders($shop, ['order_status' => $status], 20);

        if ($result['success']) {
            $orderList = $result['data']['order_list'] ?? [];
            $count = count($orderList);

            if ($count > 0) {
                echo "   ✅ Tìm thấy {$count} đơn hàng\n";
                $foundOrders[$status] = $orderList;
                $totalOrders += $count;

                // Hiển thị thông tin đơn hàng đầu tiên
                $firstOrder = $orderList[0];
                echo "      📋 Đơn hàng đầu tiên:\n";
                echo "         Order ID: " . ($firstOrder['order_id'] ?? 'N/A') . "\n";
                echo "         Buyer: " . ($firstOrder['buyer_username'] ?? 'N/A') . "\n";
                echo "         Amount: " . ($firstOrder['total_amount'] ?? 'N/A') . " " . ($firstOrder['currency'] ?? 'GBP') . "\n";
                echo "         Create Time: " . (isset($firstOrder['create_time']) ? date('Y-m-d H:i:s', $firstOrder['create_time']) : 'N/A') . "\n";
            } else {
                echo "   📭 Không có đơn hàng nào\n";
            }
        } else {
            echo "   ❌ Lỗi: {$result['message']}\n";
        }
        echo "\n";
    }

    // Tổng kết
    echo "📊 TỔNG KẾT:\n";
    echo "   Tổng số đơn hàng tìm thấy: {$totalOrders}\n";

    if ($totalOrders > 0) {
        echo "   Các status có đơn hàng:\n";
        foreach ($foundOrders as $status => $orders) {
            echo "      - {$status}: " . count($orders) . " đơn hàng\n";
        }

        // Kiểm tra database
        echo "\n📊 Kiểm tra database:\n";
        $ordersInDB = \App\Models\TikTokOrder::where('tiktok_shop_id', $shop->id)->count();
        echo "   Số đơn hàng trong database: {$ordersInDB}\n";

        if ($ordersInDB > 0) {
            echo "   ✅ Đơn hàng đã được lưu vào database\n";

            // Hiển thị đơn hàng theo status trong database
            $dbOrders = \App\Models\TikTokOrder::where('tiktok_shop_id', $shop->id)->get();
            $dbStatusCount = [];
            foreach ($dbOrders as $order) {
                $status = $order->order_status ?? 'UNKNOWN';
                $dbStatusCount[$status] = ($dbStatusCount[$status] ?? 0) + 1;
            }

            echo "   Đơn hàng trong database theo status:\n";
            foreach ($dbStatusCount as $status => $count) {
                echo "      - {$status}: {$count} đơn hàng\n";
            }
        } else {
            echo "   📭 Chưa có đơn hàng nào trong database\n";
        }
    } else {
        echo "   📭 Shop này không có đơn hàng nào với bất kỳ status nào\n";
        echo "   Có thể shop này chưa có đơn hàng hoặc đang ở chế độ test\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
