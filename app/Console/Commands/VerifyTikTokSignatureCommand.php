<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokSignatureService;
use App\Models\TikTokShopIntegration;
use App\Models\TikTokShop;
use Illuminate\Support\Facades\Log;

class VerifyTikTokSignatureCommand extends Command
{
    protected $signature = 'test:verify-tiktok-signature {shop_id}';
    protected $description = 'Verify TikTok signature generation step by step';

    public function handle()
    {
        $shopId = $this->argument('shop_id');

        // 1. Xác minh app_key / app_secret
        $this->info("=== BƯỚC 1: XÁC MINH APP_KEY / APP_SECRET ===");
        $integration = TikTokShopIntegration::where('team_id', 7)->first();
        if (!$integration) {
            $this->error("Không tìm thấy integration");
            return;
        }

        $shop = TikTokShop::find($shopId);
        if (!$shop) {
            $this->error("Không tìm thấy shop ID: {$shopId}");
            return;
        }

        $appKey = $integration->getAppKey();
        $appSecret = $integration->getAppSecret();

        $this->info("App Key: {$appKey}");
        $this->info("App Secret Length: " . strlen($appSecret) . " characters");
        $this->info("App Secret (first 10 chars): " . substr($appSecret, 0, 10) . "...");
        $this->info("Shop Cipher: {$shop->cipher}");

        // 2. Tạo test data
        $this->info("\n=== BƯỚC 2: TẠO TEST DATA ===");
        $timestamp = time();
        $this->info("Timestamp: {$timestamp}");
        $this->info("Current Time: " . date('Y-m-d H:i:s', $timestamp));

        // Kiểm tra timestamp (10 chữ số, ±5 phút)
        $currentTime = time();
        $timeDiff = abs($timestamp - $currentTime);
        if ($timeDiff > 300) { // 5 phút = 300 giây
            $this->warn("⚠️  Timestamp lệch quá 5 phút! Diff: {$timeDiff} seconds");
        } else {
            $this->info("✅ Timestamp hợp lệ (diff: {$timeDiff} seconds)");
        }

        // 3. Tạo signature với logging chi tiết
        $this->info("\n=== BƯỚC 3: TẠO SIGNATURE VỚI LOGGING CHI TIẾT ===");

        $apiPath = '/product/202309/products';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => (string) $timestamp,
            'shop_cipher' => $shop->cipher
        ];

        $bodyParams = [
            'save_mode' => 'LISTING',
            'title' => 'Test Product',
            'description' => 'Test Description',
            'category_id' => '601226',
            'main_images' => [
                ['uri' => 'test-uri-123']
            ],
            'skus' => [
                [
                    'sales_attributes' => [
                        ['value_name' => 'Red', 'name' => 'Color']
                    ],
                    'inventory' => [
                        ['warehouse_id' => '123456', 'quantity' => 100]
                    ],
                    'price' => [
                        'currency' => 'GBP',
                        'amount' => '10.00'
                    ],
                    'seller_sku' => 'TEST-SKU-001'
                ]
            ],
            'package_weight' => [
                'value' => '1.000',
                'unit' => 'KILOGRAM'
            ],
            'package_dimensions' => [
                'height' => '10.00',
                'width' => '10.00',
                'length' => '1.00',
                'unit' => 'CENTIMETER'
            ],
            'is_cod_allowed' => false,
            'idempotency_key' => 'test_' . $timestamp . '_' . uniqid()
        ];

        // 4. Tạo signature step by step
        $this->info("\n=== BƯỚC 4: TẠO SIGNATURE STEP BY STEP ===");

        // Step 1: Filter và sort parameters
        $filteredParams = array_filter($queryParams, function ($key) {
            return !in_array($key, ['sign', 'access_token']);
        }, ARRAY_FILTER_USE_KEY);
        ksort($filteredParams);

        $this->info("Filtered Params: " . json_encode($filteredParams));

        // Step 2: Tạo param string
        $paramString = '';
        foreach ($filteredParams as $key => $value) {
            $paramString .= $key . $value;
        }
        $this->info("Param String: {$paramString}");

        // Step 3: Tạo input string
        $input = $apiPath . $paramString;
        $this->info("Input (path + params): {$input}");

        // Step 4: Thêm body
        $contentType = 'application/json';
        if ($contentType !== 'multipart/form-data' && !empty($bodyParams)) {
            $bodyString = json_encode($bodyParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $input .= $bodyString;
            $this->info("Body String Length: " . strlen($bodyString));
            $this->info("Body String (first 200 chars): " . substr($bodyString, 0, 200) . "...");
        }

        $this->info("Final Input Length: " . strlen($input));
        $this->info("Final Input (first 500 chars): " . substr($input, 0, 500) . "...");

        // Step 5: Wrap với app_secret
        $stringToSign = $appSecret . $input . $appSecret;
        $this->info("String to Sign Length: " . strlen($stringToSign));
        $this->info("String to Sign (first 100 chars): " . substr($stringToSign, 0, 100) . "...");
        $this->info("String to Sign (last 100 chars): ..." . substr($stringToSign, -100));

        // Step 6: HMAC-SHA256
        $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
        $hexSignature = bin2hex($signature);

        $this->info("Generated Signature: {$hexSignature}");

        // 5. So sánh với TikTokSignatureService
        $this->info("\n=== BƯỚC 5: SO SÁNH VỚI TIKTOK SIGNATURE SERVICE ===");
        $serviceSignature = TikTokSignatureService::generateProductUploadSignature(
            $appKey,
            $appSecret,
            (string) $timestamp,
            $bodyParams,
            $shop->cipher
        );

        $this->info("Service Signature: {$serviceSignature}");

        if ($hexSignature === $serviceSignature) {
            $this->info("✅ Signatures match!");
        } else {
            $this->error("❌ Signatures do NOT match!");
        }

        // 6. Kiểm tra URL cuối cùng
        $this->info("\n=== BƯỚC 6: KIỂM TRA URL CUỐI CÙNG ===");
        $finalUrl = "https://open-api.tiktokglobalshop.com{$apiPath}";
        $queryString = http_build_query([
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $shop->cipher,
            'sign' => $hexSignature
        ]);
        $finalUrl .= '?' . $queryString;

        $this->info("Final URL: {$finalUrl}");

        // 7. Log chi tiết để debug
        $this->info("\n=== BƯỚC 7: LOG CHI TIẾT ===");
        Log::info('TikTok Signature Verification', [
            'shop_id' => $shopId,
            'app_key' => $appKey,
            'app_secret_length' => strlen($appSecret),
            'shop_cipher' => $shop->cipher,
            'timestamp' => $timestamp,
            'api_path' => $apiPath,
            'query_params' => $queryParams,
            'filtered_params' => $filteredParams,
            'param_string' => $paramString,
            'input_without_secret' => $input,
            'string_to_sign_length' => strlen($stringToSign),
            'generated_signature' => $hexSignature,
            'service_signature' => $serviceSignature,
            'signatures_match' => $hexSignature === $serviceSignature,
            'final_url' => $finalUrl
        ]);

        $this->info("\n=== HOÀN THÀNH ===");
        $this->info("Kiểm tra log file để xem chi tiết: storage/logs/laravel.log");
    }
}
