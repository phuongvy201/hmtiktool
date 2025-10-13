<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokSignatureService;
use App\Models\TikTokShopIntegration;
use App\Models\TikTokShop;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class LogTikTokRequestCommand extends Command
{
    protected $signature = 'test:log-tiktok-request {product_id} {shop_id}';
    protected $description = 'Log detailed TikTok request information including signature, query, and headers';

    public function handle()
    {
        $productId = $this->argument('product_id');
        $shopId = $this->argument('shop_id');

        $this->info("=== LOG TIKTOK REQUEST DETAILS ===");

        // Lấy dữ liệu
        $product = Product::find($productId);
        $shop = TikTokShop::find($shopId);
        $integration = TikTokShopIntegration::where('team_id', 7)->first();

        if (!$product || !$shop || !$integration) {
            $this->error("Không tìm thấy dữ liệu cần thiết");
            return;
        }

        // Tạo test data giống như product upload
        $timestamp = time();
        $appKey = $integration->getAppKey();
        $appSecret = $integration->getAppSecret();
        $shopCipher = $shop->getShopCipher();

        $this->info("App Key: {$appKey}");
        $this->info("App Secret Length: " . strlen($appSecret));
        $this->info("Shop Cipher: {$shopCipher}");
        $this->info("Timestamp: {$timestamp}");

        // Tạo body data giống như product upload
        $bodyData = [
            'save_mode' => 'LISTING',
            'title' => 'Test Product for Signature',
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
                'height' => '10',
                'width' => '10',
                'length' => '10',
                'unit' => 'CENTIMETER'
            ],
            'is_cod_allowed' => false,
            'idempotency_key' => 'test_' . $timestamp . '_' . uniqid()
        ];

        // Tạo signature step by step
        $this->info("\n=== SIGNATURE GENERATION STEP BY STEP ===");

        $apiPath = '/product/202309/products';
        $queryParams = [
            'app_key' => $appKey,
            'timestamp' => (string) $timestamp,
            'shop_cipher' => $shopCipher
        ];

        // Step 1: Filter và sort parameters
        $filteredParams = array_filter($queryParams, function ($key) {
            return !in_array($key, ['sign', 'access_token']);
        }, ARRAY_FILTER_USE_KEY);
        ksort($filteredParams);

        $this->info("1. Filtered Params: " . json_encode($filteredParams));

        // Step 2: Tạo param string
        $paramString = '';
        foreach ($filteredParams as $key => $value) {
            $paramString .= $key . $value;
        }
        $this->info("2. Param String: {$paramString}");

        // Step 3: Tạo input string
        $input = $apiPath . $paramString;
        $this->info("3. Input (path + params): {$input}");

        // Step 4: Thêm body
        $contentType = 'application/json';
        $bodyString = json_encode($bodyData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $input .= $bodyString;
        $this->info("4. Body String Length: " . strlen($bodyString));
        $this->info("4. Body String (first 200 chars): " . substr($bodyString, 0, 200) . "...");

        $this->info("5. Final Input Length: " . strlen($input));
        $this->info("5. Final Input (first 500 chars): " . substr($input, 0, 500) . "...");

        // Step 5: Wrap với app_secret
        $stringToSign = $appSecret . $input . $appSecret;
        $this->info("6. String to Sign Length: " . strlen($stringToSign));
        $this->info("6. String to Sign (first 100 chars): " . substr($stringToSign, 0, 100) . "...");
        $this->info("6. String to Sign (last 100 chars): ..." . substr($stringToSign, -100));

        // Step 6: HMAC-SHA256
        $signature = hash_hmac('sha256', $stringToSign, $appSecret, true);
        $hexSignature = bin2hex($signature);

        $this->info("7. Generated Signature: {$hexSignature}");

        // Tạo query params cuối cùng
        $finalQueryParams = [
            'app_key' => $appKey,
            'timestamp' => $timestamp,
            'shop_cipher' => $shopCipher,
            'sign' => $hexSignature
        ];

        $this->info("\n=== FINAL REQUEST DETAILS ===");

        // URL
        $url = 'https://open-api.tiktokglobalshop.com' . $apiPath;
        $this->info("URL: {$url}");

        // Query String
        $queryString = http_build_query($finalQueryParams);
        $this->info("Query String: {$queryString}");

        // Full URL
        $fullUrl = $url . '?' . $queryString;
        $this->info("Full URL: {$fullUrl}");

        // Headers
        $headers = [
            'Content-Type' => 'application/json',
            'x-tts-access-token' => $integration->access_token
        ];

        $this->info("Headers:");
        foreach ($headers as $key => $value) {
            if ($key === 'x-tts-access-token') {
                $this->info("  {$key}: " . substr($value, 0, 20) . "... (length: " . strlen($value) . ")");
            } else {
                $this->info("  {$key}: {$value}");
            }
        }

        // Body
        $this->info("Body Length: " . strlen($bodyString));
        $this->info("Body (first 500 chars): " . substr($bodyString, 0, 500) . "...");

        // Log chi tiết
        Log::info('TikTok Request Details', [
            'url' => $url,
            'query_params' => $finalQueryParams,
            'headers' => [
                'Content-Type' => $headers['Content-Type'],
                'x-tts-access-token' => substr($headers['x-tts-access-token'], 0, 20) . '...'
            ],
            'body_length' => strlen($bodyString),
            'signature_generation' => [
                'api_path' => $apiPath,
                'filtered_params' => $filteredParams,
                'param_string' => $paramString,
                'input_without_secret' => $input,
                'string_to_sign_length' => strlen($stringToSign),
                'generated_signature' => $hexSignature
            ]
        ]);

        $this->info("\n=== TESTING ACTUAL REQUEST ===");

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($fullUrl, $bodyData);

            $this->info("Response Status: " . $response->status());
            $this->info("Response Body: " . $response->body());

            Log::info('TikTok API Response', [
                'status' => $response->status(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);
        } catch (\Exception $e) {
            $this->error("Request failed: " . $e->getMessage());
            Log::error('TikTok API Request Failed', [
                'error' => $e->getMessage(),
                'url' => $fullUrl,
                'headers' => $headers
            ]);
        }

        $this->info("\n=== COMPLETED ===");
        $this->info("Check storage/logs/laravel.log for detailed logs");
    }
}
