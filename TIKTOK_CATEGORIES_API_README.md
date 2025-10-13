# TikTok Shop Categories API Integration

## Tổng quan

Hệ thống đã được tích hợp với TikTok Shop API để lấy danh sách categories thực tế thay vì sử dụng danh sách cứng. Điều này đảm bảo rằng các categories luôn được cập nhật và chính xác theo TikTok Shop.

## Tính năng

### 1. API Integration

-   **Endpoint**: `/product/202309/get_categories`
-   **Method**: GET
-   **Authentication**: Sử dụng access token từ TikTok Shop integration
-   **Signature**: HMAC-SHA256 signature theo chuẩn TikTok Shop API

### 2. Fallback System

-   Nếu không có TikTok Shop integration hoặc API call thất bại, hệ thống sẽ sử dụng danh sách categories mặc định
-   Đảm bảo tính ổn định của hệ thống

### 3. Caching & Performance

-   Categories được lấy real-time từ TikTok Shop API
-   Không cache để đảm bảo dữ liệu luôn mới nhất

## Cách sử dụng

### 1. Trong ProductTemplateController

```php
// Tự động lấy categories từ TikTok Shop API
$categories = $this->getCategories();

// Hoặc sử dụng service trực tiếp
$tikTokService = app(TikTokShopService::class);
$categories = $tikTokService->getCategoriesWithFallback($integration);
```

### 2. API Methods

#### getCategories(TikTokShopIntegration $integration)

Lấy categories từ TikTok Shop API:

```php
$result = $tikTokService->getCategories($integration);

if ($result['success']) {
    $categories = $result['data']; // Array: ['id' => 'name']
    $rawData = $result['raw_data']; // Raw API response
} else {
    $error = $result['error'];
}
```

#### getCategoriesWithFallback(TikTokShopIntegration $integration = null)

Lấy categories với fallback:

```php
// Với integration
$categories = $tikTokService->getCategoriesWithFallback($integration);

// Không có integration (sử dụng default)
$categories = $tikTokService->getCategoriesWithFallback();
```

### 3. Default Categories

Nếu API thất bại, hệ thống sẽ sử dụng danh sách categories mặc định:

```php
$defaultCategories = [
    '100001' => 'Thời trang nam',
    '100002' => 'Thời trang nữ',
    '100003' => 'Thời trang trẻ em',
    '100004' => 'Giày dép',
    '100005' => 'Túi xách',
    '100006' => 'Phụ kiện thời trang',
    '100007' => 'Đồng hồ',
    '100008' => 'Trang sức',
    '100009' => 'Mỹ phẩm',
    '100010' => 'Chăm sóc cá nhân',
    '100011' => 'Điện tử',
    '100012' => 'Điện thoại & Phụ kiện',
    '100013' => 'Máy tính & Laptop',
    '100014' => 'Gaming',
    '100015' => 'Nhà cửa & Đời sống',
    '100016' => 'Đồ gia dụng',
    '100017' => 'Nội thất',
    '100018' => 'Thể thao & Dã ngoại',
    '100019' => 'Sách & Văn phòng phẩm',
    '100020' => 'Đồ chơi & Sở thích',
    '100021' => 'Thực phẩm & Đồ uống',
    '100022' => 'Sức khỏe & Y tế',
    '100023' => 'Ô tô & Xe máy',
    '100024' => 'Mẹ & Bé',
    '100025' => 'Thú cưng',
    '100026' => 'Khác'
];
```

## Testing

### 1. Test Script

Chạy file test để kiểm tra API:

```bash
php test_tiktok_categories.php
```

### 2. Web Route Test

Truy cập route để test API:

```
GET /test/tiktok-categories
```

### 3. Manual Testing

1. Đảm bảo có TikTok Shop integration với access token hợp lệ
2. Truy cập trang tạo Product Template
3. Kiểm tra dropdown categories có dữ liệu từ TikTok Shop API

## Error Handling

### 1. Common Error Codes

-   **106001**: Access token không hợp lệ hoặc đã hết hạn
-   **401**: Lỗi xác thực
-   **Network errors**: Lỗi kết nối mạng

### 2. Fallback Behavior

-   Nếu API thất bại → Sử dụng default categories
-   Nếu không có integration → Sử dụng default categories
-   Log warning khi sử dụng fallback

## Logging

Hệ thống log các thông tin sau:

-   API requests và responses
-   Error messages
-   Fallback usage warnings

```php
Log::info('TikTok Shop API Request - Get Categories', [...]);
Log::error('TikTok Shop Get Categories Error', [...]);
Log::warning('Failed to get categories from TikTok Shop API, using default categories', [...]);
```

## Security

-   Access token được ẩn trong logs
-   API calls sử dụng signature authentication
-   Token refresh tự động khi hết hạn

## Performance Considerations

-   API calls được thực hiện real-time
-   Không cache để đảm bảo dữ liệu mới nhất
-   Fallback system đảm bảo performance khi API thất bại

## Troubleshooting

### 1. API không hoạt động

-   Kiểm tra access token có hợp lệ không
-   Kiểm tra network connection
-   Xem logs để debug

### 2. Categories không hiển thị

-   Kiểm tra TikTok Shop integration
-   Kiểm tra fallback categories
-   Xem browser console cho errors

### 3. Performance issues

-   API calls có thể chậm nếu network không ổn định
-   Fallback system sẽ đảm bảo UI responsive

## Future Improvements

1. **Caching**: Có thể thêm cache với TTL ngắn (1-2 giờ)
2. **Background Sync**: Sync categories trong background
3. **Category Mapping**: Map categories giữa các platform khác nhau
4. **Category Hierarchy**: Hiển thị categories theo cấu trúc phân cấp
