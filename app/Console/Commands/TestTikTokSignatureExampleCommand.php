<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokSignatureService;
use App\Models\TikTokShopIntegration;

class TestTikTokSignatureExampleCommand extends Command
{
    protected $signature = 'test:tiktok-signature-example';
    protected $description = 'Test TikTok signature with the exact example from documentation';

    public function handle()
    {
        $this->info("=== TESTING TIKTOK SIGNATURE EXAMPLE ===");

        // Lấy integration
        $integration = TikTokShopIntegration::where('team_id', 7)->first();
        if (!$integration) {
            $this->error("Không tìm thấy integration");
            return;
        }

        $appKey = $integration->getAppKey();
        $appSecret = $integration->getAppSecret();

        $this->info("App Key: {$appKey}");
        $this->info("App Secret Length: " . strlen($appSecret));

        // Test 1: Webhook example từ TikTok documentation
        $this->info("\n=== TEST 1: WEBHOOK EXAMPLE ===");

        $webhookApiPath = '/event/202309/webhooks';
        $webhookQueryParams = [
            'app_key' => '68xu9ks5p4i8',
            'shop_cipher' => 'ROW_xkMbgAAAeVAQra0eZWebFQq5aIK',
            'timestamp' => '1696909648'
        ];

        $webhookBodyParams = [
            'address' => 'https://partner.tiktokshop.com',
            'event_type' => 'PACKAGE_UPDATE'
        ];

        // Tạo signature theo TikTok example
        $webhookSignature = TikTokSignatureService::generateCustomSignature(
            '68xu9ks5p4i8',
            'e59af819cc', // App secret từ TikTok example
            $webhookApiPath,
            $webhookQueryParams,
            $webhookBodyParams,
            'application/json'
        );

        $this->info("Webhook Signature: {$webhookSignature}");

        // Expected từ TikTok: b596b73e0cc6de07ac26f036364178ab16b0a907af13d43f0a0cd2345f582dc8
        $expectedWebhook = 'b596b73e0cc6de07ac26f036364178ab16b0a907af13d43f0a0cd2345f582dc8';
        $this->info("Expected: {$expectedWebhook}");
        $this->info("Match: " . ($webhookSignature === $expectedWebhook ? 'YES' : 'NO'));

        // Test 2: Product upload với data thực tế
        $this->info("\n=== TEST 2: PRODUCT UPLOAD ===");

        $productApiPath = '/product/202309/products';
        $productQueryParams = [
            'app_key' => $appKey,
            'shop_cipher' => 'GCP_P3DQQQAAAADHGmVrcj6COQOADjHSJeoe',
            'timestamp' => '1757566259'
        ];

        $productBodyParams = [
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
            'idempotency_key' => 'test_1757566259_68c255338256c'
        ];

        $productSignature = TikTokSignatureService::generateCustomSignature(
            $appKey,
            $appSecret,
            $productApiPath,
            $productQueryParams,
            $productBodyParams,
            'application/json'
        );

        $this->info("Product Signature: {$productSignature}");

        // Test 3: So sánh với method generateProductUploadSignature
        $this->info("\n=== TEST 3: COMPARISON ===");

        $productSignature2 = TikTokSignatureService::generateProductUploadSignature(
            $appKey,
            $appSecret,
            '1757566259',
            $productBodyParams,
            'GCP_P3DQQQAAAADHGmVrcj6COQOADjHSJeoe'
        );

        $this->info("Product Signature (method 1): {$productSignature}");
        $this->info("Product Signature (method 2): {$productSignature2}");
        $this->info("Match: " . ($productSignature === $productSignature2 ? 'YES' : 'NO'));

        // Test 4: Kiểm tra string to sign
        $this->info("\n=== TEST 4: STRING TO SIGN ANALYSIS ===");

        // Filter và sort parameters
        $filteredParams = array_filter($productQueryParams, function ($key) {
            return !in_array($key, ['sign', 'access_token']);
        }, ARRAY_FILTER_USE_KEY);
        ksort($filteredParams);

        // Tạo param string
        $paramString = '';
        foreach ($filteredParams as $key => $value) {
            $paramString .= $key . $value;
        }

        // Tạo input
        $input = $productApiPath . $paramString;

        // Thêm body
        $bodyString = json_encode($productBodyParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $input .= $bodyString;

        // Wrap với app_secret
        $stringToSign = $appSecret . $input . $appSecret;

        $this->info("Input (path + params + body): " . substr($input, 0, 200) . "...");
        $this->info("String to Sign Length: " . strlen($stringToSign));
        $this->info("String to Sign (first 100): " . substr($stringToSign, 0, 100) . "...");
        $this->info("String to Sign (last 100): ..." . substr($stringToSign, -100));

        $this->info("\n=== COMPLETED ===");
    }
}
