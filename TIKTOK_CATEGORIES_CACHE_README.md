# TikTok Shop Categories Cache System

## Tổng quan

Hệ thống cache categories từ TikTok Shop API vào database để:

-   Tránh rate limit từ TikTok API
-   Tăng tốc độ load categories
-   Đồng bộ categories theo team
-   Tự động sync khi cần thiết

## Cấu trúc Database

### Bảng `tiktok_shop_categories`

```sql
CREATE TABLE tiktok_shop_categories (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    team_id BIGINT NOT NULL,
    category_id VARCHAR(255) NOT NULL,
    category_name VARCHAR(255) NOT NULL,
    parent_category_id VARCHAR(255) NULL,
    level INT DEFAULT 1,
    is_leaf BOOLEAN DEFAULT FALSE,
    metadata JSON NULL,
    last_synced_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    UNIQUE KEY unique_team_category (team_id, category_id),
    INDEX idx_team_parent (team_id, parent_category_id),
    INDEX idx_team_leaf (team_id, is_leaf)
);
```

## Các thành phần chính

### 1. Model `TikTokShopCategory`

**Location**: `app/Models/TikTokShopCategory.php`

**Features**:

-   Relationships với Team, Parent/Child categories
-   Scopes: `byTeam()`, `leafCategories()`, `rootCategories()`
-   Methods: `getCategoriesArray()`, `needsSync()`

**Usage**:

```php
// Lấy categories của team
$categories = TikTokShopCategory::byTeam($teamId)->get();

// Lấy leaf categories (có thể tạo sản phẩm)
$leafCategories = TikTokShopCategory::byTeam($teamId)->leafCategories()->get();

// Lấy dạng array cho select dropdown
$categoriesArray = TikTokShopCategory::getCategoriesArray($teamId);

// Kiểm tra cần sync không
$needsSync = TikTokShopCategory::needsSync($teamId, 24); // 24 giờ
```

### 2. Command `SyncTikTokCategories`

**Location**: `app/Console/Commands/SyncTikTokCategories.php`

**Usage**:

```bash
# Sync tất cả teams
php artisan tiktok:sync-categories

# Sync team cụ thể
php artisan tiktok:sync-categories --team-id=1

# Force sync (bỏ qua kiểm tra thời gian)
php artisan tiktok:sync-categories --force

# Thay đổi threshold thời gian (giờ)
php artisan tiktok:sync-categories --hours=12
```

**Features**:

-   Sync categories từ TikTok Shop API
-   Parse cấu trúc categories (parent/child, level, leaf)
-   Xóa categories cũ trước khi sync mới
-   Logging chi tiết
-   Error handling

### 3. Service `TikTokShopService`

**Location**: `app/Services/TikTokShopService.php`

**Methods mới**:

-   `getCachedCategories($teamId)`: Lấy categories từ cache
-   `ensureCategoriesSynced($integration)`: Kiểm tra và sync nếu cần

**Logic cache**:

1. Kiểm tra cache trong database trước
2. Nếu có cache và chưa hết hạn → sử dụng cache
3. Nếu không có cache hoặc hết hạn → gọi API và lưu cache

## Cách sử dụng

### 1. Sync Categories

```bash
# Sync lần đầu
php artisan tiktok:sync-categories --team-id=1 --force

# Sync định kỳ (có thể setup cron job)
php artisan tiktok:sync-categories
```

### 2. Trong Controller

```php
// ProductTemplateController
public function getCategories()
{
    $user = Auth::user();
    $team = $user->team;
    $integration = $team->tiktokShopIntegration;

    if ($integration) {
        // Sử dụng cache system
        $categories = $this->tikTokService->getCategoriesWithFallback($integration);
    } else {
        // Fallback to default categories
        $categories = $this->tikTokService->getCategoriesWithFallback();
    }

    return response()->json($categories);
}
```

### 3. Test Routes

```php
// Test sync categories
GET /test/tiktok-categories-sync

// Test categories API
GET /test/tiktok-categories
```

## Cron Job Setup

Để tự động sync categories định kỳ, thêm vào crontab:

```bash
# Sync categories mỗi 6 giờ
0 */6 * * * cd /path/to/project && php artisan tiktok:sync-categories

# Hoặc sync hàng ngày lúc 2:00 AM
0 2 * * * cd /path/to/project && php artisan tiktok:sync-categories
```

## Monitoring

### Logs

Hệ thống log chi tiết các hoạt động:

```php
// Log khi sử dụng cache
Log::info('Using cached categories from database', [
    'team_id' => $teamId,
    'count' => count($categories),
    'source' => 'database_cache'
]);

// Log khi sync thành công
Log::info('TikTok categories synced successfully', [
    'team_id' => $team->id,
    'categories_count' => $savedCount,
    'synced_at' => $now->toISOString()
]);
```

### Database Queries

```sql
-- Kiểm tra categories của team
SELECT * FROM tiktok_shop_categories WHERE team_id = 1;

-- Kiểm tra thời gian sync cuối
SELECT MAX(last_synced_at) FROM tiktok_shop_categories WHERE team_id = 1;

-- Đếm categories
SELECT COUNT(*) FROM tiktok_shop_categories WHERE team_id = 1 AND is_leaf = 1;
```

## Troubleshooting

### 1. Categories không sync được

```bash
# Kiểm tra log
tail -f storage/logs/laravel.log | grep "TikTok categories"

# Test sync manually
php artisan tiktok:sync-categories --team-id=1 --force
```

### 2. Cache không hoạt động

```php
// Kiểm tra database
$categories = TikTokShopCategory::byTeam($teamId)->get();
dd($categories);

// Clear cache và thử lại
php artisan optimize:clear
```

### 3. API Rate Limit

-   Hệ thống tự động sử dụng cache khi có thể
-   Có thể tăng threshold thời gian sync: `--hours=48`
-   Monitor logs để phát hiện rate limit errors

## Performance

### Benefits

1. **Tốc độ**: Categories load từ database nhanh hơn API
2. **Reliability**: Không phụ thuộc vào TikTok API availability
3. **Rate Limit**: Giảm số lần gọi API
4. **Offline**: Có thể hoạt động khi TikTok API down

### Optimization

1. **Indexes**: Đã tạo indexes cho các queries phổ biến
2. **Scopes**: Sử dụng Eloquent scopes để tối ưu queries
3. **Caching**: Laravel cache có thể được thêm nếu cần

## Future Improvements

1. **Background Jobs**: Sử dụng queues cho sync
2. **Incremental Sync**: Chỉ sync categories thay đổi
3. **Multi-region**: Support nhiều region TikTok Shop
4. **Webhooks**: Sync real-time khi TikTok update categories
