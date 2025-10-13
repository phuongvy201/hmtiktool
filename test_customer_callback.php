<?php

// Test customer callback với URL thực tế (sử dụng route public)
$url = 'http://127.0.0.1:8000/public/customer-callback?app_key=6h5b0bsgaonml&code=GCP_AhaQqQAAAAC73m1XWW50tl9OYJfkdb3pkcpvzhjDbo2XR6rqaWeUtHs4oIx_3t7y2jGdc1nIaLgvfvq_M3QIxI975FZp990UsbfAdK4xVaCPDPiEiXUIzk9uNsMsJML3J-bb0vF_Lps&locale=en-GB&shop_region=GB&state=7';

echo "🧪 Testing customer callback với URL thực tế...\n";
echo "URL: " . $url . "\n\n";

// Sử dụng cURL để test
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

echo "📊 Kết quả test:\n";
echo "HTTP Code: " . $httpCode . "\n";

if ($error) {
    echo "❌ Lỗi cURL: " . $error . "\n";
} else {
    echo "✅ Response length: " . strlen($response) . " bytes\n";

    // Kiểm tra xem có chứa authorization code không
    if (strpos($response, 'GCP_AhaQqQAAAAC73m1XWW50tl9OYJfkdb3pkcpvzhjDbo2XR6rqaWeUtHs4oIx_3t7y2jGdc1nIaLgvfvq_M3QIxI975FZp990UsbfAdK4xVaCPDPiEiXUIzk9uNsMsJML3J-bb0vF_Lps') !== false) {
        echo "✅ Authorization code được hiển thị trong response\n";
    } else {
        echo "❌ Authorization code không được tìm thấy trong response\n";
    }

    // Kiểm tra xem có chứa thông báo thành công không
    if (strpos($response, 'Thành công!') !== false) {
        echo "✅ Thông báo thành công được hiển thị\n";
    } else {
        echo "❌ Thông báo thành công không được tìm thấy\n";
    }

    // Lưu response vào file để kiểm tra
    file_put_contents('customer_callback_response.html', $response);
    echo "💾 Response đã được lưu vào customer_callback_response.html\n";
}

echo "\n🎉 Test hoàn thành!\n";
