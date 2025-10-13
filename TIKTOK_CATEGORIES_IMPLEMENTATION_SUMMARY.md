# TikTok Shop Categories Implementation Summary

## Tổng quan

Đã cập nhật hệ thống TikTok Shop Categories để sử dụng **chung cho toàn hệ thống** thay vì riêng cho từng team.

## Những thay đổi chính

### 1. Database Schema

-   **Loại bỏ cột `team_id`** khỏi bảng `tiktok_shop_categories`
-   **Thêm unique constraint** cho `category_id`
-   **Cập nhật indexes** cho hiệu suất tốt hơn

### 2. Model TikTokShopCategory

-   **Loại bỏ relationship với Team**
-   **Cập nhật methods**:
    -   `getCategoriesArray()` - không cần team_id
    -   `needsSystemSync()` - thay cho `needsSync()`
    -   **Loại bỏ scope `byTeam()`**

### 3. Command SyncTikTokCategories

-   **Loại bỏ option `--team-id`**
-   **Sync cho toàn hệ thống** thay vì từng team
-   **Sử dụng integration đầu tiên** làm mẫu
-   **Xóa tất cả categories cũ** trước khi sync mới

### 4. Service TikTokShopService

-   **Cập nhật `getCachedCategories()`** - không cần team_id
-   **Cập nhật `ensureCategoriesSynced()`** - sử dụng system sync

### 5. Routes và Controllers

-   **Cập nhật các file sử dụng `byTeam()`**
-   **Loại bỏ tham số team_id** trong các method calls

## Cấu trúc Database mới

```sql
CREATE TABLE tiktok_shop_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id VARCHAR(255) UNIQUE NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    parent_category_id VARCHAR(255) NULL,
    level INT DEFAULT 1,
    is_leaf BOOLEAN DEFAULT FALSE,
    metadata JSON NULL,
    last_synced_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX category_id,
    INDEX parent_category_id,
    INDEX is_leaf
);
```

## Cách sử dụng

### 1. Sync Categories

```bash
# Sync bình thường
php artisan tiktok:sync-categories

# Force sync
php artisan tiktok:sync-categories --force

# Sync với threshold khác
php artisan tiktok:sync-categories --hours=12
```

### 2. Trong Code

```php
use App\Models\TikTokShopCategory;

// Lấy tất cả categories
$categories = TikTokShopCategory::all();

// Lấy leaf categories (có thể tạo sản phẩm)
$leafCategories = TikTokShopCategory::leafCategories()->get();

// Lấy categories cho dropdown
$categoriesArray = TikTokShopCategory::getCategoriesArray();

// Kiểm tra cần sync
$needsSync = TikTokShopCategory::needsSystemSync(24);
```

### 3. Relationships

```php
$category = TikTokShopCategory::first();

// Lấy parent category
$parent = $category->parent;

// Lấy child categories
$children = $category->children;
```

## Lợi ích

1. **Đơn giản hóa**: Categories chung cho toàn hệ thống
2. **Hiệu suất tốt hơn**: Không cần query theo team
3. **Dễ bảo trì**: Một nguồn dữ liệu duy nhất
4. **Tiết kiệm storage**: Không duplicate categories cho từng team
5. **Đồng bộ dễ dàng**: Chỉ cần sync một lần cho toàn hệ thống

## Migration Files

1. `2025_08_22_163500_create_tiktok_shop_categories_table.php` - Tạo bảng mới
2. `2025_08_22_171148_remove_team_id_from_tiktok_shop_categories_table.php` - Xóa team_id (backup)

## Test Files

1. `test_tiktok_categories_system.php` - Test toàn bộ hệ thống
2. `TIKTOK_CATEGORIES_SYNC_README.md` - Hướng dẫn chi tiết

## Lưu ý quan trọng

-   **Categories giờ là global** - tất cả teams sử dụng chung
-   **Cần có ít nhất 1 TikTok Shop integration** để sync
-   **Sync sẽ xóa tất cả categories cũ** và thay thế bằng mới
-   **Backup dữ liệu** trước khi chạy migration nếu cần

## API Endpoint

**Get Categories** - `/product/202309/get_categories`

-   Lấy danh sách categories từ TikTok Shop API
-   Categories được cập nhật thường xuyên
-   Nên gọi real-time để đảm bảo dữ liệu mới nhất

