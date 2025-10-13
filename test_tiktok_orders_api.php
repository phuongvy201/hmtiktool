<?php

/**
 * File test để demo cách sử dụng TikTok Order API
 * 
 * Cách sử dụng:
 * 1. Chạy migration: php artisan migrate
 * 2. Chạy file này: php test_tiktok_orders_api.php
 */

require_once 'vendor/autoload.php';

use App\Services\TikTokOrderService;
use App\Services\TikTokShopService;
use App\Models\TikTokShop;
use App\Models\TikTokOrder;

// Khởi tạo Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TIKTOK ORDER API TEST ===\n\n";

try {
    // Lấy shop đầu tiên để test
    $shop = TikTokShop::with('integration')->first();

    if (!$shop) {
        echo "❌ Không tìm thấy TikTok Shop nào trong database\n";
        echo "Vui lòng tạo shop trước khi test API orders\n";
        exit(1);
    }

    if (!$shop->integration) {
        echo "❌ Shop không có integration\n";
        echo "Vui lòng tạo integration cho shop trước\n";
        exit(1);
    }

    echo "✅ Tìm thấy shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "✅ Integration: {$shop->integration->app_name}\n\n";

    // Khởi tạo services
    $tiktokOrderService = new TikTokOrderService();
    $tiktokShopService = new TikTokShopService();

    // Test 1: Tìm kiếm đơn hàng với filters cơ bản
    echo "🔍 Test 1: Tìm kiếm đơn hàng cơ bản\n";
    echo "----------------------------------------\n";

    $filters = [
        'order_status' => 'UNPAID',
        'create_time_ge' => strtotime('-7 days'), // 7 ngày gần đây
        'create_time_lt' => time()
    ];

    $result = $tiktokOrderService->searchOrders($shop, $filters, 10);

    if ($result['success']) {
        $orderList = $result['data']['order_list'] ?? [];
        echo "✅ Tìm thấy " . count($orderList) . " đơn hàng\n";

        if (!empty($orderList)) {
            echo "\n📋 Danh sách đơn hàng:\n";
            foreach ($orderList as $index => $order) {
                echo sprintf(
                    "%d. Order ID: %s | Status: %s | Amount: %s %s | Buyer: %s\n",
                    $index + 1,
                    $order['order_id'] ?? 'N/A',
                    $order['order_status'] ?? 'N/A',
                    $order['order_amount'] ?? '0',
                    $order['currency'] ?? 'GBP',
                    $order['buyer_username'] ?? 'N/A'
                );
            }
        }
    } else {
        echo "❌ Lỗi: " . $result['message'] . "\n";
    }

    echo "\n";

    // Test 2: Tìm kiếm đơn hàng theo trạng thái
    echo "🔍 Test 2: Tìm kiếm đơn hàng theo trạng thái\n";
    echo "----------------------------------------\n";

    $statuses = ['UNPAID', 'AWAITING_SHIPMENT', 'IN_TRANSIT', 'DELIVERED'];

    foreach ($statuses as $status) {
        $result = $tiktokOrderService->getOrdersByStatus($shop, $status, 5);

        if ($result['success']) {
            $orderList = $result['data']['order_list'] ?? [];
            echo "✅ Trạng thái '{$status}': " . count($orderList) . " đơn hàng\n";
        } else {
            echo "❌ Trạng thái '{$status}': " . $result['message'] . "\n";
        }
    }

    echo "\n";

    // Test 3: Tìm kiếm đơn hàng theo khoảng thời gian
    echo "🔍 Test 3: Tìm kiếm đơn hàng theo khoảng thời gian\n";
    echo "----------------------------------------\n";

    $startTime = strtotime('-30 days');
    $endTime = time();

    $result = $tiktokOrderService->getOrdersByTimeRange($shop, $startTime, $endTime, 10);

    if ($result['success']) {
        $orderList = $result['data']['order_list'] ?? [];
        echo "✅ Đơn hàng trong 30 ngày qua: " . count($orderList) . " đơn hàng\n";
    } else {
        echo "❌ Lỗi: " . $result['message'] . "\n";
    }

    echo "\n";

    // Test 4: Đồng bộ tất cả đơn hàng (cẩn thận với API rate limit)
    echo "🔍 Test 4: Đồng bộ đơn hàng (chỉ 1 trang để test)\n";
    echo "----------------------------------------\n";

    // Chỉ đồng bộ đơn hàng trong 7 ngày qua để tránh quá tải
    $syncFilters = [
        'create_time_ge' => strtotime('-7 days'),
        'create_time_lt' => time()
    ];

    echo "⚠️  Bắt đầu đồng bộ đơn hàng (có thể mất vài phút)...\n";

    $result = $tiktokOrderService->syncAllOrders($shop, $syncFilters);

    if ($result['success']) {
        echo "✅ Đồng bộ thành công: " . $result['total_orders'] . " đơn hàng\n";
    } else {
        echo "❌ Lỗi đồng bộ: " . $result['message'] . "\n";
    }

    echo "\n";

    // Test 5: Lấy đơn hàng từ database (đã lưu)
    echo "🔍 Test 5: Lấy đơn hàng từ database\n";
    echo "----------------------------------------\n";

    $storedOrders = $tiktokOrderService->getStoredOrders($shop, [
        'limit' => 10
    ]);

    if ($storedOrders['success']) {
        $orders = $storedOrders['data'];
        echo "✅ Tìm thấy " . $orders->count() . " đơn hàng trong database\n";

        if ($orders->count() > 0) {
            echo "\n📋 Đơn hàng đã lưu:\n";
            foreach ($orders as $order) {
                echo sprintf(
                    "- Order ID: %s | Status: %s (%s) | Amount: %s %s | Created: %s\n",
                    $order->order_id,
                    $order->order_status,
                    $order->status_in_vietnamese,
                    $order->order_amount,
                    $order->currency,
                    $order->create_time ? $order->create_time->format('Y-m-d H:i:s') : 'N/A'
                );
            }
        }
    } else {
        echo "❌ Lỗi: " . $storedOrders['message'] . "\n";
    }

    echo "\n";

    // Test 6: Thống kê đơn hàng
    echo "🔍 Test 6: Thống kê đơn hàng\n";
    echo "----------------------------------------\n";

    $totalOrders = TikTokOrder::where('tiktok_shop_id', $shop->id)->count();
    $ordersByStatus = TikTokOrder::where('tiktok_shop_id', $shop->id)
        ->selectRaw('order_status, COUNT(*) as count')
        ->groupBy('order_status')
        ->get();

    echo "✅ Tổng số đơn hàng: {$totalOrders}\n";
    echo "\n📊 Phân bố theo trạng thái:\n";

    foreach ($ordersByStatus as $status) {
        echo "- {$status->order_status}: {$status->count} đơn hàng\n";
    }

    echo "\n";

    // Test 7: Sử dụng TikTokShopService trực tiếp
    echo "🔍 Test 7: Sử dụng TikTokShopService trực tiếp\n";
    echo "----------------------------------------\n";

    $result = $tiktokShopService->searchOrders(
        $shop->integration,
        $shop->id,
        ['order_status' => 'UNPAID'],
        5
    );

    if ($result['success']) {
        $orderList = $result['data']['order_list'] ?? [];
        echo "✅ TikTokShopService: Tìm thấy " . count($orderList) . " đơn hàng UNPAID\n";
    } else {
        echo "❌ TikTokShopService: " . $result['message'] . "\n";
    }

    echo "\n";

    echo "🎉 Hoàn thành tất cả test!\n";
    echo "========================\n";
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n📝 Hướng dẫn sử dụng:\n";
echo "1. TikTokOrderService: Service chính để làm việc với đơn hàng\n";
echo "2. TikTokShopService: Service tổng quát, có method searchOrders\n";
echo "3. TikTokOrder model: Model để truy vấn đơn hàng đã lưu\n";
echo "4. Migration: Chạy 'php artisan migrate' để tạo bảng tiktok_orders\n";
echo "\n📚 Các method chính:\n";
echo "- searchOrders(): Tìm kiếm đơn hàng với filters\n";
echo "- getOrdersByStatus(): Lấy đơn hàng theo trạng thái\n";
echo "- getOrdersByTimeRange(): Lấy đơn hàng theo khoảng thời gian\n";
echo "- syncAllOrders(): Đồng bộ tất cả đơn hàng\n";
echo "- getStoredOrders(): Lấy đơn hàng từ database\n";
