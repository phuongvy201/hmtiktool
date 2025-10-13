<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TikTokShop;
use App\Services\TikTokShopPerformanceService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class TestTikTokRealAPI extends Command
{
    protected $signature = 'tiktok:test-real-api';
    protected $description = 'Test TikTok Partner API với dữ liệu thực tế';

    public function handle()
    {
        $this->info('🧪 Testing TikTok Partner API với dữ liệu thực tế...');

        // Test 1: Kiểm tra TikTok shops và integrations
        $this->info('📊 Test 1: Kiểm tra TikTok shops và integrations...');
        $shops = TikTokShop::with('integration')->where('status', 'active')->get();

        if ($shops->count() === 0) {
            $this->error('❌ Không có TikTok shops active');
            return 1;
        }

        $this->info("✅ Found {$shops->count()} active TikTok shops:");
        foreach ($shops as $shop) {
            $this->line("  - {$shop->shop_name} (ID: {$shop->id})");
            if ($shop->integration) {
                $this->line("    Integration: {$shop->integration->status}");
                $this->line("    Access Token: " . (empty($shop->integration->access_token) ? 'MISSING' : 'EXISTS'));
                $this->line("    Token Expires: " . ($shop->integration->access_token_expires_at ?? 'N/A'));
            } else {
                $this->line("    Integration: MISSING");
            }
        }

        // Test 2: Test API call trực tiếp
        $this->info('📊 Test 2: Test TikTok Partner API trực tiếp...');
        $shop = $shops->first();

        if (!$shop->integration || empty($shop->integration->access_token)) {
            $this->error('❌ Shop không có access token');
            return 1;
        }

        // Test API call trực tiếp
        $this->testDirectAPICall($shop);

        // Test 3: Test với Performance Service
        $this->info('📊 Test 3: Test với TikTokShopPerformanceService...');
        $this->testPerformanceService($shop);

        // Test 4: Kiểm tra logs
        $this->info('📊 Test 4: Kiểm tra logs...');
        $this->checkLogs();

        return 0;
    }

    private function testDirectAPICall(TikTokShop $shop)
    {
        $this->line("Testing direct API call for shop: {$shop->shop_name}");

        try {
            $accessToken = $shop->integration->access_token;
            $appKey = $shop->integration->getAppKey();
            $appSecret = $shop->integration->getAppSecret();

            $this->line("  App Key: {$appKey}");
            $this->line("  Access Token: " . substr($accessToken, 0, 20) . "...");

            // Chuẩn bị parameters
            $params = [
                'app_key' => $appKey,
                'timestamp' => time(),
                'shop_id' => $shop->shop_id,
                'start_date' => date('Y-m-d', strtotime('-7 days')),
                'end_date' => date('Y-m-d'),
                'granularity' => '1D',
                'currency' => 'USD'
            ];

            // Tạo signature
            $signature = $this->generateSignature($params, $appSecret);
            $params['sign'] = $signature;

            $this->line("  API Parameters: " . json_encode($params, JSON_PRETTY_PRINT));

            // Gọi API
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'x-tts-access-token' => $accessToken
            ])->timeout(30)->get('https://open-api.tiktokglobalshop.com/202405/analytics/shop/performance', $params);

            $httpCode = $response->status();
            $responseData = $response->json();

            $this->line("  HTTP Code: {$httpCode}");
            $this->line("  Response: " . json_encode($responseData, JSON_PRETTY_PRINT));

            if ($httpCode === 200 && isset($responseData['code']) && $responseData['code'] === 0) {
                $this->info("✅ API call thành công!");
                if (isset($responseData['data'])) {
                    $this->line("  Data keys: " . implode(', ', array_keys($responseData['data'])));
                }
            } else {
                $this->error("❌ API call failed!");
                $this->line("  Error Code: " . ($responseData['code'] ?? 'Unknown'));
                $this->line("  Error Message: " . ($responseData['message'] ?? 'Unknown'));
            }
        } catch (\Exception $e) {
            $this->error("❌ Exception: " . $e->getMessage());
        }
    }

    private function testPerformanceService(TikTokShop $shop)
    {
        try {
            $service = new TikTokShopPerformanceService();

            $filters = [
                'start_date' => date('Y-m-d', strtotime('-7 days')),
                'end_date' => date('Y-m-d'),
                'granularity' => '1D',
                'with_comparison' => true,
                'currency' => 'USD'
            ];

            $this->line("Testing Performance Service với filters: " . json_encode($filters));

            $result = $service->getShopPerformance($shop, $filters);

            $this->line("Service Result: " . json_encode($result, JSON_PRETTY_PRINT));

            if (isset($result['success']) && $result['success']) {
                $this->info("✅ Performance Service thành công!");
                if (isset($result['data']['summary'])) {
                    $summary = $result['data']['summary'];
                    $this->line("  Total GMV: $" . number_format($summary['total_gmv'], 2));
                    $this->line("  Total Orders: " . $summary['total_orders']);
                }
            } else {
                $this->error("❌ Performance Service failed!");
                $this->line("  Message: " . ($result['message'] ?? 'Unknown'));
            }
        } catch (\Exception $e) {
            $this->error("❌ Performance Service Exception: " . $e->getMessage());
        }
    }

    private function checkLogs()
    {
        $this->line("Checking recent logs...");

        // Đọc log file mới nhất
        $logFile = storage_path('logs/laravel-' . date('Y-m-d') . '.log');

        if (file_exists($logFile)) {
            $this->line("  Log file: {$logFile}");

            // Đọc 50 dòng cuối
            $lines = file($logFile);
            $recentLines = array_slice($lines, -50);

            $this->line("  Recent log entries:");
            foreach ($recentLines as $line) {
                if (strpos($line, 'TikTok') !== false || strpos($line, 'Performance') !== false) {
                    $this->line("    " . trim($line));
                }
            }
        } else {
            $this->line("  No log file found for today");
        }
    }

    private function generateSignature(array $params, string $appSecret): string
    {
        // Sắp xếp parameters theo alphabetical order
        ksort($params);

        // Tạo query string
        $queryString = http_build_query($params);

        // Thêm app_secret
        $queryString .= "&app_secret={$appSecret}";

        // Tạo signature
        return strtoupper(hash('sha256', $queryString));
    }
}
