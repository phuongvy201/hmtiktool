# Hướng dẫn Fix Lỗi TikTokShopProductService trên Production

## 🔴 Vấn đề

Lỗi: `Class "App\Services\TikTokShopProductService" not found`

**Nguyên nhân:**
- File có tên sai: `TiktokShopProductService.php` (chữ T viết thường)
- Class name đúng: `TikTokShopProductService` (chữ T viết hoa)
- Trên Linux, tên file phân biệt chữ hoa/thường, nên autoload không tìm thấy class

## ✅ Giải pháp

### Cách 1: Sử dụng Script Tự động (Khuyến nghị)

```bash
# SSH vào EC2
ssh ec2-user@your-ec2-ip

# Upload script lên server
scp fix-tiktok-service-name.sh ec2-user@your-ec2-ip:/var/www/hmtiktool/

# SSH vào server và chạy script
ssh ec2-user@your-ec2-ip
cd /var/www/hmtiktool
chmod +x fix-tiktok-service-name.sh
sudo ./fix-tiktok-service-name.sh
```

### Cách 2: Fix Thủ Công

```bash
# SSH vào EC2
ssh ec2-user@your-ec2-ip
cd /var/www/hmtiktool

# 1. Kiểm tra file cũ
ls -la app/Services/Tik*.php

# 2. Nếu có file TiktokShopProductService.php (chữ T viết thường)
#    và chưa có TikTokShopProductService.php (chữ T viết hoa)
mv app/Services/TiktokShopProductService.php app/Services/TikTokShopProductService.php

# 3. Nếu cả 2 file đều tồn tại, xóa file cũ
rm -f app/Services/TiktokShopProductService.php

# 4. Clear Laravel cache
php artisan clear-compiled
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 5. Regenerate autoload
composer dump-autoload
```

### Cách 3: Upload File Mới từ Local

```bash
# Từ máy local
scp app/Services/TikTokShopProductService.php ec2-user@your-ec2-ip:/var/www/hmtiktool/app/Services/

# SSH vào server và clear cache
ssh ec2-user@your-ec2-ip
cd /var/www/hmtiktool
php artisan clear-compiled
composer dump-autoload
```

## 🔍 Kiểm tra

### 1. Kiểm tra file tồn tại

```bash
ls -la app/Services/TikTokShopProductService.php
```

Kết quả phải thấy file với tên đúng: `TikTokShopProductService.php`

### 2. Kiểm tra class name trong file

```bash
grep "class TikTokShopProductService" app/Services/TikTokShopProductService.php
```

Kết quả phải thấy: `class TikTokShopProductService`

### 3. Test autoload

```bash
php artisan tinker
```

Trong tinker:
```php
use App\Services\TikTokShopProductService;
$service = new TikTokShopProductService();
// Nếu không có lỗi, nghĩa là đã fix thành công
```

### 4. Kiểm tra trong code

```bash
php artisan route:list | grep product
# Hoặc test trực tiếp upload product
```

## ⚠️ Lưu ý

1. **Đảm bảo file mới đã được upload:**
   - File phải có tên: `TikTokShopProductService.php` (chữ T viết hoa)
   - Class name: `TikTokShopProductService`

2. **Clear cache sau khi fix:**
   - Luôn chạy `composer dump-autoload` sau khi đổi tên file
   - Clear Laravel cache để đảm bảo không còn cache cũ

3. **Kiểm tra permissions:**
   ```bash
   chmod 644 app/Services/TikTokShopProductService.php
   chown www-data:www-data app/Services/TikTokShopProductService.php
   ```

## 🆘 Nếu vẫn lỗi

1. **Kiểm tra namespace:**
   ```bash
   head -5 app/Services/TikTokShopProductService.php
   ```
   Phải thấy: `namespace App\Services;`

2. **Kiểm tra autoload trong composer.json:**
   ```bash
   grep -A 5 "autoload" composer.json
   ```

3. **Xem log chi tiết:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

4. **Restart PHP-FPM (nếu cần):**
   ```bash
   sudo systemctl restart php-fpm
   # hoặc
   sudo service php8.4-fpm restart
   ```

## ✅ Sau khi fix

Lỗi sẽ hết và bạn có thể:
- Upload products lên TikTok
- Sử dụng `TikTokShopProductService` trong code
- Không còn lỗi "Class not found"
