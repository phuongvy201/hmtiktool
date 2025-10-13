<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST INTEGRATION PERMISSIONS ===\n";

// Lấy shop và integration
$shop = \App\Models\TikTokShop::with('integration')->find(8);
$integration = $shop->integration;

echo "Shop: {$shop->shop_name}\n";
echo "Integration ID: {$integration->id}\n";
echo "App Name: {$integration->app_name}\n";
echo "App Key: {$integration->getAppKey()}\n";
echo "Access Token Length: " . strlen($integration->access_token) . "\n";
echo "Is Active: " . ($integration->isActive() ? 'Yes' : 'No') . "\n";

// Test với một API đơn giản hơn trước
echo "\n=== TEST SIMPLE API CALL ===\n";

$timestamp = time();
$shopCipher = $shop->getShopCipher();

// Test với API get shops (đơn giản hơn)
$url = 'https://open-api.tiktokglobalshop.com/shop/202309/shops';

$queryParams = [
    'app_key' => $integration->getAppKey(),
    'timestamp' => $timestamp,
    'shop_cipher' => $shopCipher
];

// Tạo signature cho shops API
$signature = \App\Services\TikTokSignatureService::generateCustomSignature(
    $integration->getAppKey(),
    $integration->getAppSecret(),
    '/shop/202309/shops',
    $queryParams,
    [],
    null
);

$queryParams['sign'] = $signature;

$headers = [
    'Content-Type' => 'application/json',
    'x-tts-access-token' => $integration->access_token
];

echo "Testing Shops API...\n";
echo "URL: {$url}\n";
echo "Query Params: " . json_encode($queryParams) . "\n";

$response = \Illuminate\Support\Facades\Http::withHeaders($headers)
    ->timeout(30)
    ->get($url . '?' . http_build_query($queryParams));

$httpCode = $response->status();
$responseData = $response->json();

echo "HTTP Code: {$httpCode}\n";
echo "Response: " . json_encode($responseData, JSON_PRETTY_PRINT) . "\n";

if ($httpCode === 200) {
    echo "✅ Shops API works! Integration has basic permissions.\n";
} else {
    echo "❌ Shops API failed. Integration may not have proper permissions.\n";
}
