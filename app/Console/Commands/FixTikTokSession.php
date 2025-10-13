<?php

namespace App\Console\Commands;

use App\Models\TikTokShopIntegration;
use App\Models\Team;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FixTikTokSession extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:fix-session 
                            {--team-id=7 : Team ID để tạo integration mới}
                            {--reset-all : Reset tất cả integrations về pending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Khắc phục lỗi session TikTok Shop và tạo authorization link mới';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Khắc phục lỗi session TikTok Shop...');

        $teamId = $this->option('team-id');
        $resetAll = $this->option('reset-all');

        // 1. Hiển thị trạng thái hiện tại
        $this->info('📊 Trạng thái TikTok Shop Integrations:');
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

        // 2. Reset integrations nếu được yêu cầu
        if ($resetAll) {
            $this->info('🔄 Reset tất cả integrations về pending...');

            $resetCount = 0;
            foreach ($integrations as $integration) {
                if ($integration->status !== 'pending') {
                    $integration->update([
                        'status' => 'pending',
                        'error_message' => null,
                        'access_token' => null,
                        'refresh_token' => null,
                        'access_token_expires_at' => null,
                        'refresh_token_expires_at' => null,
                        'additional_data' => null,
                    ]);
                    $resetCount++;
                }
            }

            $this->info("✅ Đã reset {$resetCount} integrations về pending");
        }

        // 3. Tạo integration mới
        $this->info('🆕 Tạo integration mới...');

        $team = Team::find($teamId);
        if (!$team) {
            $this->error("❌ Không tìm thấy team với ID: {$teamId}");
            return 1;
        }

        $newIntegration = TikTokShopIntegration::create([
            'team_id' => $team->id,
            'status' => 'pending',
        ]);

        $this->info("✅ Đã tạo integration mới với ID: {$newIntegration->id}");

        // 4. Tạo authorization URLs
        $this->info('🔗 Tạo authorization URLs...');

        // Authorization URL thông thường
        $authUrl = $newIntegration->getAuthorizationUrl();
        $this->info("📱 Authorization URL (cho kết nối trực tiếp):");
        $this->line($authUrl);
        $this->newLine();

        // Customer Authorization URL
        $customerAuthUrl = $this->createCustomerAuthUrl($newIntegration);
        $this->info("👤 Customer Authorization URL (cho khách hàng):");
        $this->line($customerAuthUrl);
        $this->newLine();

        // 5. Tạo script test
        $this->info('🧪 Tạo script test...');
        $testScript = 'test_auth_fix.php';
        $testContent = "<?php
echo '=== TEST AUTHORIZATION FIX ===\n';
echo 'Integration ID: {$newIntegration->id}\n';
echo 'Team ID: {$newIntegration->team_id}\n';
echo 'Status: {$newIntegration->status}\n';
echo 'Authorization URL: {$authUrl}\n';
echo 'Customer URL: {$customerAuthUrl}\n';
echo '\n';
echo 'Hướng dẫn sử dụng:\n';
echo '1. Sử dụng Authorization URL để kết nối trực tiếp\n';
echo '2. Hoặc sử dụng Customer Authorization URL để khách hàng lấy code\n';
echo '3. Kiểm tra log để xem chi tiết quá trình authorization\n';
?>";

        file_put_contents($testScript, $testContent);
        $this->info("✅ Đã tạo script test: {$testScript}");

        // 6. Hướng dẫn sử dụng
        $this->newLine();
        $this->info('📋 Hướng dẫn sử dụng:');
        $this->line('1. Sử dụng Authorization URL để kết nối trực tiếp từ trang admin');
        $this->line('2. Hoặc sử dụng Customer Authorization URL để khách hàng lấy authorization code');
        $this->line('3. Kiểm tra log để xem chi tiết quá trình authorization');
        $this->line('4. Nếu vẫn lỗi, chạy: php artisan tiktok:fix-session --reset-all');

        $this->newLine();
        $this->info('🎉 Hoàn thành khắc phục lỗi session!');

        return 0;
    }

    /**
     * Create customer authorization URL
     */
    private function createCustomerAuthUrl(TikTokShopIntegration $integration): string
    {
        $params = [
            'app_key' => $integration->getAppKey(),
            'state' => base64_encode(json_encode([
                'team_id' => $integration->team_id,
                'auth_token' => 'fix_token_' . time(),
                'type' => 'customer_auth'
            ])),
            'redirect_uri' => route('public.customer-callback'),
            'scope' => 'seller.authorization.info,seller.shop.info,seller.product.basic,seller.order.info,seller.fulfillment.basic,seller.logistics,seller.delivery.status.write,seller.finance.info,seller.product.delete,seller.product.write,seller.product.optimize',
        ];

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query($params);
    }
}
