<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;
use App\Services\TikTokOrderService;
use Illuminate\Support\Facades\Log;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST TIKTOK ORDERS API ===\n\n";

try {
    // Lấy shop đầu tiên có integration hoạt động
    $shop = TikTokShop::with('integration')
        ->whereHas('integration', function($query) {
            $query->where('status', 'active');
        })
        ->first();

    if (!$shop) {
        echo "❌ Không tìm thấy TikTok Shop nào có integration hoạt động\n";
        exit(1);
    }

    echo "✅ Tìm thấy shop: {$shop->shop_name} (ID: {$shop->id})\n";
    echo "   Shop Cipher: {$shop->getShopCipher()}\n";
    echo "   Status: {$shop->status}\n\n";

    // Kiểm tra integration
    $integration = $shop->integration;
    if (!$integration) {
        echo "❌ Shop không có integration\n";
        exit(1);
    }

    echo "✅ Integration Status: {$integration->status}\n";
    echo "   Access Token: " . substr($integration->access_token, 0, 20) . "...\n";
    echo "   Access Token Expires: " . date('Y-m-d H:i:s', $integration->access_token_expires_at) . "\n\n";

    // Kiểm tra token có hết hạn không
    if ($integration->isAccessTokenExpired()) {
        echo "⚠️  Access token đã hết hạn, thử refresh...\n";
        $refreshResult = $integration->refreshAccessToken();
        if (!$refreshResult['success']) {
            echo "❌ Không thể refresh token: {$refreshResult['message']}\n";
            exit(1);
        }
        echo "✅ Refresh token thành công\n\n";
    } else {
        echo "✅ Access token còn hiệu lực\n\n";
    }

    // Khởi tạo service
    $orderService = new TikTokOrderService();

    echo "=== TEST 1: Lấy đơn hàng cơ bản ===\n";
    $result = $orderService->searchOrders($shop, [], 10);
    
    if ($result['success']) {
        echo "✅ Lấy đơn hàng thành công\n";
        $orderList = $result['data']['order_list'] ?? [];
        echo "   Số đơn hàng: " . count($orderList) . "\n";
        echo "   Có thêm trang: " . ($result['data']['more'] ?? false ? 'Có' : 'Không') . "\n\n";

        // Hiển thị thông tin một vài đơn hàng đầu tiên
        if (!empty($orderList)) {
            echo "📋 Thông tin đơn hàng đầu tiên:\n";
            $firstOrder = $orderList[0];
            echo "   Order ID: " . ($firstOrder['order_id'] ?? 'N/A') . "\n";
            echo "   Order Number: " . ($firstOrder['order_number'] ?? 'N/A') . "\n";
            echo "   Status: " . ($firstOrder['order_status'] ?? 'N/A') . "\n";
            echo "   Buyer: " . ($firstOrder['buyer_username'] ?? 'N/A') . "\n";
            echo "   Create Time: " . (isset($firstOrder['create_time']) ? date('Y-m-d H:i:s', $firstOrder['create_time']) : 'N/A') . "\n";
            echo "   Total Amount: " . ($firstOrder['total_amount'] ?? 'N/A') . " " . ($firstOrder['currency'] ?? 'GBP') . "\n\n";
        }
    } else {
        echo "❌ Lỗi khi lấy đơn hàng: {$result['message']}\n\n";
    }

    echo "=== TEST 2: Lấy đơn hàng với filter ===\n";
    $filters = [
        'order_status' => 'UNPAID',
        'create_time_ge' => strtotime('-7 days'), // 7 ngày gần đây
    ];
    
    $result2 = $orderService->searchOrders($shop, $filters, 5);
    
    if ($result2['success']) {
        echo "✅ Lấy đơn hàng với filter thành công\n";
        $orderList2 = $result2['data']['order_list'] ?? [];
        echo "   Số đơn hàng UNPAID: " . count($orderList2) . "\n\n";
    } else {
        echo "❌ Lỗi khi lấy đơn hàng với filter: {$result2['message']}\n\n";
    }

    echo "=== TEST 3: Lấy đơn hàng từ database ===\n";
    $storedResult = $orderService->getStoredOrders($shop, ['limit' => 10]);
    
    if ($storedResult['success']) {
        echo "✅ Lấy đơn hàng từ database thành công\n";
        echo "   Số đơn hàng đã lưu: {$storedResult['count']}\n\n";
    } else {
        echo "❌ Lỗi khi lấy đơn hàng từ database\n\n";
    }

    echo "=== TEST HOÀN THÀNH ===\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
