<?php

namespace App\Console\Commands;

use App\Models\TikTokShopIntegration;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateTikTokAuthLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:create-auth-link 
                            {--team-id= : Team ID để tạo authorization link}
                            {--integration-id= : Integration ID cụ thể}
                            {--show-url : Chỉ hiển thị URL, không tạo integration mới}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo authorization link mới cho TikTok Shop với session đúng cách';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔗 Tạo authorization link mới cho TikTok Shop...');

        $teamId = $this->option('team-id');
        $integrationId = $this->option('integration-id');
        $showUrlOnly = $this->option('show-url');

        // 1. Tìm integration
        if ($integrationId) {
            $integration = TikTokShopIntegration::find($integrationId);
            if (!$integration) {
                $this->error("❌ Không tìm thấy integration với ID: {$integrationId}");
                return 1;
            }
            $teamId = $integration->team_id;
        } elseif ($teamId) {
            $team = Team::find($teamId);
            if (!$team) {
                $this->error("❌ Không tìm thấy team với ID: {$teamId}");
                return 1;
            }

            if ($showUrlOnly) {
                // Tạo integration tạm thời để lấy URL
                $integration = new TikTokShopIntegration();
                $integration->team_id = $teamId;
            } else {
                // Tạo integration mới
                $integration = TikTokShopIntegration::create([
                    'team_id' => $teamId,
                    'status' => 'pending',
                ]);
                $this->info("✅ Đã tạo integration mới với ID: {$integration->id}");
            }
        } else {
            $this->error("❌ Vui lòng cung cấp --team-id hoặc --integration-id");
            return 1;
        }

        // 2. Tạo authorization URL
        $authUrl = $integration->getAuthorizationUrl();

        $this->info("🔗 Authorization URL:");
        $this->line($authUrl);
        $this->newLine();

        // 3. Tạo session token cho customer callback
        if (!$showUrlOnly) {
            $authToken = Str::random(32);

            // Lưu session token vào database hoặc cache
            $integration->update([
                'additional_data' => array_merge($integration->additional_data ?? [], [
                    'auth_token' => $authToken,
                    'auth_token_expires' => now()->addHours(1)->timestamp
                ])
            ]);

            $this->info("🔐 Session Token: {$authToken}");
            $this->info("⏰ Token hết hạn: " . now()->addHours(1)->format('Y-m-d H:i:s'));
            $this->newLine();
        }

        // 4. Tạo customer authorization URL
        $customerAuthUrl = $this->createCustomerAuthUrl($integration, $authToken ?? 'temp_token');

        $this->info("👤 Customer Authorization URL:");
        $this->line($customerAuthUrl);
        $this->newLine();

        // 5. Hướng dẫn sử dụng
        $this->info('📋 Hướng dẫn sử dụng:');
        $this->line('1. Sử dụng Customer Authorization URL ở trên');
        $this->line('2. Khách hàng truy cập URL và đăng nhập TikTok Shop');
        $this->line('3. Khách hàng sẽ nhận được authorization code');
        $this->line('4. Khách hàng gửi code cho team admin');
        $this->line('5. Team admin sử dụng code để hoàn tất kết nối');
        $this->newLine();

        // 6. Tạo script test
        $this->info('🧪 Tạo script test...');
        $testScript = 'test_auth_link.php';
        $testContent = "<?php
echo '=== TEST AUTHORIZATION LINK ===\n';
echo 'Integration ID: {$integration->id}\n';
echo 'Team ID: {$integration->team_id}\n';
echo 'Authorization URL: {$authUrl}\n';
echo 'Customer URL: {$customerAuthUrl}\n';
echo 'Session Token: " . ($authToken ?? 'N/A') . "\n';
echo 'Expires: " . (now()->addHours(1)->format('Y-m-d H:i:s') ?? 'N/A') . "\n';
?>";

        file_put_contents($testScript, $testContent);
        $this->info("✅ Đã tạo script test: {$testScript}");

        $this->newLine();
        $this->info('🎉 Hoàn thành!');
        $this->line('Sử dụng Customer Authorization URL để khách hàng có thể lấy authorization code.');

        return 0;
    }

    /**
     * Create customer authorization URL
     */
    private function createCustomerAuthUrl(TikTokShopIntegration $integration, string $authToken): string
    {
        $params = [
            'app_key' => $integration->getAppKey(),
            'state' => base64_encode(json_encode([
                'team_id' => $integration->team_id,
                'auth_token' => $authToken,
                'type' => 'customer_auth'
            ])),
            'redirect_uri' => route('public.customer-callback'),
            'scope' => 'seller.authorization.info,seller.shop.info,seller.product.basic,seller.order.info,seller.fulfillment.basic,seller.logistics,seller.delivery.status.write,seller.finance.info,seller.product.delete,seller.product.write,seller.product.optimize',
        ];

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query($params);
    }
}
