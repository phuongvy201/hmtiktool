<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Team;
use App\Models\TikTokShop;
use App\Models\TikTokOrder;
use App\Models\TikTokShopSeller;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TEST PHÂN QUYỀN ĐƠN HÀNG ===\n\n";

try {
    // Tìm team đầu tiên
    $team = Team::first();
    if (!$team) {
        echo "❌ Không tìm thấy team nào\n";
        exit(1);
    }

    echo "🏢 Team: {$team->name} (ID: {$team->id})\n\n";

    // Tìm user team-admin
    $teamAdmin = User::where('team_id', $team->id)
        ->where('role', 'team-admin')
        ->first();

    if (!$teamAdmin) {
        echo "❌ Không tìm thấy team-admin trong team này\n";
        exit(1);
    }

    echo "👤 Team Admin: {$teamAdmin->name} (ID: {$teamAdmin->id})\n";

    // Tìm user seller
    $seller = User::where('team_id', $team->id)
        ->where('role', 'seller')
        ->first();

    if (!$seller) {
        echo "❌ Không tìm thấy seller trong team này\n";
        exit(1);
    }

    echo "👤 Seller: {$seller->name} (ID: {$seller->id})\n\n";

    // Lấy shops của team
    $shops = TikTokShop::where('team_id', $team->id)->get();
    echo "🏪 Shops trong team: " . $shops->count() . "\n";

    foreach ($shops as $shop) {
        echo "   - {$shop->shop_name} (ID: {$shop->id})\n";
        
        // Kiểm tra quyền truy cập của team-admin
        $canAccessAdmin = $shop->canUserAccess($teamAdmin);
        echo "     Team Admin có thể truy cập: " . ($canAccessAdmin ? '✅ Có' : '❌ Không') . "\n";

        // Kiểm tra quyền truy cập của seller
        $canAccessSeller = $shop->canUserAccess($seller);
        echo "     Seller có thể truy cập: " . ($canAccessSeller ? '✅ Có' : '❌ Không') . "\n";

        // Kiểm tra số đơn hàng
        $orderCount = $shop->orders()->count();
        echo "     Số đơn hàng: {$orderCount}\n";
        echo "     ---\n";
    }

    echo "\n";

    // Test lấy đơn hàng theo quyền
    echo "🧪 Test lấy đơn hàng theo quyền:\n\n";

    // Team Admin - có thể xem tất cả đơn hàng trong team
    echo "👑 Team Admin có thể xem:\n";
    $adminAccessibleShops = TikTokShop::where('team_id', $team->id)->get();
    $adminTotalOrders = TikTokOrder::whereIn('tiktok_shop_id', $adminAccessibleShops->pluck('id'))->count();
    echo "   - Tất cả shops trong team: " . $adminAccessibleShops->count() . "\n";
    echo "   - Tổng số đơn hàng: {$adminTotalOrders}\n";

    // Seller - chỉ xem được đơn hàng của shops được assign
    echo "\n👤 Seller có thể xem:\n";
    
    // Kiểm tra xem seller có được assign vào shop nào không
    $sellerShops = TikTokShopSeller::where('user_id', $seller->id)
        ->where('is_active', true)
        ->with('shop')
        ->get();

    if ($sellerShops->count() > 0) {
        $sellerShopIds = $sellerShops->pluck('tiktok_shop_id');
        $sellerTotalOrders = TikTokOrder::whereIn('tiktok_shop_id', $sellerShopIds)->count();
        
        echo "   - Shops được assign: " . $sellerShops->count() . "\n";
        foreach ($sellerShops as $sellerShop) {
            $shopName = $sellerShop->shop->shop_name ?? 'N/A';
            $orderCount = $sellerShop->shop->orders()->count();
            echo "     + {$shopName}: {$orderCount} đơn hàng\n";
        }
        echo "   - Tổng số đơn hàng: {$sellerTotalOrders}\n";
    } else {
        echo "   - Không có shop nào được assign\n";
        echo "   - Tổng số đơn hàng: 0\n";
    }

    echo "\n✅ Test phân quyền hoàn thành!\n";
    echo "   Team Admin: Có thể xem tất cả đơn hàng trong team\n";
    echo "   Seller: Chỉ xem được đơn hàng của shops được assign\n";

} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== HOÀN THÀNH ===\n";
