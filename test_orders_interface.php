<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokOrder;
use App\Models\TikTokShop;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST GIAO DIỆN ĐƠN HÀNG ===\n\n";

try {
    // Kiểm tra đơn hàng trong database
    $totalOrders = TikTokOrder::count();
    echo "📊 Tổng số đơn hàng trong database: {$totalOrders}\n\n";

    if ($totalOrders > 0) {
        // Lấy 5 đơn hàng gần nhất
        $recentOrders = TikTokOrder::with('shop')
            ->orderBy('create_time', 'desc')
            ->limit(5)
            ->get();

        echo "📋 5 đơn hàng gần nhất:\n";
        foreach ($recentOrders as $index => $order) {
            echo "   " . ($index + 1) . ". Order ID: {$order->order_id}\n";
            echo "      Shop: " . ($order->shop->shop_name ?? 'N/A') . "\n";
            echo "      Status: {$order->order_status}\n";
            echo "      Amount: {$order->order_amount} {$order->currency}\n";
            echo "      Create Time: " . ($order->create_time ? $order->create_time->format('Y-m-d H:i:s') : 'N/A') . "\n";
            echo "      ---\n";
        }

        echo "\n";

        // Kiểm tra các shops có đơn hàng
        $shopsWithOrders = TikTokShop::whereHas('orders')->withCount('orders')->get();
        
        echo "🏪 Shops có đơn hàng:\n";
        foreach ($shopsWithOrders as $shop) {
            echo "   - {$shop->shop_name} (ID: {$shop->id}): {$shop->orders_count} đơn hàng\n";
        }

        echo "\n";

        // Test các method helper
        $firstOrder = $recentOrders->first();
        echo "🧪 Test các method helper:\n";
        echo "   Status Color: " . $firstOrder->getStatusColor() . "\n";
        echo "   Status Text: " . $firstOrder->getStatusText() . "\n";
        echo "   Shop Name: " . ($firstOrder->shop->shop_name ?? 'N/A') . "\n";

    } else {
        echo "❌ Không có đơn hàng nào trong database\n";
        echo "   Hãy chạy script đồng bộ đơn hàng trước\n";
    }

    echo "\n✅ Giao diện đã sẵn sàng!\n";
    echo "   Truy cập: /tiktok/orders để xem danh sách đơn hàng\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
