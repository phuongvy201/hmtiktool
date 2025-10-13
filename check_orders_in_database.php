<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokOrder;
use App\Models\TikTokShop;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== KIỂM TRA ĐƠN HÀNG TRONG DATABASE ===\n\n";

try {
    // Lấy shop ID 12
    $shop = TikTokShop::find(12);

    if (!$shop) {
        echo "❌ Không tìm thấy shop ID 12\n";
        exit(1);
    }

    echo "🏪 Shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n\n";

    // Kiểm tra đơn hàng trong database
    $orders = TikTokOrder::where('tiktok_shop_id', $shop->id)->get();

    echo "📊 Thống kê đơn hàng:\n";
    echo "   Tổng số đơn hàng trong database: {$orders->count()}\n\n";

    if ($orders->count() > 0) {
        echo "📋 Danh sách đơn hàng:\n";
        foreach ($orders as $index => $order) {
            echo "   " . ($index + 1) . ". Order ID: {$order->order_id}\n";
            echo "      Order Number: {$order->order_number}\n";
            echo "      Status: {$order->order_status}\n";
            echo "      Buyer: {$order->buyer_username}\n";
            echo "      Amount: {$order->total_amount} {$order->currency}\n";
            echo "      Create Time: {$order->create_time}\n";
            echo "      Update Time: {$order->update_time}\n";
            echo "      Sync Status: {$order->sync_status}\n";
            echo "      Last Synced: {$order->last_synced_at}\n";
            echo "      ---\n";
        }
    } else {
        echo "📭 Không có đơn hàng nào trong database\n";
        echo "   Có thể do:\n";
        echo "   - Shop chưa có đơn hàng\n";
        echo "   - Đơn hàng cũ hơn 30 ngày (filter mặc định)\n";
        echo "   - API trả về 0 đơn hàng\n\n";
    }

    // Kiểm tra tất cả đơn hàng trong database (không filter theo shop)
    echo "📊 Thống kê tổng quan:\n";
    $totalOrders = TikTokOrder::count();
    echo "   Tổng số đơn hàng trong database: {$totalOrders}\n";

    if ($totalOrders > 0) {
        $shopsWithOrders = TikTokOrder::select('tiktok_shop_id')
            ->distinct()
            ->pluck('tiktok_shop_id')
            ->toArray();

        echo "   Số shops có đơn hàng: " . count($shopsWithOrders) . "\n";
        echo "   Shop IDs có đơn hàng: " . implode(', ', $shopsWithOrders) . "\n";

        // Hiển thị đơn hàng mới nhất
        $latestOrder = TikTokOrder::orderBy('created_at', 'DESC')->first();
        echo "   Đơn hàng mới nhất:\n";
        echo "      Order ID: {$latestOrder->order_id}\n";
        echo "      Shop ID: {$latestOrder->tiktok_shop_id}\n";
        echo "      Created: {$latestOrder->created_at}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== KIỂM TRA HOÀN THÀNH ===\n";
