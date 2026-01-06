# Hướng dẫn Setup Laravel Scheduler trên AWS EC2

## 📋 Tổng quan

Laravel Scheduler đã được cấu hình trong `bootstrap/app.php` để tự động chạy các tasks:
- **Refresh TikTok Tokens**: Hàng ngày lúc 2:00 AM
- **Sync TikTok Orders**: Nhiều lần trong ngày (mỗi 10-30 phút)
- **Backup Database**: Hàng ngày lúc 1:00 AM
- **Sync TikTok Finance**: Hàng ngày lúc 3:00 AM

## 🚀 Cách 1: Sử dụng Script Tự động (Khuyến nghị)

### Bước 1: Upload script lên EC2

```bash
# Từ máy local, upload script lên EC2
scp setup-cron.sh ec2-user@your-ec2-ip:/home/ec2-user/hmtiktool/
```

### Bước 2: SSH vào EC2 và chạy script

```bash
# SSH vào EC2
ssh ec2-user@your-ec2-ip

# Di chuyển vào thư mục project
cd /var/www/hmtiktool  # hoặc đường dẫn project của bạn

# Cấp quyền thực thi cho script
chmod +x setup-cron.sh

# Chạy script (có thể cần sudo)
sudo ./setup-cron.sh
```

## 🔧 Cách 2: Setup Thủ Công

### Bước 1: SSH vào EC2

```bash
ssh ec2-user@your-ec2-ip
cd /var/www/hmtiktool  # Đường dẫn project của bạn
```

### Bước 2: Mở crontab

```bash
crontab -e
```

### Bước 3: Thêm dòng sau vào cuối file

```bash
* * * * * cd /var/www/hmtiktool && php artisan schedule:run >> /dev/null 2>&1
```

**Lưu ý:** Thay `/var/www/hmtiktool` bằng đường dẫn thực tế của project trên EC2.

### Bước 4: Lưu và thoát

- Nhấn `Esc`, gõ `:wq` và Enter (nếu dùng vi/vim)
- Hoặc `Ctrl+X`, sau đó `Y` và Enter (nếu dùng nano)

### Bước 5: Kiểm tra crontab

```bash
crontab -l
```

Bạn sẽ thấy dòng vừa thêm.

## ✅ Kiểm tra Setup

### 1. Kiểm tra scheduled tasks

```bash
cd /var/www/hmtiktool
php artisan schedule:list
```

Kết quả sẽ hiển thị tất cả các tasks đã được schedule.

### 2. Test chạy scheduler thủ công

```bash
php artisan schedule:run
```

### 3. Kiểm tra logs

```bash
# Xem log refresh tokens
tail -f storage/logs/tiktok-token-refresh.log

# Xem log sync orders
tail -f storage/logs/tiktok-orders-sync.log

# Xem tất cả logs
ls -lh storage/logs/
```

## 📊 Các Scheduled Tasks

| Task | Tần suất | Thời gian | Log File |
|------|----------|-----------|----------|
| `tiktok:refresh-tokens` | Hàng ngày | 2:00 AM | `tiktok-token-refresh.log` |
| `tiktok:sync-orders` (24h) | Mỗi 30 phút | - | `tiktok-orders-sync.log` |
| `tiktok:sync-orders` (1h) | Mỗi 10 phút | - | `tiktok-orders-sync-recent.log` |
| `tiktok:sync-orders` (AWAITING_SHIPMENT) | Mỗi 15 phút | - | `tiktok-orders-awaiting-shipment.log` |
| `tiktok:sync-orders` (IN_TRANSIT) | Mỗi 20 phút | - | `tiktok-orders-in-transit.log` |
| `tiktok:sync-orders` (full sync) | Hàng ngày | 3:00 AM | `tiktok-orders-full-sync.log` |
| `tiktok:monitor-sync` | Mỗi 2 giờ | - | `tiktok-sync-monitoring.log` |
| `tiktok:dispatch-sync-jobs` | Mỗi 5 phút | - | `tiktok-dispatch-jobs.log` |
| `backup:database` | Hàng ngày | 1:00 AM | - |
| `tiktok:sync-finance` | Hàng ngày | 3:00 AM | - |

## 🔍 Troubleshooting

### 1. Cron không chạy

Kiểm tra cron service:

```bash
# Trên Amazon Linux 2 / CentOS
sudo systemctl status crond
sudo systemctl start crond
sudo systemctl enable crond

# Trên Ubuntu
sudo systemctl status cron
sudo systemctl start cron
sudo systemctl enable cron
```

### 2. Permission errors

```bash
# Đảm bảo storage có quyền ghi
cd /var/www/hmtiktool
sudo chmod -R 775 storage
sudo chown -R ec2-user:www-data storage
```

### 3. PHP path không đúng

Tìm đường dẫn PHP:

```bash
which php
# hoặc
php -v
```

Cập nhật crontab với đường dẫn đầy đủ:

```bash
* * * * * cd /var/www/hmtiktool && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

### 4. Kiểm tra cron logs

```bash
# Xem cron logs trên Amazon Linux 2
sudo tail -f /var/log/cron

# Xem cron logs trên Ubuntu
sudo tail -f /var/log/syslog | grep CRON
```

### 5. Test command thủ công

```bash
cd /var/www/hmtiktool
php artisan tiktok:refresh-tokens --dry-run
```

## 🔐 Security Best Practices

1. **Đảm bảo file permissions đúng:**
   ```bash
   chmod 600 /var/spool/cron/ec2-user  # Chỉ owner có quyền đọc/ghi
   ```

2. **Không hardcode credentials trong crontab:**
   - Sử dụng `.env` file
   - Đảm bảo `.env` có permission 600

3. **Monitor logs thường xuyên:**
   ```bash
   # Setup log rotation
   sudo logrotate -d /etc/logrotate.d/laravel
   ```

## 📝 Notes

- Laravel scheduler chạy mỗi phút (`* * * * *`), nhưng các tasks bên trong sẽ chỉ chạy theo lịch đã định
- Sử dụng `withoutOverlapping()` để tránh chạy đồng thời nhiều instance
- Sử dụng `runInBackground()` để không block scheduler
- Logs được lưu trong `storage/logs/`

## 🆘 Support

Nếu gặp vấn đề, kiểm tra:
1. Laravel logs: `storage/logs/laravel.log`
2. Cron logs: `/var/log/cron` hoặc `/var/log/syslog`
3. Scheduler output: `php artisan schedule:list -v`
