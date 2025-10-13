# Hướng dẫn Test Warehouses với Shop Cipher

## Tổng quan

Đây là hướng dẫn sử dụng các hàm test để lấy warehouses từ TikTok Shop API sử dụng shop cipher từ TikTokShop model thay vì shop_id.

## Các hàm đã được tạo

### 1. TikTokShopService Methods

#### `testGetWarehousesWithShopCipher(int $shopId = null): array`

-   Test lấy warehouses cho một shop cụ thể
-   Sử dụng shop cipher từ TikTokShop model
-   Có fallback logic để lấy cipher từ các nguồn khác nhau

#### `testGetWarehousesForAllShops(): array`

-   Test lấy warehouses cho tất cả shops trong database
-   Trả về summary và chi tiết từng shop

#### `getWarehousesWithCipher(TikTokShopIntegration $integration, string $shopCipher): array`

-   Hàm core để gọi TikTok Shop API với shop cipher
-   Sử dụng signature authentication

#### `getWarehouses(TikTokShopIntegration $integration, int $shopId = null): array`

-   Hàm chính để lấy warehouses (đã được cập nhật để sử dụng shop cipher)
-   Tự động lấy shop cipher từ TikTokShop model
-   Hỗ trợ truyền shop ID hoặc lấy shop đầu tiên

### 2. TikTokShop Model Methods

#### `getShopCipher(): ?string`

-   Lấy shop cipher với fallback logic:
    1. Ưu tiên từ trường `cipher`
    2. Fallback từ `shop_data['cipher']` hoặc `shop_data['shop_cipher']`
    3. Cuối cùng fallback về `shop_id`

#### `hasValidCipher(): bool`

-   Kiểm tra xem shop có cipher hợp lệ không

### 3. Console Command

#### `TestWarehousesCommand`

-   Command để test warehouses từ command line
-   Hỗ trợ test một shop hoặc tất cả shops

## Cách sử dụng

### 1. Test một shop cụ thể

```bash
# Test shop đầu tiên trong database
php artisan test:warehouses

# Test shop với ID cụ thể
php artisan test:warehouses 1
```

### 2. Test tất cả shops

```bash
php artisan test:warehouses --all
```

### 3. Sử dụng trong code

```php
use App\Services\TikTokShopService;

$tiktokService = new TikTokShopService();

// Test một shop
$result = $tiktokService->testGetWarehousesWithShopCipher(1);

// Test tất cả shops
$result = $tiktokService->testGetWarehousesForAllShops();

// Sử dụng trực tiếp với cipher
$result = $tiktokService->getWarehousesWithCipher($integration, $shopCipher);

// Sử dụng hàm chính (tự động lấy cipher từ shop)
$result = $tiktokService->getWarehouses($integration, 1); // shop ID
$result = $tiktokService->getWarehouses($integration); // shop đầu tiên
```

### 4. Sử dụng TikTokShop model

```php
use App\Models\TikTokShop;

$shop = TikTokShop::find(1);

// Lấy shop cipher
$cipher = $shop->getShopCipher();

// Kiểm tra cipher hợp lệ
if ($shop->hasValidCipher()) {
    // Sử dụng cipher
}
```

## Cấu trúc Response

### Thành công

```php
[
    'success' => true,
    'data' => [
        [
            'id' => '7540452453539350295',
            'name' => 'Sandbox GB Local Sales warehouse',
            'type' => 'SALES_WAREHOUSE',
            'sub_type' => 'DOMESTIC_WAREHOUSE',
            'effect_status' => 'ENABLED',
            'is_default' => true,
            'address' => [
                'address_line1' => '4 Lindsey St',
                'city' => 'Greater London',
                'region_code' => 'GB',
                // ... other address fields
            ]
        ]
    ],
    'request_id' => '202509100235476BCA380294FAC535B9B7'
]
```

### Thất bại

```php
[
    'success' => false,
    'error' => 'Lỗi mô tả'
]
```

## Logging

Tất cả các hàm đều có logging chi tiết:

-   Request parameters
-   API response
-   Error handling
-   Shop cipher source

Logs được ghi vào Laravel log files.

## Lưu ý quan trọng

1. **Shop Cipher Priority**: Hàm sẽ ưu tiên lấy cipher từ trường `cipher` trước, sau đó mới fallback sang các nguồn khác.

2. **Token Management**: Hàm tự động kiểm tra và refresh access token nếu cần.

3. **Error Handling**: Có xử lý lỗi chi tiết cho các trường hợp:

    - Không tìm thấy shop
    - Không có integration
    - Không có cipher
    - API errors
    - Token expired

4. **Signature**: Sử dụng TikTokSignatureService để tạo signature cho API calls.

## Troubleshooting

### Lỗi "Không tìm thấy shop cipher"

-   Kiểm tra xem shop có trường `cipher` không
-   Kiểm tra `shop_data` có chứa cipher không
-   Fallback cuối cùng sẽ dùng `shop_id`

### Lỗi "Access token không tồn tại"

-   Kiểm tra integration có access_token không
-   Chạy lại quá trình OAuth để lấy token mới

### Lỗi API từ TikTok

-   Kiểm tra logs để xem chi tiết lỗi
-   Kiểm tra signature generation
-   Kiểm tra timestamp (phải trong vòng 5 phút)

## Ví dụ Output

### Command Line Output

```
=== TEST WAREHOUSES WITH SHOP CIPHER ===
Testing với shop ID: 1
✅ Test thành công!
📦 Tìm thấy 3 warehouses
📋 Danh sách warehouses:
+---------------+------------------+-------------+--------+
| Warehouse ID  | Warehouse Name   | Type        | Status |
+---------------+------------------+-------------+--------+
| warehouse_123 | Main Warehouse   | FULFILLMENT | ACTIVE |
| warehouse_456 | Secondary WH     | FULFILLMENT | ACTIVE |
| warehouse_789 | Backup Warehouse | FULFILLMENT | ACTIVE |
+---------------+------------------+-------------+--------+
Request ID: req_abc123
=== END TEST ===
```

### Test All Shops Output

```
=== TEST WAREHOUSES WITH SHOP CIPHER ===
Testing tất cả shops trong database...
✅ Test tất cả shops hoàn thành!
📊 Tổng kết:
   - Tổng số shops: 5
   - Thành công: 3
   - Thất bại: 2
📋 Chi tiết từng shop:
+---------+-------------+----------------+--------+------------------+------------+
| Shop ID | Shop Name   | TikTok Shop ID | Cipher | Status           | Warehouses |
+---------+-------------+----------------+--------+------------------+------------+
| 1       | Shop A      | shop_123       | abc123 | ✅ Thành công    | 3          |
| 2       | Shop B      | shop_456       | def456 | ✅ Thành công    | 1          |
| 3       | Shop C      | shop_789       | ghi789 | ✅ Thành công    | 0          |
| 4       | Shop D      | shop_101       | jkl101 | ❌ Token expired | 0          |
| 5       | Shop E      | shop_202       | mno202 | ❌ No cipher     | 0          |
+---------+-------------+----------------+--------+------------------+------------+
=== END TEST ===
```
