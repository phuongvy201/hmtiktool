<?php

namespace App\Console\Commands;

use App\Models\TikTokShopIntegration;
use App\Services\TikTokShopService;
use App\Services\TikTokTokenManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshTikTokTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiktok:refresh-tokens 
                            {--team-id= : Chỉ refresh token cho team cụ thể}
                            {--force : Bắt buộc refresh tất cả tokens}
                            {--dry-run : Chỉ hiển thị thông tin, không thực hiện refresh}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh TikTok Shop access tokens khi sắp hết hạn';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Bắt đầu refresh TikTok Shop tokens...');

        $teamId = $this->option('team-id');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('🔍 Chế độ dry-run: Chỉ hiển thị thông tin, không thực hiện refresh');
        }

        // Lấy danh sách integrations cần refresh
        $query = TikTokShopIntegration::whereIn('status', ['active', 'error'])
            ->whereNotNull('access_token')
            ->whereNotNull('refresh_token');

        if ($teamId) {
            $query->where('team_id', $teamId);
        }

        $integrations = $query->get();

        if ($integrations->isEmpty()) {
            $this->info('✅ Không có integration nào cần refresh token');
            return 0;
        }

        $this->info("📊 Tìm thấy {$integrations->count()} integration(s)");

        $refreshed = 0;
        $skipped = 0;
        $errors = 0;
        $details = [];

        foreach ($integrations as $integration) {
            $integrationId = $integration->id;
            $teamId = $integration->team_id;
            $hoursUntilExpiry = $integration->getHoursUntilExpiry();

            // Kiểm tra xem có cần refresh không
            if (!$force && !$integration->needsTokenRefresh()) {
                $skipped++;
                $details[] = [
                    'status' => 'skipped',
                    'integration_id' => $integrationId,
                    'team_id' => $teamId,
                    'reason' => "Token còn {$hoursUntilExpiry} giờ mới hết hạn"
                ];
                continue;
            }

            if ($dryRun) {
                $refreshed++;
                $details[] = [
                    'status' => 'would_refresh',
                    'integration_id' => $integrationId,
                    'team_id' => $teamId,
                    'hours_until_expiry' => $hoursUntilExpiry
                ];
                continue;
            }

            // Thực hiện refresh token
            try {
                $result = $integration->refreshAccessToken();

                if ($result['success']) {
                    $refreshed++;
                    $details[] = [
                        'status' => 'refreshed',
                        'integration_id' => $integrationId,
                        'team_id' => $teamId,
                        'new_expires_at' => $result['data']['formatted_access_expires'] ?? null
                    ];
                } else {
                    $errors++;
                    $details[] = [
                        'status' => 'error',
                        'integration_id' => $integrationId,
                        'team_id' => $teamId,
                        'error' => $result['message']
                    ];
                }
            } catch (\Exception $e) {
                $errors++;
                $details[] = [
                    'status' => 'error',
                    'integration_id' => $integrationId,
                    'team_id' => $teamId,
                    'error' => $e->getMessage()
                ];
            }
        }

        // Hiển thị kết quả chi tiết
        if (!empty($details)) {
            $this->newLine();
            $this->info('📋 Chi tiết kết quả:');

            foreach ($details as $detail) {
                $status = $detail['status'];
                $integrationId = $detail['integration_id'];
                $teamId = $detail['team_id'];

                switch ($status) {
                    case 'refreshed':
                        $newExpires = $detail['new_expires_at'] ?? 'N/A';
                        $this->info("   ✅ Integration {$integrationId} (Team {$teamId}): Refresh thành công - Hết hạn: {$newExpires}");
                        break;
                    case 'skipped':
                        $reason = $detail['reason'];
                        $this->line("   ⏭️  Integration {$integrationId} (Team {$teamId}): Bỏ qua - {$reason}");
                        break;
                    case 'would_refresh':
                        $hours = $detail['hours_until_expiry'];
                        $this->line("   🔄 Integration {$integrationId} (Team {$teamId}): Sẽ refresh (còn {$hours} giờ)");
                        break;
                    case 'error':
                        $error = $detail['error'];
                        $this->error("   ❌ Integration {$integrationId} (Team {$teamId}): Lỗi - {$error}");
                        break;
                }
            }
        }

        // Hiển thị kết quả tổng kết
        $this->newLine();
        $this->info('📈 Kết quả tổng kết:');
        $this->line("   ✅ Đã refresh: {$refreshed}");
        $this->line("   ⏭️  Đã bỏ qua: {$skipped}");
        $this->line("   ❌ Lỗi: {$errors}");

        if ($errors > 0) {
            $this->warn('⚠️  Có một số token không thể refresh. Vui lòng kiểm tra log để biết chi tiết.');
        }

        if ($refreshed > 0) {
            $this->info('🎉 Hoàn thành refresh tokens!');
        }

        return 0;
    }
}
