<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "=== FIX USER ROLE ===\n\n";

// Lấy user hiện tại
$user = User::find(1);
if (!$user) {
    echo "❌ Không tìm thấy user ID 1\n";
    exit;
}

echo "👤 User trước khi sửa: {$user->name}\n";
echo "🏷️  Role hiện tại: " . ($user->role ?? 'Không có') . "\n";
echo "🏢 Team ID: " . ($user->team_id ?? 'Không có') . "\n";

// Cập nhật role thành team-admin
$user->role = 'team-admin';
$user->save();

echo "\n✅ Đã cập nhật role thành: {$user->role}\n";

// Kiểm tra lại
$user = User::find(1);
echo "👤 User sau khi sửa: {$user->name}\n";
echo "🏷️  Role mới: " . ($user->role ?? 'Không có') . "\n";
echo "🏢 Team ID: " . ($user->team_id ?? 'Không có') . "\n";

echo "\n=== HOÀN THÀNH ===\n";
