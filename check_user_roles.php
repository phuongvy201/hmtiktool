<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== CHECK USER ROLES ===\n\n";

// Lấy user hiện tại
$user = User::find(1);
if (!$user) {
    echo "❌ Không tìm thấy user ID 1\n";
    exit;
}

echo "👤 User: {$user->name} (ID: {$user->id})\n";
echo "🏢 Team ID: {$user->team_id}\n";

// Kiểm tra roles hiện tại
echo "\n🏷️  Roles hiện tại:\n";
$roles = $user->roles;
if ($roles->isEmpty()) {
    echo "   ❌ User chưa có role nào\n";
} else {
    foreach ($roles as $role) {
        echo "   - {$role->name}\n";
    }
}

// Kiểm tra tất cả roles có sẵn
echo "\n📋 Tất cả roles có sẵn:\n";
$allRoles = Role::all();
if ($allRoles->isEmpty()) {
    echo "   ❌ Không có role nào trong hệ thống\n";
} else {
    foreach ($allRoles as $role) {
        echo "   - {$role->name}\n";
    }
}

// Tìm hoặc tạo role team-admin
$teamAdminRole = Role::firstOrCreate(['name' => 'team-admin']);
echo "\n✅ Role team-admin: " . ($teamAdminRole->exists ? "Đã tồn tại" : "Đã tạo mới") . "\n";

// Gán role team-admin cho user
$user->assignRole('team-admin');

echo "✅ Đã gán role team-admin cho user\n";

// Kiểm tra lại
echo "\n🏷️  Roles sau khi gán:\n";
$roles = $user->roles;
foreach ($roles as $role) {
    echo "   - {$role->name}\n";
}

// Test hasRole
echo "\n🔐 Test hasRole:\n";
echo "   - hasRole('team-admin'): " . ($user->hasRole('team-admin') ? '✅' : '❌') . "\n";
echo "   - hasRole('seller'): " . ($user->hasRole('seller') ? '✅' : '❌') . "\n";

echo "\n=== HOÀN THÀNH ===\n";
