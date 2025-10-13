# TikTok Orders Scheduling Guide

## Tổng quan

Hệ thống scheduling TikTok orders đã được thiết lập để tự động đồng bộ đơn hàng từ TikTok Shop API theo các lịch trình khác nhau.

## Các Command đã tạo

### 1. `tiktok:sync-orders`

Sync đơn hàng trực tiếp từ TikTok API.

**Cách sử dụng:**

```bash
# Sync tất cả shops trong 24h gần đây
php artisan tiktok:sync-orders

# Sync shop cụ thể
php artisan tiktok:sync-orders --shop=12

# Sync đơn hàng theo trạng thái
php artisan tiktok:sync-orders --status=AWAITING_SHIPMENT --hours=48

# Sync đơn hàng mới trong 1 giờ gần đây
php artisan tiktok:sync-orders --hours=1

# Dry run (chỉ hiển thị thông tin, không sync thật)
php artisan tiktok:sync-orders --dry-run

# Bỏ qua kiểm tra integration status
php artisan tiktok:sync-orders --force
```

### 2. `tiktok:dispatch-sync-jobs`

Dispatch jobs để sync orders cho nhiều shops song song.

**Cách sử dụng:**

```bash
# Dispatch jobs cho tất cả shops
php artisan tiktok:dispatch-sync-jobs

# Dispatch jobs ưu tiên cao
php artisan tiktok:dispatch-sync-jobs --priority

# Dispatch với batch size nhỏ
php artisan tiktok:dispatch-sync-jobs --batch-size=3

# Dispatch với delay giữa các jobs
php artisan tiktok:dispatch-sync-jobs --delay=5
```

### 3. `tiktok:monitor-sync`

Monitor trạng thái sync và cảnh báo nếu có vấn đề.

**Cách sử dụng:**

```bash
# Monitor tất cả shops
php artisan tiktok:monitor-sync

# Monitor shop cụ thể
php artisan tiktok:monitor-sync --shop=12

# Monitor với cảnh báo
php artisan tiktok:monitor-sync --send-alerts --alert-threshold=2

# Monitor trong 48h gần đây
php artisan tiktok:monitor-sync --hours=48
```

## Lịch trình tự động

### 1. Sync Orders thường xuyên

-   **Mỗi 30 phút**: Sync orders trong 24h gần đây
-   **Mỗi 10 phút**: Sync orders mới (1h gần đây)
-   **Mỗi 15 phút**: Sync orders đang chờ ship (48h gần đây)
-   **Mỗi 20 phút**: Sync orders đang vận chuyển (72h gần đây)

### 2. Sync toàn bộ

-   **Hàng ngày 3:00 AM**: Full sync tất cả orders (7 ngày gần đây)

### 3. Monitoring

-   **Mỗi 2 giờ**: Monitor sync status và gửi cảnh báo
-   **Mỗi 5 phút**: Dispatch high priority jobs

### 4. Token Refresh

-   **Hàng ngày 2:00 AM**: Refresh TikTok tokens

## Log Files

Tất cả logs được lưu trong `storage/logs/`:

-   `tiktok-orders-sync.log` - Sync orders thường xuyên
-   `tiktok-orders-sync-recent.log` - Sync orders mới
-   `tiktok-orders-awaiting-shipment.log` - Sync orders chờ ship
-   `tiktok-orders-in-transit.log` - Sync orders đang vận chuyển
-   `tiktok-orders-full-sync.log` - Full sync
-   `tiktok-sync-monitoring.log` - Monitoring logs
-   `tiktok-dispatch-jobs.log` - Job dispatch logs
-   `tiktok-token-refresh.log` - Token refresh logs

## Queue Configuration

### Queue Names

-   `default` - Jobs thường
-   `high` - Jobs ưu tiên cao

### Job Settings

-   **Timeout**: 5 phút
-   **Max Tries**: 3 lần
-   **Max Exceptions**: 2 lần

## Monitoring Dashboard

Sử dụng command `tiktok:monitor-sync` để xem:

1. **Thống kê tổng quan**:

    - Tổng số orders
    - Số shops hoạt động
    - Tỷ lệ shops có orders

2. **Thống kê từng shop**:

    - Tổng orders
    - Orders sync gần đây
    - Orders mới
    - Lần sync cuối
    - Trạng thái integration

3. **Cảnh báo**:
    - Không sync quá lâu
    - Integration không hoạt động
    - Có orders mới nhưng chưa sync

## Rate Limiting

Hệ thống có rate limiting để tránh spam TikTok API:

-   **30 giây** delay giữa các lần sync của cùng một shop
-   **2 giây** delay giữa các shops trong batch
-   **1 giây** delay giữa các pages trong pagination

## Troubleshooting

### 1. Command không chạy

```bash
# Kiểm tra Laravel scheduler có chạy không
php artisan schedule:list

# Chạy scheduler manual
php artisan schedule:run

# Kiểm tra cron job
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Jobs không xử lý

```bash
# Chạy queue worker
php artisan queue:work

# Xem queue status
php artisan queue:monitor

# Clear failed jobs
php artisan queue:flush
```

### 3. Integration không hoạt động

```bash
# Refresh tokens
php artisan tiktok:refresh-tokens

# Kiểm tra integration status
php artisan tiktok:monitor-sync --shop=SHOP_ID
```

### 4. Kiểm tra logs

```bash
# Xem logs realtime
tail -f storage/logs/laravel.log

# Xem logs cụ thể
tail -f storage/logs/tiktok-orders-sync.log
```

## Best Practices

1. **Luôn chạy queue worker**:

    ```bash
    php artisan queue:work --daemon
    ```

2. **Monitor logs thường xuyên**:

    ```bash
    php artisan tiktok:monitor-sync --send-alerts
    ```

3. **Test commands trước khi deploy**:

    ```bash
    php artisan tiktok:sync-orders --dry-run
    ```

4. **Backup database** trước khi full sync lớn

5. **Giám sát rate limits** của TikTok API

## Cấu hình Production

### 1. Supervisor Configuration

Tạo file `/etc/supervisor/conf.d/laravel-worker.conf`:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path-to-your-project/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path-to-your-project/storage/logs/worker.log
stopwaitsecs=3600
```

### 2. Cron Job

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### 3. Environment Variables

```env
QUEUE_CONNECTION=database
TIKTOK_WEBHOOK_SECRET=your_webhook_secret
MAIL_ADMIN_EMAIL=admin@yourdomain.com
```

## API Rate Limits

TikTok API có các giới hạn:

-   **100 requests/minute** per shop
-   **1000 requests/hour** per app
-   **10,000 requests/day** per app

Hệ thống đã được thiết kế để tuân thủ các giới hạn này.
