<?php

// Test signature generation với các format khác nhau
echo "🧪 Testing TikTok Signature Generation...\n\n";

// Parameters từ logs
$params = [
    'app_key' => '6h5b0bsgaonml',
    'timestamp' => 1758617333,
    'shop_cipher' => 'GCP_P3DQQQAAAADHGmVrcj6COQOADjHSJeoe',
    'granularity' => '1D',
    'start_date_ge' => '2025-09-16',
    'end_date_lt' => '2025-09-23',
    'with_comparison' => 'true',
    'currency' => 'USD'
];

$appSecret = '55f4e32e0749bc3eb94bf8d422dd407fbffdbb69';
$endpoint = '/analytics/202405/shop/performance';

echo "📊 Test 1: Signature hiện tại (với string values)\n";
$signature1 = generateSignature($params, $appSecret, $endpoint);
echo "Signature: " . $signature1 . "\n\n";

echo "📊 Test 2: Signature với boolean values\n";
$params2 = $params;
$params2['with_comparison'] = true; // Boolean thay vì string
$signature2 = generateSignature($params2, $appSecret, $endpoint);
echo "Signature: " . $signature2 . "\n\n";

echo "📊 Test 3: Signature với timestamp hiện tại\n";
$params3 = $params;
$params3['timestamp'] = time();
$signature3 = generateSignature($params3, $appSecret, $endpoint);
echo "Signature: " . $signature3 . "\n\n";

echo "📊 Test 4: Signature với thứ tự parameters khác\n";
$params4 = [
    'app_key' => '6h5b0bsgaonml',
    'timestamp' => 1758617333,
    'shop_cipher' => 'GCP_P3DQQQAAAADHGmVrcj6COQOADjHSJeoe',
    'granularity' => '1D',
    'start_date_ge' => '2025-09-16',
    'end_date_lt' => '2025-09-23',
    'with_comparison' => true,
    'currency' => 'USD'
];
$signature4 = generateSignature($params4, $appSecret, $endpoint);
echo "Signature: " . $signature4 . "\n\n";

echo "📊 Test 5: Signature với format khác (không có endpoint trong string)\n";
$signature5 = generateSignatureWithoutEndpoint($params, $appSecret);
echo "Signature: " . $signature5 . "\n\n";

function generateSignature($params, $appSecret, $endpoint) {
    // Lọc bỏ sign parameter
    $filteredParams = array_filter($params, function ($key) {
        return !in_array($key, ['sign']);
    }, ARRAY_FILTER_USE_KEY);

    // Sắp xếp parameters theo key
    ksort($filteredParams);

    // Tạo param string
    $paramString = '';
    foreach ($filteredParams as $key => $value) {
        $paramString .= $key . $value;
    }

    // Tạo string để sign
    $stringToSign = $appSecret . $endpoint . $paramString . $appSecret;

    // Tạo signature
    $signature = strtoupper(hash('sha256', $stringToSign));

    echo "  Params: " . json_encode($filteredParams) . "\n";
    echo "  Param String: " . $paramString . "\n";
    echo "  String to Sign: " . $stringToSign . "\n";

    return $signature;
}

function generateSignatureWithoutEndpoint($params, $appSecret) {
    // Lọc bỏ sign parameter
    $filteredParams = array_filter($params, function ($key) {
        return !in_array($key, ['sign']);
    }, ARRAY_FILTER_USE_KEY);

    // Sắp xếp parameters theo key
    ksort($filteredParams);

    // Tạo param string
    $paramString = '';
    foreach ($filteredParams as $key => $value) {
        $paramString .= $key . $value;
    }

    // Tạo string để sign (không có endpoint)
    $stringToSign = $appSecret . $paramString . $appSecret;

    // Tạo signature
    $signature = strtoupper(hash('sha256', $stringToSign));

    echo "  Params: " . json_encode($filteredParams) . "\n";
    echo "  Param String: " . $paramString . "\n";
    echo "  String to Sign: " . $stringToSign . "\n";

    return $signature;
}

echo "🎉 Test hoàn thành!\n";