<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\TikTokShop;
use App\Models\TikTokOrder;

echo "=== TEST FINAL PERMISSIONS ===\n\n";

// Lấy user hiện tại
$user = User::find(1);
if (!$user) {
    echo "❌ Không tìm thấy user ID 1\n";
    exit;
}

echo "👤 User: {$user->name} (ID: {$user->id})\n";
echo "🏢 Team ID: {$user->team_id}\n";
echo "🏷️  Primary Role: {$user->getPrimaryRoleNameAttribute()}\n";
echo "🔐 Has team-admin role: " . ($user->hasRole('team-admin') ? '✅' : '❌') . "\n";

// Test logic phân quyền như trong controller
$team = $user->team;
if (!$team) {
    echo "❌ User không thuộc team nào\n";
    exit;
}

echo "🏢 Team: {$team->name} (ID: {$team->id})\n";

// Lấy shops có thể truy cập (logic từ controller)
if ($user->hasRole('team-admin')) {
    echo "✅ User là team-admin\n";
    $shops = TikTokShop::where('team_id', $team->id)
        ->where('status', 'active')
        ->get();
    echo "🏪 Shops có thể truy cập: {$shops->count()}\n";
    foreach ($shops as $shop) {
        echo "   - {$shop->shop_name} (ID: {$shop->id})\n";
    }
    
    if ($shops->isNotEmpty()) {
        $shopIds = $shops->pluck('id')->toArray();
        $orders = TikTokOrder::whereIn('tiktok_shop_id', $shopIds)->get();
        echo "📦 Đơn hàng có thể xem: {$orders->count()}\n";
        foreach ($orders as $order) {
            echo "   - Order {$order->order_id} (Shop: {$order->shop->shop_name})\n";
        }
    }
} else {
    echo "❌ User không phải team-admin\n";
}

echo "\n=== HOÀN THÀNH ===\n";
