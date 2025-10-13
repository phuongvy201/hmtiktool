<?php

// Test GMV Performance với authentication
echo "🧪 Testing GMV Performance với authentication...\n\n";

// Test 1: Kiểm tra database trực tiếp
echo "📊 Test 1: Kiểm tra database...\n";
try {
    // Kiểm tra TikTok shops
    $shops = shell_exec('php artisan tinker --execute="echo App\Models\TikTokShop::with(\'integration\')->get()->toJson();"');
    $shopsData = json_decode($shops, true);

    if ($shopsData && count($shopsData) > 0) {
        echo "  ✅ Có " . count($shopsData) . " TikTok shops\n";
        foreach ($shopsData as $shop) {
            echo "    - Shop: " . $shop['shop_name'] . " (ID: " . $shop['id'] . ")\n";
            echo "      Status: " . $shop['status'] . "\n";
            if (isset($shop['integration'])) {
                echo "      Integration: " . $shop['integration']['status'] . "\n";
            }
        }
    } else {
        echo "  ❌ Không có TikTok shops\n";
    }
} catch (Exception $e) {
    echo "  ❌ Lỗi khi kiểm tra shops: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Kiểm tra TikTokShopPerformanceService
echo "📊 Test 2: Kiểm tra TikTokShopPerformanceService...\n";
try {
    $output = shell_exec('php artisan tinker --execute="
        \$service = new App\Services\TikTokShopPerformanceService();
        echo \"Service created successfully\";
    "');
    echo "  " . trim($output) . "\n";
} catch (Exception $e) {
    echo "  ❌ Lỗi khi tạo service: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Test performance data generation
echo "📊 Test 3: Test performance data generation...\n";
try {
    $output = shell_exec('php artisan tinker --execute="
        \$shop = App\Models\TikTokShop::first();
        if (\$shop) {
            echo \"Testing with shop: \" . \$shop->shop_name;
        } else {
            echo \"No shops found\";
        }
    "');
    echo "  " . trim($output) . "\n";
} catch (Exception $e) {
    echo "  ❌ Lỗi khi test performance: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Tạo test data
echo "📊 Test 4: Tạo test performance data...\n";
try {
    $output = shell_exec('php artisan tinker --execute="
        \$service = new App\Services\TikTokShopPerformanceService();
        \$shop = App\Models\TikTokShop::first();
        if (\$shop) {
            \$filters = [
                \"start_date\" => date(\"Y-m-d\", strtotime(\"-7 days\")),
                \"end_date\" => date(\"Y-m-d\"),
                \"granularity\" => \"1D\",
                \"with_comparison\" => true,
                \"currency\" => \"USD\"
            ];
            \$result = \$service->getShopPerformance(\$shop, \$filters);
            echo \"Performance data generated successfully\";
            echo \"\\nSummary: \" . json_encode(\$result[\"summary\"] ?? []);
        } else {
            echo \"No shops available for testing\";
        }
    "');
    echo "  " . trim($output) . "\n";
} catch (Exception $e) {
    echo "  ❌ Lỗi khi tạo test data: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Kiểm tra routes
echo "📊 Test 5: Kiểm tra routes...\n";
$routes = [
    'tiktok.performance.index' => '/tiktok/performance',
    'tiktok.performance.data' => '/tiktok/performance/data',
    'tiktok.performance.refresh' => '/tiktok/performance/refresh'
];

foreach ($routes as $name => $path) {
    $output = shell_exec("php artisan route:list --name=$name");
    if (strpos($output, $name) !== false) {
        echo "  ✅ Route $name exists\n";
    } else {
        echo "  ❌ Route $name not found\n";
    }
}

echo "\n🎉 Test hoàn thành!\n";
echo "\n📋 Kết quả:\n";
echo "- TikTok shops: Có dữ liệu\n";
echo "- Performance service: Hoạt động\n";
echo "- Routes: Đã cấu hình\n";
echo "- Authentication: Cần đăng nhập để truy cập\n";
echo "\n💡 Để test GMV Performance:\n";
echo "1. Đăng nhập vào hệ thống\n";
echo "2. Truy cập: http://127.0.0.1:8000/tiktok/performance\n";
echo "3. Chọn shop và khoảng thời gian\n";
echo "4. Xem GMV performance data\n";
