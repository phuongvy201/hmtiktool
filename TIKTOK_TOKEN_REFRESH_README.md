# TikTok Shop Token Refresh System

## Tổng quan

Hệ thống tự động refresh access token cho TikTok Shop API khi token sắp hết hạn (7 ngày). Hệ thống bao gồm:

-   **Tự động refresh**: Chạy hàng ngày lúc 2:00 AM
-   **Manual refresh**: Có thể chạy thủ công khi cần
-   **Kiểm tra trạng thái**: Theo dõi tình trạng token
-   **Error handling**: Xử lý lỗi và logging chi tiết

## Cấu trúc hệ thống

### 1. Models

-   `TikTokShopIntegration`: Model chính chứa thông tin token
    -   `isAccessTokenExpired()`: Kiểm tra access token hết hạn
    -   `isRefreshTokenExpired()`: Kiểm tra refresh token hết hạn
    -   `needsTokenRefresh()`: Kiểm tra cần refresh
    -   `canRefreshToken()`: Kiểm tra có thể refresh
    -   `token_status`: Thông tin chi tiết về trạng thái token

### 2. Services

-   `TikTokShopService`: Service chính xử lý API calls

    -   `ensureValidAccessToken()`: Đảm bảo token hợp lệ trước khi gọi API
    -   `refreshAccessToken()`: Refresh token từ TikTok API

-   `TikTokTokenManager`: Service quản lý token
    -   `refreshToken()`: Refresh token cho một integration
    -   `refreshAllTokens()`: Refresh tất cả tokens
    -   `getTokenHealthStatus()`: Lấy trạng thái sức khỏe tokens
    -   `cleanupExpiredIntegrations()`: Dọn dẹp integrations hết hạn

### 3. Commands

-   `tiktok:refresh-tokens`: Refresh tokens
-   `tiktok:check-token-status`: Kiểm tra trạng thái tokens

### 4. Scheduled Tasks

-   Chạy hàng ngày lúc 2:00 AM để refresh tokens

## Cách sử dụng

### 1. Refresh tokens thủ công

```bash
# Refresh tất cả tokens
php artisan tiktok:refresh-tokens

# Refresh cho team cụ thể
php artisan tiktok:refresh-tokens --team-id=1

# Bắt buộc refresh tất cả (kể cả token còn hạn)
php artisan tiktok:refresh-tokens --force

# Chế độ dry-run (chỉ xem, không thực hiện)
php artisan tiktok:refresh-tokens --dry-run
```

### 2. Kiểm tra trạng thái tokens

```bash
# Xem tổng quan
php artisan tiktok:check-token-status

# Xem chi tiết dạng bảng
php artisan tiktok:check-token-status --format=table

# Xem tóm tắt
php artisan tiktok:check-token-status --format=summary

# Xem JSON
php artisan tiktok:check-token-status --format=json

# Kiểm tra team cụ thể
php artisan tiktok:check-token-status --team-id=1
```

### 3. Trong code

```php
use App\Services\TikTokTokenManager;
use App\Services\TikTokShopService;

// Refresh token cho một integration
$tokenManager = new TikTokTokenManager(new TikTokShopService());
$result = $tokenManager->refreshToken($integration);

// Refresh tất cả tokens
$result = $tokenManager->refreshAllTokens([
    'team_id' => 1, // optional
    'force' => false, // optional
    'dry_run' => false // optional
]);

// Kiểm tra trạng thái sức khỏe
$healthStatus = $tokenManager->getTokenHealthStatus();
```

## Cơ chế hoạt động

### 1. Tự động refresh

-   Chạy hàng ngày lúc 2:00 AM
-   Kiểm tra tất cả integrations có status = 'active'
-   Refresh token nếu:
    -   Access token hết hạn hoặc sắp hết hạn (trong 5 phút)
    -   Refresh token còn hạn
    -   Integration đang active

### 2. Manual refresh

-   Có thể chạy bất kỳ lúc nào
-   Hỗ trợ các tùy chọn:
    -   `--team-id`: Chỉ refresh cho team cụ thể
    -   `--force`: Bắt buộc refresh tất cả
    -   `--dry-run`: Chỉ xem, không thực hiện

### 3. Error handling

-   Logging chi tiết tất cả hoạt động
-   Xử lý lỗi API từ TikTok
-   Đánh dấu integration lỗi khi không thể refresh
-   Cache lock để tránh refresh đồng thời

## Các trạng thái token

### Access Token

-   **OK**: Token còn hạn và không sắp hết hạn
-   **Sắp hết hạn**: Token sẽ hết hạn trong 30 phút
-   **Hết hạn**: Token đã hết hạn hoặc sắp hết hạn trong 5 phút

### Refresh Token

-   **OK**: Token còn hạn
-   **Hết hạn**: Token đã hết hạn, cần kết nối lại

### Integration Status

-   **Healthy**: Token khỏe mạnh, không cần refresh
-   **Expiring Soon**: Token sắp hết hạn
-   **Expired**: Token đã hết hạn
-   **Needs Refresh**: Cần refresh token
-   **Cannot Refresh**: Không thể refresh (refresh token hết hạn)

## Logging

Tất cả hoạt động được log với các mức độ:

-   **INFO**: Hoạt động bình thường
-   **WARNING**: Cảnh báo (token sắp hết hạn)
-   **ERROR**: Lỗi (không thể refresh token)

Log được lưu trong `storage/logs/laravel.log` với format:

```
[timestamp] local.INFO: TikTok token refresh completed successfully {"integration_id":1,"team_id":1,"new_expires_at":1234567890}
```

## Troubleshooting

### 1. Token không thể refresh

-   Kiểm tra refresh token có hết hạn không
-   Kiểm tra App Key và App Secret có đúng không
-   Kiểm tra log để xem lỗi chi tiết

### 2. Scheduled task không chạy

-   Đảm bảo cron job đã được cấu hình:
    ```bash
    * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
    ```
-   Kiểm tra log để xem có lỗi không

### 3. API calls bị lỗi 401

-   Token có thể đã hết hạn
-   Chạy refresh token thủ công
-   Kiểm tra trạng thái token

## Cấu hình

### Environment Variables

```env
TIKTOK_SHOP_APP_KEY=your_app_key
TIKTOK_SHOP_APP_SECRET=your_app_secret
```

### Scheduled Tasks

Được cấu hình trong `routes/console.php`:

```php
Schedule::command('tiktok:refresh-tokens')
    ->daily()
    ->at('02:00')
    ->name('refresh-tiktok-tokens')
    ->description('Refresh TikTok Shop access tokens daily at 2 AM')
    ->withoutOverlapping()
    ->runInBackground();
```

## Monitoring

### 1. Kiểm tra trạng thái hàng ngày

```bash
php artisan tiktok:check-token-status --format=summary
```

### 2. Xem log

```bash
tail -f storage/logs/laravel.log | grep "TikTok"
```

### 3. Test refresh thủ công

```bash
php artisan tiktok:refresh-tokens --dry-run
```

## Best Practices

1. **Kiểm tra trạng thái thường xuyên**: Chạy `tiktok:check-token-status` hàng ngày
2. **Monitor logs**: Theo dõi log để phát hiện lỗi sớm
3. **Backup tokens**: Lưu trữ thông tin token an toàn
4. **Test trước khi deploy**: Sử dụng `--dry-run` để test
5. **Handle errors gracefully**: Xử lý lỗi một cách thân thiện với user

## API Integration

Khi gọi TikTok Shop API, hệ thống sẽ tự động:

1. Kiểm tra access token có hết hạn không
2. Nếu hết hạn, tự động refresh token
3. Thử lại API call với token mới
4. Nếu refresh thất bại, trả về lỗi

```php
// Tự động refresh token nếu cần
$result = $tiktokService->getAuthorizedShops($integration);
```

