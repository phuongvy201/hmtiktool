# TikTok Category Attributes System

Hệ thống quản lý attributes của categories từ TikTok Shop API.

## 🚀 Tính năng

-   **Sync Attributes**: Đồng bộ attributes từ TikTok Shop API cho từng category
-   **Quản lý Attributes**: Xem, phân loại và quản lý attributes theo category
-   **Real-time Status**: Kiểm tra trạng thái sync và thời gian cập nhật
-   **API Integration**: Tích hợp hoàn toàn với TikTok Shop API
-   **Web Interface**: Giao diện web thân thiện để quản lý

## 📋 Cấu trúc Database

### Bảng `tik_tok_category_attributes`

| Cột                      | Kiểu      | Mô tả                                             |
| ------------------------ | --------- | ------------------------------------------------- |
| `id`                     | bigint    | Primary key                                       |
| `category_id`            | string    | TikTok category ID                                |
| `attribute_id`           | string    | TikTok attribute ID                               |
| `name`                   | string    | Tên attribute                                     |
| `type`                   | enum      | Loại attribute (PRODUCT_PROPERTY, SALES_PROPERTY) |
| `is_required`            | boolean   | Có bắt buộc không                                 |
| `is_multiple_selection`  | boolean   | Có thể chọn nhiều không                           |
| `is_customizable`        | boolean   | Có thể tùy chỉnh không                            |
| `value_data_format`      | string    | Định dạng dữ liệu                                 |
| `values`                 | json      | Danh sách giá trị có sẵn                          |
| `requirement_conditions` | json      | Điều kiện yêu cầu                                 |
| `attribute_data`         | json      | Dữ liệu gốc từ API                                |
| `last_synced_at`         | timestamp | Thời gian sync cuối                               |
| `created_at`             | timestamp | Thời gian tạo                                     |
| `updated_at`             | timestamp | Thời gian cập nhật                                |

## 🛠️ Cài đặt

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Đăng ký Command (nếu cần)

Thêm vào `app/Console/Kernel.php`:

```php
protected $commands = [
    \App\Console\Commands\SyncTikTokCategoryAttributes::class,
];
```

## 📖 Sử dụng

### Command Line

#### Sync attributes cho một category cụ thể:

```bash
# Sync category 600001
php artisan tiktok:sync-category-attributes 600001

# Force sync (bỏ qua kiểm tra thời gian)
php artisan tiktok:sync-category-attributes 600001 --force

# Sync với locale khác
php artisan tiktok:sync-category-attributes 600001 --locale=vi-VN

# Kiểm tra sync trong 12 giờ qua
php artisan tiktok:sync-category-attributes 600001 --hours=12
```

#### Sync tất cả leaf categories:

```bash
# Sync tất cả
php artisan tiktok:sync-category-attributes

# Force sync tất cả
php artisan tiktok:sync-category-attributes --force
```

### Web Interface

#### Truy cập giao diện:

```
http://your-domain/tik-tok-category-attributes
```

#### Các chức năng:

1. **Chọn Category**: Dropdown để chọn category cần xem attributes
2. **Sync Attributes**: Nút để sync attributes từ TikTok Shop API
3. **Force Sync**: Nút để force sync (bỏ qua kiểm tra thời gian)
4. **Xem Attributes**: Hiển thị danh sách attributes với thông tin chi tiết
5. **Phân loại**: Attributes được phân loại theo Required/Optional và Product/Sales Properties

### API Endpoints

#### Lấy attributes của category:

```http
GET /tik-tok-category-attributes/api/attributes?category_id=600001
```

Response:

```json
{
    "success": true,
    "data": {
        "attributes": [...],
        "grouped": {
            "required": [...],
            "optional": [...],
            "product_properties": [...],
            "sales_properties": [...]
        },
        "stats": {
            "total": 15,
            "required": 8,
            "optional": 7,
            "product_properties": 12,
            "sales_properties": 3
        }
    }
}
```

#### Lấy values của attribute:

```http
GET /tik-tok-category-attributes/api/values?attribute_id=123
```

#### Kiểm tra trạng thái sync:

```http
GET /tik-tok-category-attributes/api/check-sync-status?category_id=600001
```

## 🔧 Model Methods

### TikTokCategoryAttribute

#### Scopes:

```php
// Lọc theo loại
TikTokCategoryAttribute::ofType('PRODUCT_PROPERTY')->get();

// Lọc required/optional
TikTokCategoryAttribute::required()->get();
TikTokCategoryAttribute::optional()->get();

// Lọc multiple selection
TikTokCategoryAttribute::multipleSelection()->get();

// Lọc customizable
TikTokCategoryAttribute::customizable()->get();
```

#### Static Methods:

```php
// Kiểm tra cần sync không
TikTokCategoryAttribute::needsSync('600001', 24);

// Xóa attributes của category
TikTokCategoryAttribute::clearCategoryAttributes('600001');

// Tạo từ API data
TikTokCategoryAttribute::createOrUpdateFromApiData('600001', $apiData);

// Lấy với phân loại
TikTokCategoryAttribute::getByCategoryWithGrouping('600001');
```

#### Accessors:

```php
// Lấy danh sách values đơn giản
$attribute->values_list; // ['id' => 'name', ...]
```

## 🔄 Workflow

### 1. Sync Categories trước

```bash
php artisan tiktok:sync-categories
```

### 2. Sync Attributes cho categories cần thiết

```bash
# Sync một category
php artisan tiktok:sync-category-attributes 600001

# Hoặc sync tất cả
php artisan tiktok:sync-category-attributes
```

### 3. Sử dụng trong code

```php
// Lấy attributes của category
$attributes = TikTokCategoryAttribute::where('category_id', '600001')->get();

// Lấy required attributes
$required = TikTokCategoryAttribute::where('category_id', '600001')
    ->required()
    ->get();

// Lấy product properties
$productProps = TikTokCategoryAttribute::where('category_id', '600001')
    ->ofType('PRODUCT_PROPERTY')
    ->get();
```

## 📊 Monitoring

### Logs

Hệ thống log chi tiết các hoạt động:

-   `SyncTikTokCategoryAttributes`: Log quá trình sync
-   `TikTok Shop API Error`: Log lỗi API
-   `Category attributes synced successfully`: Log thành công

### Metrics

Theo dõi các metrics:

-   Số lượng attributes per category
-   Thời gian sync
-   Tỷ lệ success/error
-   Performance của API calls

## 🚨 Lưu ý quan trọng

1. **Leaf Categories Only**: Chỉ có thể sync attributes cho leaf categories (categories cuối cùng)
2. **API Rate Limits**: TikTok Shop API có giới hạn rate, không sync quá thường xuyên
3. **Timestamp Requirements**: Timestamp phải nằm trong vòng ±5 phút so với server time
4. **Access Token**: Cần có access token hợp lệ để gọi API
5. **Shop Cipher**: Cần có shop cipher để xác định shop

## 🔧 Troubleshooting

### Lỗi thường gặp:

1. **"Category is not a leaf category"**

    - Giải pháp: Chỉ sync leaf categories

2. **"Access token không hợp lệ"**

    - Giải pháp: Refresh access token hoặc reconnect TikTok Shop

3. **"Timestamp out of range"**

    - Giải pháp: Kiểm tra server time, đảm bảo đồng bộ với UTC

4. **"No shop found"**
    - Giải pháp: Kết nối shop trước khi sync

### Debug Commands:

```bash
# Kiểm tra categories
php artisan tiktok:sync-categories --force

# Test API connection
php artisan tiktok:sync-category-attributes 600001 --force

# Check logs
tail -f storage/logs/laravel.log
```

## 📈 Performance Tips

1. **Batch Sync**: Sync nhiều categories cùng lúc thay vì từng cái một
2. **Caching**: Sử dụng cache cho attributes thường xuyên truy cập
3. **Background Jobs**: Sử dụng queue cho sync operations lớn
4. **Selective Sync**: Chỉ sync categories cần thiết

## 🔗 Related Files

-   `app/Models/TikTokCategoryAttribute.php`
-   `app/Http/Controllers/TikTokCategoryAttributeController.php`
-   `app/Console/Commands/SyncTikTokCategoryAttributes.php`
-   `app/Services/TikTokShopService.php`
-   `database/migrations/2024_01_01_000001_create_tik_tok_category_attributes_table.php`
-   `resources/views/tik-tok-category-attributes/`
