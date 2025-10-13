# TikTok Shop Categories Sync

## Tổng quan

Command `SyncTikTokCategories` được sử dụng để đồng bộ danh sách categories từ TikTok Shop API vào database của hệ thống. **Categories được sử dụng chung cho toàn hệ thống, không phụ thuộc vào team cụ thể nào.** Categories được cập nhật thường xuyên từ TikTok, vì vậy việc đồng bộ định kỳ là cần thiết để đảm bảo dữ liệu luôn mới nhất.

## API Endpoint

**Get Categories** - `/product/202309/get_categories`

-   **Mô tả**: Lấy danh sách categories sản phẩm có sẵn cho shop
-   **Lưu ý**: Categories được cập nhật thường xuyên, nên gọi API real-time để đảm bảo dữ liệu mới nhất
-   **Thị trường Indonesia**: Để list sản phẩm trên cả TikTok Shop và Tokopedia, phải sử dụng categories có sẵn trên cả 2 platform

## Cách sử dụng Command

### 1. Sync categories cho toàn hệ thống

```bash
php artisan tiktok:sync-categories
```

### 2. Force sync (bỏ qua kiểm tra thời gian)

```bash
php artisan tiktok:sync-categories --force
```

### 3. Thay đổi threshold thời gian (mặc định 24 giờ)

```bash
php artisan tiktok:sync-categories --hours=12
```

### 4. Kết hợp các options

```bash
php artisan tiktok:sync-categories --force --hours=6
```

## Các Options

-   `--force`: Bắt buộc sync ngay cả khi không cần thiết (bỏ qua kiểm tra thời gian)
-   `--hours`: Số giờ threshold để kiểm tra xem có cần sync không (mặc định 24 giờ)

## Cấu trúc Database

Bảng `tiktok_shop_categories` lưu trữ:

-   `category_id`: ID category từ TikTok (unique)
-   `category_name`: Tên category
-   `parent_category_id`: ID category cha (nếu có)
-   `level`: Cấp độ của category (1, 2, 3...)
-   `is_leaf`: Có phải category cuối cùng không (có thể tạo sản phẩm)
-   `metadata`: Thông tin bổ sung từ API (JSON)
-   `last_synced_at`: Thời gian sync cuối cùng

## Logic xử lý

### 1. Kiểm tra cần thiết sync

-   Kiểm tra thời gian sync cuối cùng
-   Nếu chưa đủ số giờ threshold → bỏ qua (trừ khi dùng --force)

### 2. Lấy categories từ API

-   Gọi TikTok Shop API `/product/202309/get_categories`
-   Xử lý authentication và refresh token nếu cần
-   Parse response data

### 3. Lưu vào database

-   Xóa categories cũ của team
-   Lưu categories mới với thông tin chi tiết
-   Cập nhật `last_synced_at`

### 4. Xử lý cấu trúc categories

-   Hỗ trợ cả raw data và formatted data từ API
-   Parse parent-child relationships
-   Xác định leaf categories

## Lập lịch tự động

Để sync tự động, thêm vào `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync categories mỗi 6 giờ
    $schedule->command('tiktok:sync-categories')->everyFourHours();

    // Hoặc sync hàng ngày lúc 2:00 AM
    $schedule->command('tiktok:sync-categories')->dailyAt('02:00');
}
```

## Monitoring và Logging

Command sẽ log các thông tin:

-   Thời gian bắt đầu/kết thúc sync
-   Số lượng categories được lưu
-   Lỗi nếu có
-   Thông tin chi tiết cho từng team

Logs được lưu trong `storage/logs/laravel.log`

## Troubleshooting

### Lỗi thường gặp:

1. **Access token expired**

    - Command sẽ tự động refresh token
    - Nếu không được, cần reconnect TikTok Shop integration

2. **API rate limit**

    - TikTok có giới hạn số request
    - Command có retry logic

3. **Network issues**
    - Kiểm tra kết nối internet
    - Kiểm tra firewall/proxy

### Debug:

```bash
# Chạy với verbose output
php artisan tiktok:sync-categories --verbose

# Xem logs
tail -f storage/logs/laravel.log | grep "TikTok"
```

## Model Methods

### TikTokShopCategory

```php
// Lấy tất cả categories
TikTokShopCategory::all();

// Lấy leaf categories (có thể tạo sản phẩm)
TikTokShopCategory::leafCategories()->get();

// Lấy root categories
TikTokShopCategory::rootCategories()->get();

// Lấy dạng array cho select dropdown
TikTokShopCategory::getCategoriesArray();

// Kiểm tra cần sync
TikTokShopCategory::needsSystemSync($hours);
```

## Ví dụ sử dụng trong code

```php
use App\Models\TikTokShopCategory;

// Lấy categories cho dropdown
$categories = TikTokShopCategory::getCategoriesArray();

// Lấy category cụ thể
$category = TikTokShopCategory::where('category_id', '100001')->first();

// Lấy child categories
$children = $category->children;

// Lấy parent category
$parent = $category->parent;
```
