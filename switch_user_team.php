<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Team;

echo "=== SWITCH USER TO PHAN HIỂN TEAM ===\n\n";

// Lấy user hiện tại
$user = User::find(1);
if (!$user) {
    echo "❌ Không tìm thấy user ID 1\n";
    exit;
}

echo "👤 User hiện tại: {$user->name}\n";
echo "🏢 Team hiện tại: {$user->team_id} (Default Team)\n";

// Tìm team Phan Hiển
$phanHienTeam = Team::where('name', 'Phan Hiển')->first();
if (!$phanHienTeam) {
    echo "❌ Không tìm thấy team Phan Hiển\n";
    exit;
}

echo "🎯 Team đích: {$phanHienTeam->name} (ID: {$phanHienTeam->id})\n";

// Chuyển user sang team Phan Hiển
$user->team_id = $phanHienTeam->id;
$user->save();

echo "\n✅ Đã chuyển user sang team Phan Hiển\n";

// Kiểm tra lại
$user = User::find(1);
echo "👤 User sau khi chuyển: {$user->name}\n";
echo "🏢 Team mới: {$user->team_id}\n";

// Kiểm tra shops trong team này
$shops = \App\Models\TikTokShop::where('team_id', $phanHienTeam->id)->get();
echo "🏪 Shops trong team Phan Hiển: {$shops->count()}\n";
foreach ($shops as $shop) {
    echo "   - {$shop->shop_name} (ID: {$shop->id})\n";
}

// Kiểm tra đơn hàng
$orders = \App\Models\TikTokOrder::whereIn('tiktok_shop_id', $shops->pluck('id'))->get();
echo "📦 Đơn hàng có thể xem: {$orders->count()}\n";

echo "\n=== HOÀN THÀNH ===\n";
