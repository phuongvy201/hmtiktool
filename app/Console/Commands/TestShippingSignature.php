<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TikTokSignatureService;
use App\Models\TikTokOrder;
use App\Models\TikTokShop;

class TestShippingSignature extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:shipping-signature {order_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test TikTok Shipping API signature generation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderId = $this->argument('order_id');

        try {
            // Tìm đơn hàng
            $order = TikTokOrder::with('shop')->findOrFail($orderId);

            $this->info("Testing signature for Order ID: {$order->order_id}");
            $this->info("Shop: {$order->shop->shop_name}");

            // Lấy delivery_option_id
            $deliveryOptionId = $order->order_data['delivery_option_id'] ?? null;

            if (!$deliveryOptionId) {
                $this->error('Không tìm thấy delivery_option_id trong order_data');
                return;
            }

            $this->info("Delivery Option ID: {$deliveryOptionId}");

            // Lấy app credentials
            $appKey = config('tiktok-shop.app_key');
            $appSecret = config('tiktok-shop.app_secret');
            $shopCipher = $order->shop->getShopCipher();

            $this->info("App Key: {$appKey}");
            $this->info("Shop Cipher: {$shopCipher}");
            $this->info("App Secret Length: " . strlen($appSecret));

            // Tạo timestamp
            $timestamp = time();
            $this->info("Timestamp: {$timestamp}");

            // Test signature generation
            $signature = TikTokSignatureService::generateShippingProvidersSignature(
                $appKey,
                $appSecret,
                $timestamp,
                $shopCipher,
                $deliveryOptionId
            );

            $this->info("Generated Signature: {$signature}");

            // Build test URL
            $queryParams = [
                'app_key' => $appKey,
                'timestamp' => $timestamp,
                'shop_cipher' => $shopCipher,
                'sign' => $signature
            ];

            $url = "https://open-api.tiktokglobalshop.com/logistics/202309/delivery_options/{$deliveryOptionId}/shipping_providers?" . http_build_query($queryParams);

            $this->info("Test URL: {$url}");

            // Test API call
            $this->info("\nTesting API call...");

            $integration = $order->shop->integration;
            if (!$integration || !$integration->isActive()) {
                $this->error('Integration không hoạt động');
                return;
            }

            $headers = [
                'Content-Type: application/json',
                'x-tts-access-token: ' . $integration->access_token
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $this->info("HTTP Code: {$httpCode}");
            $this->info("Response: {$response}");

            if ($httpCode === 200) {
                $data = json_decode($response, true);
                if (isset($data['code']) && $data['code'] === 0) {
                    $this->info("✅ API call successful!");
                    $this->info("Shipping Providers: " . json_encode($data['data']['shipping_providers'] ?? [], JSON_PRETTY_PRINT));
                } else {
                    $this->error("❌ API Error: " . ($data['message'] ?? 'Unknown error'));
                }
            } else {
                $this->error("❌ HTTP Error: {$httpCode}");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
