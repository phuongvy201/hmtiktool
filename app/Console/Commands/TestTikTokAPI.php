<?php

namespace App\Console\Commands;

use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestTikTokAPI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:test-api 
                            {--integration-id= : Test integration cụ thể}
                            {--all : Test tất cả integration}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test TikTok Shop API để kiểm tra lỗi session';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Test TikTok Shop API...');

        $integrationId = $this->option('integration-id');
        $testAll = $this->option('all');

        $service = new TikTokShopService();

        if ($integrationId) {
            $integrations = TikTokShopIntegration::where('id', $integrationId)->get();
        } elseif ($testAll) {
            $integrations = TikTokShopIntegration::where('status', 'active')->get();
        } else {
            $integrations = TikTokShopIntegration::where('status', 'active')->get();
        }

        if ($integrations->isEmpty()) {
            $this->warn('Không có integration nào để test.');
            return 0;
        }

        $this->info("📊 Tìm thấy {$integrations->count()} integration(s) để test");

        $successCount = 0;
        $errorCount = 0;
        $details = [];

        foreach ($integrations as $integration) {
            $this->line("Testing Integration ID: {$integration->id} (Team {$integration->team_id})");

            try {
                $result = $service->getAuthorizedShops($integration);

                if ($result['success']) {
                    $successCount++;
                    $shopCount = isset($result['data']['shops']) ? count($result['data']['shops']) : 0;
                    $this->info("   ✅ API hoạt động bình thường - Số shops: {$shopCount}");

                    $details[] = [
                        'integration_id' => $integration->id,
                        'team_id' => $integration->team_id,
                        'status' => 'success',
                        'shops_count' => $shopCount
                    ];
                } else {
                    $errorCount++;
                    $this->error("   ❌ API lỗi: {$result['error']}");

                    $details[] = [
                        'integration_id' => $integration->id,
                        'team_id' => $integration->team_id,
                        'status' => 'error',
                        'error' => $result['error']
                    ];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $this->error("   ❌ Exception: {$e->getMessage()}");

                $details[] = [
                    'integration_id' => $integration->id,
                    'team_id' => $integration->team_id,
                    'status' => 'exception',
                    'error' => $e->getMessage()
                ];
            }

            $this->newLine();
        }

        // Hiển thị kết quả tổng kết
        $this->info('📈 Kết quả tổng kết:');
        $this->line("   ✅ Thành công: {$successCount}");
        $this->line("   ❌ Lỗi: {$errorCount}");

        if ($errorCount > 0) {
            $this->warn('⚠️  Có một số integration gặp lỗi. Vui lòng kiểm tra:');
            foreach ($details as $detail) {
                if ($detail['status'] !== 'success') {
                    $this->line("   - Integration {$detail['integration_id']}: {$detail['error']}");
                }
            }
            $this->newLine();
            $this->info('💡 Để khắc phục lỗi session:');
            $this->line('1. Chạy: php artisan tiktok:create-auth --reset-errors');
            $this->line('2. Sử dụng authorization URLs được tạo');
            $this->line('3. Hoàn thành quá trình ủy quyền trên TikTok Shop');
        } else {
            $this->info('🎉 Tất cả API đều hoạt động bình thường!');
        }

        return 0;
    }
}
