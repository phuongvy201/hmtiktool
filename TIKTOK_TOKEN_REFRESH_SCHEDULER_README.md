# TikTok Shop Token Refresh Scheduler

## Tổng quan

Hệ thống tự động refresh TikTok Shop access tokens để đảm bảo API calls luôn hoạt động. Access token có thời hạn 7 ngày và cần được refresh trước khi hết hạn.

## Cấu hình

### 1. Scheduled Job

Job được cấu hình trong `bootstrap/app.php`:

```php
->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule): void {
    // Refresh TikTok Shop tokens hàng ngày lúc 2:00 AM
    $schedule->command('tiktok:refresh-tokens')
        ->dailyAt('02:00')
        ->withoutOverlapping()
        ->runInBackground()
        ->appendOutputTo(storage_path('logs/tiktok-token-refresh.log'));
})
```

**Thời gian chạy**: Hàng ngày lúc 2:00 AM
**Log file**: `storage/logs/tiktok-token-refresh.log`

### 2. Điều kiện refresh

Token sẽ được refresh khi:

-   Integration có status = 'active'
-   Có access_token và refresh_token
-   Refresh token chưa hết hạn
-   Access token hết hạn trong vòng 24 giờ tới

## Sử dụng Command

### Chạy thủ công

```bash
# Refresh tất cả tokens cần thiết
php artisan tiktok:refresh-tokens

# Chỉ refresh token của team cụ thể
php artisan tiktok:refresh-tokens --team-id=1

# Bắt buộc refresh tất cả tokens (bỏ qua điều kiện 24h)
php artisan tiktok:refresh-tokens --force

# Chế độ dry-run (chỉ xem, không thực hiện)
php artisan tiktok:refresh-tokens --dry-run
```

### Kết quả output

```
🔄 Bắt đầu refresh TikTok Shop tokens...
📊 Tìm thấy 3 integration(s)

📋 Chi tiết kết quả:
   ✅ Integration 1 (Team 1): Refresh thành công - Hết hạn: 15/01/2025 14:30:25
   ⏭️  Integration 2 (Team 2): Bỏ qua - Token còn 48.5 giờ mới hết hạn
   ❌ Integration 3 (Team 3): Lỗi - Refresh token đã hết hạn

📈 Kết quả tổng kết:
   ✅ Đã refresh: 1
   ⏭️  Đã bỏ qua: 1
   ❌ Lỗi: 1
```

## API Refresh Token

### Endpoint

```
POST https://open-api.tiktok-shops.com/api/v2/token/refresh
```

### Request Body

```json
{
    "app_key": "your_app_key",
    "app_secret": "your_app_secret",
    "refresh_token": "current_refresh_token",
    "grant_type": "refresh_token"
}
```

### Response

```json
{
    "code": 0,
    "message": "success",
    "data": {
        "access_token": "TTP_Fw8rBwAAAAAkW03F...",
        "access_token_expire_in": 1660556783,
        "refresh_token": "TTP_NTUxZTNhYTQ2ZD...",
        "refresh_token_expire_in": 1691487031,
        "open_id": "7010736057180325637",
        "seller_name": "Jjj test shop",
        "seller_base_region": "ID",
        "user_type": 0
    },
    "request_id": "2022080809462301024509910319695C45"
}
```

## Monitoring

### 1. Log Files

-   **Laravel Log**: `storage/logs/laravel.log`
-   **Token Refresh Log**: `storage/logs/tiktok-token-refresh.log`

### 2. Database

Kiểm tra trạng thái tokens trong bảng `tiktok_shop_integrations`:

```sql
SELECT
    id,
    team_id,
    status,
    access_token_expires_at,
    refresh_token_expires_at,
    error_message,
    created_at,
    updated_at
FROM tiktok_shop_integrations
WHERE status = 'active';
```

### 3. Model Methods

```php
$integration = TikTokShopIntegration::find(1);

// Kiểm tra token có cần refresh không
$needsRefresh = $integration->needsTokenRefresh();

// Lấy số giờ còn lại trước khi hết hạn
$hoursLeft = $integration->getHoursUntilExpiry();

// Refresh token thủ công
$result = $integration->refreshAccessToken();
```

## Troubleshooting

### 1. Token không được refresh

**Nguyên nhân có thể:**

-   Refresh token đã hết hạn
-   App key/secret không đúng
-   Network issues
-   TikTok API rate limiting

**Giải pháp:**

-   Kiểm tra log files
-   Verify app credentials
-   Re-authorize integration

### 2. Scheduled job không chạy

**Kiểm tra:**

```bash
# Xem scheduled tasks
php artisan schedule:list

# Test schedule
php artisan schedule:test

# Chạy schedule thủ công
php artisan schedule:run
```

### 3. Cron Job Setup

Đảm bảo cron job được cấu hình:

```bash
# Thêm vào crontab
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

## Security

-   Access token và refresh token được lưu trong database
-   Tokens được ẩn khỏi serialization (`$hidden` property)
-   Log files không chứa token values
-   API calls sử dụng HTTPS

## Performance

-   Job chạy background để không block main process
-   `withoutOverlapping()` ngăn multiple instances
-   Timeout 30 giây cho mỗi API call
-   Batch processing cho multiple integrations
