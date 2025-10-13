<?php

// Test GMV Performance Dashboard
echo "🧪 Testing GMV Performance Dashboard...\n\n";

// Test 1: Kiểm tra route có tồn tại không
echo "📊 Test 1: Kiểm tra routes...\n";
$routes = [
    'http://127.0.0.1:8000/tiktok/performance',
    'http://127.0.0.1:8000/tiktok/performance/data',
    'http://127.0.0.1:8000/tiktok/performance/refresh'
];

foreach ($routes as $route) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $route);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_NOBODY, true); // Chỉ lấy header

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "  $route: HTTP $httpCode\n";
}

echo "\n";

// Test 2: Kiểm tra TikTok shops có dữ liệu không
echo "📊 Test 2: Kiểm tra TikTok shops...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/tiktok/performance');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "  Performance page: HTTP $httpCode\n";

if ($httpCode == 200) {
    // Kiểm tra xem có shops không
    if (strpos($response, 'Chọn shop để xem performance') !== false) {
        echo "  ✅ Có dropdown chọn shop\n";
    } else {
        echo "  ❌ Không tìm thấy dropdown chọn shop\n";
    }

    // Kiểm tra xem có GMV dashboard không
    if (strpos($response, 'GMV Performance Dashboard') !== false) {
        echo "  ✅ Có GMV Performance Dashboard\n";
    } else {
        echo "  ❌ Không tìm thấy GMV Performance Dashboard\n";
    }

    // Kiểm tra xem có charts không
    if (strpos($response, 'chart-container') !== false) {
        echo "  ✅ Có chart containers\n";
    } else {
        echo "  ❌ Không tìm thấy chart containers\n";
    }

    // Lưu response để kiểm tra
    file_put_contents('gmv_performance_response.html', $response);
    echo "  💾 Response đã được lưu vào gmv_performance_response.html\n";
} else {
    echo "  ❌ Không thể truy cập performance page\n";
}

echo "\n";

// Test 3: Kiểm tra TikTok shops trong database
echo "📊 Test 3: Kiểm tra TikTok shops trong database...\n";
try {
    // Chạy artisan command để kiểm tra shops
    $output = shell_exec('php artisan tinker --execute="echo App\Models\TikTokShop::count();"');
    echo "  Số lượng TikTok shops: " . trim($output) . "\n";

    // Kiểm tra integrations
    $output = shell_exec('php artisan tinker --execute="echo App\Models\TikTokShopIntegration::count();"');
    echo "  Số lượng TikTok integrations: " . trim($output) . "\n";

    // Kiểm tra active integrations
    $output = shell_exec('php artisan tinker --execute="echo App\Models\TikTokShopIntegration::where(\'status\', \'active\')->count();"');
    echo "  Số lượng active integrations: " . trim($output) . "\n";
} catch (Exception $e) {
    echo "  ❌ Lỗi khi kiểm tra database: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Kiểm tra performance service
echo "📊 Test 4: Kiểm tra TikTokShopPerformanceService...\n";
try {
    $output = shell_exec('php artisan tinker --execute="echo class_exists(\'App\Services\TikTokShopPerformanceService\') ? \'EXISTS\' : \'NOT_FOUND\';"');
    echo "  TikTokShopPerformanceService: " . trim($output) . "\n";
} catch (Exception $e) {
    echo "  ❌ Lỗi khi kiểm tra service: " . $e->getMessage() . "\n";
}

echo "\n🎉 Test hoàn thành!\n";
echo "\n📋 Hướng dẫn sử dụng:\n";
echo "1. Truy cập: http://127.0.0.1:8000/tiktok/performance\n";
echo "2. Chọn shop từ dropdown\n";
echo "3. Chọn khoảng thời gian\n";
echo "4. Click 'Load Data' để xem GMV performance\n";
echo "5. Sử dụng 'Refresh' để cập nhật dữ liệu\n";
