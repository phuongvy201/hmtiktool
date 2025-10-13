<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;
use Illuminate\Support\Facades\Http;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST ĐƠN GIẢN TIKTOK ORDERS API ===\n\n";

try {
    // Lấy shop đầu tiên (bỏ qua check status integration)
    $shop = TikTokShop::with('integration')->first();

    if (!$shop) {
        echo "❌ Không tìm thấy TikTok Shop nào\n";
        exit(1);
    }

    echo "✅ Sử dụng shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Integration Status: " . ($shop->integration->status ?? 'N/A') . "\n\n";

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    echo "=== TEST: Lấy đơn hàng (bỏ qua integration status) ===\n";
    
    // Tạo một test đơn giản bằng cách gọi trực tiếp method private
    $reflection = new ReflectionClass($orderService);
    $method = $reflection->getMethod('searchOrders');
    $method->setAccessible(true);
    
    $result = $method->invoke($orderService, $shop, [], 5);
    
    if ($result['success']) {
        echo "✅ Lấy đơn hàng thành công!\n";
        $orderList = $result['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        echo "   Có thêm trang: " . ($result['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        // Hiển thị thông tin đơn hàng
        if (!empty($orderList)) {
            echo "📋 Danh sách đơn hàng:\n";
            foreach (array_slice($orderList, 0, 3) as $index => $order) {
                echo "   " . ($index + 1) . ". Order ID: " . ($order['order_id'] ?? 'N/A') . "\n";
                echo "      Status: " . ($order['order_status'] ?? 'N/A') . "\n";
                echo "      Buyer: " . ($order['buyer_username'] ?? 'N/A') . "\n";
                echo "      Amount: " . ($order['total_amount'] ?? 'N/A') . " " . ($order['currency'] ?? 'GBP') . "\n";
                echo "      Create Time: " . (isset($order['create_time']) ? date('Y-m-d H:i:s', $order['create_time']) : 'N/A') . "\n";
                echo "      ---\n";
            }
        }
    } else {
        echo "❌ Lỗi khi lấy đơn hàng: {$result['message']}\n\n";
        
        // Hiển thị thêm thông tin debug
        echo "🔍 Debug Info:\n";
        echo "   Shop Cipher: {$shop->getShopCipher()}\n";
        echo "   Integration ID: {$shop->integration->id}\n";
        echo "   Access Token: " . substr($shop->integration->access_token, 0, 30) . "...\n";
        echo "   Token Expires: " . date('Y-m-d H:i:s', $shop->integration->access_token_expires_at) . "\n";
    }

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== TEST HOÀN THÀNH ===\n";
