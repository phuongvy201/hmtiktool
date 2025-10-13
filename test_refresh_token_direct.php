<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== TEST REFRESH TOKEN DIRECT ===\n";

$integration = \App\Models\TikTokShopIntegration::find(10);

if (!$integration) {
    echo "❌ Integration không tồn tại\n";
    exit;
}

echo "Integration ID: {$integration->id}\n";
echo "App Key: {$integration->getAppKey()}\n";
echo "App Secret: " . substr($integration->getAppSecret(), 0, 10) . "...\n";
echo "Refresh Token: " . substr($integration->refresh_token, 0, 20) . "...\n";

// Test trực tiếp với cURL - thử nhiều URL khác nhau
$urls = [
    'https://auth.tiktok-shops.com/api/v2/token/refresh',
    'https://open-api.tiktok-shops.com/api/v2/token/refresh',
    'https://open-api.tiktokglobalshop.com/api/v2/token/refresh',
    'https://open.tiktokapis.com/v2/oauth/token/'
];

foreach ($urls as $url) {
    echo "\n=== Testing URL: {$url} ===\n";

    $data = [
        'app_key' => $integration->getAppKey(),
        'app_secret' => $integration->getAppSecret(),
        'refresh_token' => $integration->refresh_token,
        'grant_type' => 'refresh_token'
    ];

    $queryString = http_build_query($data);
    $fullUrl = $url . '?' . $queryString;

    echo "Request URL: {$fullUrl}\n";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $fullUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ],
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "HTTP Code: {$httpCode}\n";
    echo "cURL Error: " . ($error ?: 'None') . "\n";
    echo "Response: " . substr($response, 0, 200) . "\n";

    if ($response && $httpCode === 200) {
        $data = json_decode($response, true);
        if ($data) {
            echo "✅ SUCCESS! Parsed Response:\n";
            echo json_encode($data, JSON_PRETTY_PRINT) . "\n";
            break; // Dừng khi tìm thấy URL hoạt động
        }
    }
}
