<?php

require_once 'vendor/autoload.php';

use App\Models\TikTokShop;
use App\Models\TikTokShopIntegration;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== KIỂM TRA VÀ SỬA LỖI DUPLICATE SHOP ===\n\n";

try {
    $shopId = '7494088027748009056';

    echo "🔍 Kiểm tra shop với ID: {$shopId}\n";

    // Kiểm tra shop đã tồn tại chưa
    $existingShop = TikTokShop::where('shop_id', $shopId)->first();

    if ($existingShop) {
        echo "✅ Shop đã tồn tại:\n";
        echo "   ID: {$existingShop->id}\n";
        echo "   Shop Name: {$existingShop->shop_name}\n";
        echo "   Shop ID: {$existingShop->shop_id}\n";
        echo "   Cipher: {$existingShop->cipher}\n";
        echo "   Status: {$existingShop->status}\n";
        echo "   Team ID: {$existingShop->team_id}\n";
        echo "   Integration ID: {$existingShop->tiktok_shop_integration_id}\n";
        echo "   Created: {$existingShop->created_at}\n";
        echo "   Updated: {$existingShop->updated_at}\n\n";

        // Kiểm tra integration
        if ($existingShop->integration) {
            echo "🔗 Integration:\n";
            echo "   Status: {$existingShop->integration->status}\n";
            echo "   Team ID: {$existingShop->integration->team_id}\n";
            echo "   Access Token: " . substr($existingShop->integration->access_token, 0, 30) . "...\n";
            echo "   Token Expires: " . date('Y-m-d H:i:s', $existingShop->integration->access_token_expires_at) . "\n\n";
        }

        echo "💡 Giải pháp:\n";
        echo "   - Thay vì tạo shop mới, hãy UPDATE shop hiện có\n";
        echo "   - Hoặc sử dụng shop hiện có với ID: {$existingShop->id}\n\n";
    } else {
        echo "❌ Shop không tồn tại trong database\n";
        echo "   Có thể đã bị xóa hoặc chưa được tạo\n\n";
    }

    // Kiểm tra tất cả shops có shop_id tương tự
    echo "🔍 Kiểm tra tất cả shops có shop_id bắt đầu với 7494088027748009056:\n";
    $similarShops = TikTokShop::where('shop_id', 'like', '7494088027748009056%')->get();

    if ($similarShops->count() > 0) {
        foreach ($similarShops as $shop) {
            echo "   - Shop ID: {$shop->shop_id}, Name: {$shop->shop_name}, DB ID: {$shop->id}\n";
        }
    } else {
        echo "   Không tìm thấy shop nào tương tự\n";
    }

    echo "\n";

    // Kiểm tra integration mới
    $newIntegrationId = 13;
    echo "🔍 Kiểm tra integration mới (ID: {$newIntegrationId}):\n";
    $newIntegration = TikTokShopIntegration::find($newIntegrationId);

    if ($newIntegration) {
        echo "✅ Integration mới tồn tại:\n";
        echo "   ID: {$newIntegration->id}\n";
        echo "   Team ID: {$newIntegration->team_id}\n";
        echo "   Status: {$newIntegration->status}\n";
        echo "   Access Token: " . substr($newIntegration->access_token, 0, 30) . "...\n\n";

        // Đề xuất giải pháp
        echo "💡 Đề xuất giải pháp:\n";
        if ($existingShop) {
            echo "   1. UPDATE shop hiện có để liên kết với integration mới:\n";
            echo "      \$shop = TikTokShop::find({$existingShop->id});\n";
            echo "      \$shop->tiktok_shop_integration_id = {$newIntegrationId};\n";
            echo "      \$shop->save();\n\n";

            echo "   2. Hoặc xóa shop cũ và tạo mới (nếu cần):\n";
            echo "      \$existingShop->delete();\n";
            echo "      // Sau đó tạo shop mới\n\n";
        }
    } else {
        echo "❌ Integration mới không tồn tại\n";
    }

    // Hiển thị tất cả integrations
    echo "🔍 Tất cả integrations:\n";
    $integrations = TikTokShopIntegration::all();
    foreach ($integrations as $integration) {
        echo "   - ID: {$integration->id}, Team: {$integration->team_id}, Status: {$integration->status}\n";
    }
} catch (Exception $e) {
    echo "❌ Lỗi: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== KIỂM TRA HOÀN THÀNH ===\n";
