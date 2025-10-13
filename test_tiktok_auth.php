<?php
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
?>