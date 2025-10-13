<?php

/**
 * Script tạo authorization link mới cho TikTok Shop
 * Khắc phục lỗi "Session không hợp lệ"
 */

require_once 'vendor/autoload.php';

use App\Models\TikTokShopIntegration;
use App\Models\Team;
use Illuminate\Support\Str;

echo "=== TẠO AUTHORIZATION LINK MỚI CHO TIKTOK SHOP ===\n\n";

// 1. Kiểm tra các integration hiện tại
echo "1. Kiểm tra các TikTok Shop Integration hiện tại:\n";
$integrations = TikTokShopIntegration::all();

foreach ($integrations as $integration) {
    echo "   - Integration ID: {$integration->id}\n";
    echo "     Team ID: {$integration->team_id}\n";
    echo "     Status: {$integration->status}\n";
    echo "     Access Token: " . ($integration->access_token ? '✓ Có' : '✗ Không có') . "\n";
    echo "     Refresh Token: " . ($integration->refresh_token ? '✓ Có' : '✗ Không có') . "\n";

    if ($integration->access_token_expires_at) {
        $isExpired = $integration->isAccessTokenExpired();
        echo "     Token Expired: " . ($isExpired ? '✗ Hết hạn' : '✓ Còn hạn') . "\n";
    }

    if ($integration->error_message) {
        echo "     Error: {$integration->error_message}\n";
    }
    echo "\n";
}

// 2. Tạo integration mới cho team
echo "2. Tạo integration mới:\n";
echo "Nhập Team ID để tạo integration mới (hoặc Enter để bỏ qua): ";
$teamId = trim(fgets(STDIN));

if ($teamId && is_numeric($teamId)) {
    $team = Team::find($teamId);
    if (!$team) {
        echo "❌ Không tìm thấy team với ID: {$teamId}\n";
        exit(1);
    }

    echo "   - Tìm thấy team: {$team->name}\n";

    // Tạo integration mới
    $newIntegration = TikTokShopIntegration::create([
        'team_id' => $team->id,
        'status' => 'pending',
    ]);

    echo "   ✅ Đã tạo integration mới với ID: {$newIntegration->id}\n";

    // Tạo authorization URL
    $authUrl = $newIntegration->getAuthorizationUrl();
    echo "   ✅ Authorization URL: {$authUrl}\n\n";

    echo "3. Hướng dẫn sử dụng:\n";
    echo "   1. Truy cập URL trên: {$authUrl}\n";
    echo "   2. Đăng nhập TikTok Shop và đồng ý quyền\n";
    echo "   3. Hệ thống sẽ tự động xử lý callback\n";
    echo "   4. Kiểm tra trạng thái integration sau khi hoàn thành\n\n";
} else {
    echo "   ⏭️  Bỏ qua tạo integration mới\n\n";
}

// 3. Tạo authorization link cho integration có lỗi
echo "4. Tạo authorization link cho integration có lỗi:\n";
$errorIntegrations = TikTokShopIntegration::where('status', 'error')
    ->orWhere('status', 'pending')
    ->get();

foreach ($errorIntegrations as $integration) {
    echo "   - Integration ID {$integration->id} (Team {$integration->team_id}):\n";

    // Reset integration về trạng thái pending
    $integration->update([
        'status' => 'pending',
        'error_message' => null,
        'access_token' => null,
        'refresh_token' => null,
        'access_token_expires_at' => null,
        'refresh_token_expires_at' => null,
    ]);

    // Tạo authorization URL mới
    $authUrl = $integration->getAuthorizationUrl();
    echo "     ✅ Đã reset integration\n";
    echo "     ✅ Authorization URL: {$authUrl}\n";
    echo "     📋 Hướng dẫn: Truy cập URL trên để ủy quyền lại\n\n";
}

// 4. Tạo script test API
echo "5. Tạo script test API:\n";
$testScript = 'test_tiktok_auth.php';
$testContent = '<?php
require_once "vendor/autoload.php";

use App\Services\TikTokShopService;
use App\Models\TikTokShopIntegration;

echo "=== TEST TIKTOK SHOP API ===\n";

$service = new TikTokShopService();
$integrations = TikTokShopIntegration::where("status", "active")->get();

foreach ($integrations as $integration) {
    echo "Testing Integration ID: {$integration->id}\n";
    
    try {
        $result = $service->getAuthorizedShops($integration);
        if ($result["success"]) {
            echo "✅ API hoạt động bình thường\n";
            if (isset($result["data"]["shops"])) {
                echo "   - Số lượng shops: " . count($result["data"]["shops"]) . "\n";
            }
        } else {
            echo "❌ API lỗi: {$result["error"]}\n";
        }
    } catch (Exception $e) {
        echo "❌ Exception: {$e->getMessage()}\n";
    }
    echo "\n";
}
?>';

file_put_contents($testScript, $testContent);
echo "   ✅ Đã tạo script test: {$testScript}\n\n";

echo "=== HOÀN THÀNH ===\n";
echo "Để khắc phục lỗi authorization:\n";
echo "1. Sử dụng các authorization URLs được tạo ở trên\n";
echo "2. Hoàn thành quá trình ủy quyền trên TikTok Shop\n";
echo "3. Chạy script test: php {$testScript}\n";
echo "4. Kiểm tra trạng thái integration trong admin panel\n";
