<?php

namespace App\Console\Commands;

use App\Models\TikTokShopIntegration;
use App\Models\Team;
use App\Services\TikTokShopService;
use Illuminate\Console\Command;

class TestTikTokConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:test-connection 
                            {--create-new : Tạo integration mới để test}
                            {--team-id=7 : Team ID để tạo integration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test kết nối TikTok Shop và tạo authorization URLs';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Test kết nối TikTok Shop...');

        $createNew = $this->option('create-new');
        $teamId = $this->option('team-id');

        // 1. Kiểm tra integrations hiện tại
        $this->info('📊 Kiểm tra TikTok Shop Integrations:');
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

        // 2. Test API cho integrations active
        $this->info('🔍 Test API cho integrations active:');
        $service = new TikTokShopService();
        $activeIntegrations = TikTokShopIntegration::where('status', 'active')->get();

        if ($activeIntegrations->isEmpty()) {
            $this->warn('Không có integration nào đang active.');
        } else {
            foreach ($activeIntegrations as $integration) {
                $this->line("Testing Integration ID: {$integration->id}");

                try {
                    $result = $service->getAuthorizedShops($integration);
                    if ($result['success']) {
                        $shopCount = isset($result['data']['shops']) ? count($result['data']['shops']) : 0;
                        $this->info("   ✅ API hoạt động bình thường - Số shops: {$shopCount}");
                    } else {
                        $this->error("   ❌ API lỗi: {$result['error']}");
                    }
                } catch (\Exception $e) {
                    $this->error("   ❌ Exception: {$e->getMessage()}");
                }
            }
        }

        // 3. Tạo integration mới nếu được yêu cầu
        if ($createNew) {
            $this->info('🆕 Tạo integration mới để test:');

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

            // Tạo authorization URL
            $authUrl = $newIntegration->getAuthorizationUrl();
            $this->info("🔗 Authorization URL:");
            $this->line($authUrl);
            $this->newLine();

            // Tạo customer authorization URL
            $customerAuthUrl = $this->createCustomerAuthUrl($newIntegration);
            $this->info("👤 Customer Authorization URL:");
            $this->line($customerAuthUrl);
            $this->newLine();

            $this->info('📋 Hướng dẫn sử dụng:');
            $this->line('1. Sử dụng Authorization URL để kết nối trực tiếp');
            $this->line('2. Hoặc sử dụng Customer Authorization URL để khách hàng lấy code');
            $this->line('3. Kiểm tra log để xem chi tiết quá trình authorization');
        }

        $this->newLine();
        $this->info('🎉 Hoàn thành test!');

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
                'auth_token' => 'test_token_' . time(),
                'type' => 'customer_auth'
            ])),
            'redirect_uri' => route('team.tiktok-shop.customer-callback'),
            'scope' => 'seller.authorization.info,seller.shop.info,seller.product.basic,seller.order.info,seller.fulfillment.basic,seller.logistics,seller.delivery.status.write,seller.finance.info,seller.product.delete,seller.product.write,seller.product.optimize',
        ];

        return 'https://auth.tiktok-shops.com/oauth/authorize?' . http_build_query($params);
    }
}
