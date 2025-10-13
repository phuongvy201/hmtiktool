<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Services\TikTokOrderService;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DEBUG RESPONSE STRUCTURE ===\n\n";

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

    // Test với filter thời gian rộng
    $veryOldTime = strtotime('-100 years');
    $result = $orderService->searchOrders($shop, ['create_time_ge' => $veryOldTime], 100);

    if ($result['success']) {
        echo "✅ API call thành công!\n\n";

        // In ra cấu trúc response
        echo "📊 Cấu trúc response:\n";
        echo "   - Keys trong data: " . implode(', ', array_keys($result['data'])) . "\n\n";

        if (isset($result['data']['orders'])) {
            echo "✅ Tìm thấy key 'orders' trong response!\n";
            $orders = $result['data']['orders'];
            echo "   Số đơn hàng: " . count($orders) . "\n";
            echo "   Total count: " . ($result['data']['total_count'] ?? 'N/A') . "\n";
            echo "   Next page token: " . ($result['data']['next_page_token'] ?? 'N/A') . "\n\n";

            // Hiển thị thông tin đơn hàng đầu tiên
            if (!empty($orders)) {
                $firstOrder = $orders[0];
                echo "📋 Đơn hàng đầu tiên:\n";
                echo "   Order ID: " . ($firstOrder['id'] ?? 'N/A') . "\n";
                echo "   Status: " . ($firstOrder['status'] ?? 'N/A') . "\n";
                echo "   Buyer Email: " . ($firstOrder['buyer_email'] ?? 'N/A') . "\n";
                echo "   Total Amount: " . ($firstOrder['payment']['total_amount'] ?? 'N/A') . " " . ($firstOrder['payment']['currency'] ?? 'GBP') . "\n";
                echo "   Create Time: " . (isset($firstOrder['create_time']) ? date('Y-m-d H:i:s', $firstOrder['create_time']) : 'N/A') . "\n";
                echo "   Line Items: " . count($firstOrder['line_items'] ?? []) . "\n";
            }
        } else {
            echo "❌ Không tìm thấy key 'orders' trong response!\n";
            echo "   Available keys: " . implode(', ', array_keys($result['data'])) . "\n";
        }

        // Kiểm tra xem có key 'order_list' không
        if (isset($result['data']['order_list'])) {
            echo "✅ Tìm thấy key 'order_list' trong response!\n";
            $orderList = $result['data']['order_list'];
            echo "   Số đơn hàng trong order_list: " . count($orderList) . "\n";
        } else {
            echo "❌ Không tìm thấy key 'order_list' trong response!\n";
        }
    } else {
        echo "❌ API call thất bại: {$result['message']}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
