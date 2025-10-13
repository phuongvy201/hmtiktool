<?php

namespace App\Console\Commands;

use App\Models\TikTokShopIntegration;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreateTikTokAuthorization extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:create-auth 
                            {--team-id= : Team ID để tạo integration mới}
                            {--reset-errors : Reset các integration có lỗi về pending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo authorization link mới cho TikTok Shop để khắc phục lỗi session';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔗 Tạo authorization link mới cho TikTok Shop...');

        $teamId = $this->option('team-id');
        $resetErrors = $this->option('reset-errors');

        // 1. Hiển thị các integration hiện tại
        $this->info('📊 Kiểm tra các TikTok Shop Integration hiện tại:');
        $integrations = TikTokShopIntegration::all();

        if ($integrations->isEmpty()) {
            $this->warn('Không có integration nào được tìm thấy.');
        } else {
            $table = [];
            foreach ($integrations as $integration) {
                $table[] = [
                    'ID' => $integration->id,
                    'Team ID' => $integration->team_id,
                    'Status' => $integration->status,
                    'Access Token' => $integration->access_token ? '✓' : '✗',
                    'Refresh Token' => $integration->refresh_token ? '✓' : '✗',
                    'Token Expired' => $integration->access_token ? ($integration->isAccessTokenExpired() ? '✗' : '✓') : 'N/A',
                    'Error' => $integration->error_message ? 'Có' : 'Không'
                ];
            }
            $this->table(['ID', 'Team ID', 'Status', 'Access Token', 'Refresh Token', 'Token Expired', 'Error'], $table);
        }

        // 2. Tạo integration mới nếu có team-id
        if ($teamId) {
            $team = Team::find($teamId);
            if (!$team) {
                $this->error("❌ Không tìm thấy team với ID: {$teamId}");
                return 1;
            }

            $this->info("✅ Tìm thấy team: {$team->name}");

            // Tạo integration mới
            $newIntegration = TikTokShopIntegration::create([
                'team_id' => $team->id,
                'status' => 'pending',
            ]);

            $this->info("✅ Đã tạo integration mới với ID: {$newIntegration->id}");

            // Tạo authorization URL
            $authUrl = $newIntegration->getAuthorizationUrl();
            $this->info("🔗 Authorization URL: {$authUrl}");

            $this->newLine();
            $this->info('📋 Hướng dẫn sử dụng:');
            $this->line('1. Truy cập URL trên: ' . $authUrl);
            $this->line('2. Đăng nhập TikTok Shop và đồng ý quyền');
            $this->line('3. Hệ thống sẽ tự động xử lý callback');
            $this->line('4. Kiểm tra trạng thái integration sau khi hoàn thành');
        }

        // 3. Reset các integration có lỗi
        if ($resetErrors) {
            $this->info('🔄 Reset các integration có lỗi...');

            $errorIntegrations = TikTokShopIntegration::where('status', 'error')
                ->orWhere('status', 'pending')
                ->get();

            foreach ($errorIntegrations as $integration) {
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

                $this->info("✅ Integration {$integration->id} (Team {$integration->team_id}):");
                $this->line("   - Đã reset integration");
                $this->line("   - Authorization URL: {$authUrl}");
                $this->line("   - Hướng dẫn: Truy cập URL trên để ủy quyền lại");
                $this->newLine();
            }
        }

        // 4. Tạo script test API
        $this->info('🧪 Tạo script test API...');
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
        $this->info("✅ Đã tạo script test: {$testScript}");

        $this->newLine();
        $this->info('🎉 Hoàn thành!');
        $this->line('Để khắc phục lỗi authorization:');
        $this->line('1. Sử dụng các authorization URLs được tạo ở trên');
        $this->line('2. Hoàn thành quá trình ủy quyền trên TikTok Shop');
        $this->line('3. Chạy script test: php ' . $testScript);
        $this->line('4. Kiểm tra trạng thái integration trong admin panel');

        return 0;
    }
}
