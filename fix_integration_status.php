<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== FIX INTEGRATION STATUS ===\n";

$integration = \App\Models\TikTokShopIntegration::find(10);

if (!$integration) {
    echo "❌ Integration không tồn tại\n";
    exit;
}

echo "Integration ID: {$integration->id}\n";
echo "Current Status: {$integration->status}\n";
echo "Current Access Token: " . substr($integration->access_token, 0, 20) . "...\n";
echo "Current Refresh Token: " . substr($integration->refresh_token, 0, 20) . "...\n";

// Thử cập nhật status về active
echo "\n🔄 Cập nhật status về 'active'...\n";
$integration->status = 'active';
$integration->save();

echo "✅ Đã cập nhật status thành 'active'\n";

// Kiểm tra lại
$integration->refresh();
echo "New Status: {$integration->status}\n";
echo "Is Active: " . ($integration->isActive() ? 'YES' : 'NO') . "\n";

// Thử refresh token
echo "\n🔄 Thử refresh token...\n";
try {
    $result = $integration->refreshAccessToken();
    
    if ($result['success']) {
        echo "✅ Refresh token thành công!\n";
        echo "New Access Token: " . substr($integration->access_token, 0, 20) . "...\n";
        echo "New Expires At: " . ($integration->access_expires_at ?? 'NULL') . "\n";
    } else {
        echo "❌ Refresh token thất bại: " . $result['message'] . "\n";
    }
} catch (\Exception $e) {
    echo "❌ Lỗi khi refresh token: " . $e->getMessage() . "\n";
}
