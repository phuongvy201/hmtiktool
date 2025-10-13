<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test TikTok Webhook ===\n\n";

// Test payload cho order status update
$testPayload = [
    'event_type' => 'order.status.updated',
    'order_id' => '576786823924783896', // Order ID thực tế từ database
    'order_status' => 'IN_TRANSIT',
    'shop_id' => '12', // Shop ID thực tế
    'update_time' => time(),
    'timestamp' => time()
];

echo "Test payload:\n";
echo json_encode($testPayload, JSON_PRETTY_PRINT) . "\n\n";

// Test với curl
$url = 'http://localhost:8000/tiktok/webhook/handle';
$payload = json_encode($testPayload);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'X-TikTok-Signature: test-signature' // Test signature
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

echo "Sending test webhook to: $url\n";
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

// Test endpoint
echo "=== Testing webhook endpoint ===\n";
$testUrl = 'http://localhost:8000/tiktok/webhook/test';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Test endpoint HTTP Code: $httpCode\n";
echo "Test endpoint Response: $response\n";

echo "\n=== Test Complete ===\n";
