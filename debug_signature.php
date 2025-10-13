<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DEBUG TIKTOK SIGNATURE ===\n";

// Lấy shop và integration
$shop = \App\Models\TikTokShop::with('integration')->find(8);
$integration = $shop->integration;

echo "Shop: {$shop->shop_name}\n";
echo "Shop ID: {$shop->shop_id}\n";
echo "Shop Cipher: {$shop->getShopCipher()}\n";
echo "App Key: {$integration->getAppKey()}\n";
echo "App Secret Length: " . strlen($integration->getAppSecret()) . "\n";
echo "Access Token Length: " . strlen($integration->access_token) . "\n";

// Test signature generation
$timestamp = time();
$bodyParams = [
    'order_status' => 'UNPAID',
    'create_time_ge' => strtotime('-7 days'),
    'create_time_lt' => time()
];

echo "\n=== SIGNATURE GENERATION ===\n";
echo "Timestamp: {$timestamp}\n";
echo "Body Params: " . json_encode($bodyParams) . "\n";

$signature = \App\Services\TikTokSignatureService::generateOrderSearchSignature(
    $integration->getAppKey(),
    $integration->getAppSecret(),
    (string) $timestamp,
    $bodyParams,
    $shop->getShopCipher()
);

echo "Generated Signature: {$signature}\n";

// Test API call
echo "\n=== TEST API CALL ===\n";

$queryParams = [
    'shop_cipher' => $shop->getShopCipher(),
    'app_key' => $integration->getAppKey(),
    'timestamp' => $timestamp,
    'page_size' => 10,
    'sort_order' => 'DESC',
    'sort_field' => 'create_time',
    'sign' => $signature
];

$url = 'https://open-api.tiktokglobalshop.com/order/202309/orders/search';
$fullUrl = $url . '?' . http_build_query($queryParams);

echo "Full URL: {$fullUrl}\n";

$headers = [
    'Content-Type' => 'application/json',
    'x-tts-access-token' => $integration->access_token
];

$jsonBody = json_encode($bodyParams, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

echo "Headers: " . json_encode($headers) . "\n";
echo "Body: {$jsonBody}\n";

// Gọi API
$response = \Illuminate\Support\Facades\Http::withHeaders($headers)
    ->timeout(30)
    ->withBody($jsonBody, 'application/json')
    ->post($fullUrl);

$httpCode = $response->status();
$responseData = $response->json();

echo "\n=== API RESPONSE ===\n";
echo "HTTP Code: {$httpCode}\n";
echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";
