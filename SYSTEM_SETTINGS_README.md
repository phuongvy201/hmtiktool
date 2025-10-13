# System Settings Management - HMTIK

## Tổng quan

Hệ thống quản lý cấu hình cho phép System Admin điều chỉnh các thiết lập hệ thống một cách linh hoạt và an toàn. Tất cả cấu hình được lưu trữ trong database và có thể được export/import.

## Tính năng chính

### 1. **Quản lý Cấu hình**

-   ✅ Cấu hình chung (General)
-   ✅ Cấu hình người dùng (User)
-   ✅ Cấu hình múi giờ (Timezone)
-   ✅ Cấu hình API
-   ✅ Cấu hình SMTP
-   ✅ Cấu hình Log

### 2. **Tính năng nâng cao**

-   ✅ Export/Import cấu hình
-   ✅ Reset về mặc định
-   ✅ Xem thông tin hệ thống
-   ✅ Phân loại cấu hình (Public/Private)
-   ✅ Hỗ trợ nhiều kiểu dữ liệu

## Cấu trúc Database

### System Settings Table

```sql
system_settings
├── id (Primary Key)
├── key (VARCHAR) - Khóa cấu hình
├── value (TEXT) - Giá trị cấu hình
├── type (VARCHAR) - Kiểu dữ liệu: string, boolean, integer, json, array
├── group (VARCHAR) - Nhóm cấu hình: general, user, timezone, api, smtp, log
├── label (VARCHAR) - Nhãn hiển thị
├── description (TEXT) - Mô tả cấu hình
├── is_public (BOOLEAN) - Có thể xem công khai
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

## Các nhóm cấu hình

### 1. **General (Cấu hình chung)**

-   `app_name`: Tên ứng dụng
-   `app_description`: Mô tả ứng dụng
-   `maintenance_mode`: Chế độ bảo trì

### 2. **User (Cấu hình người dùng)**

-   `user_registration_enabled`: Cho phép đăng ký
-   `email_verification_required`: Yêu cầu xác thực email
-   `password_min_length`: Độ dài mật khẩu tối thiểu
-   `session_timeout`: Thời gian timeout session

### 3. **Timezone (Múi giờ)**

-   `default_timezone`: Múi giờ mặc định
-   `date_format`: Định dạng ngày
-   `time_format`: Định dạng giờ

### 4. **API (Cấu hình API)**

-   `api_rate_limit`: Giới hạn API rate
-   `api_timeout`: Timeout API
-   `api_documentation_enabled`: Bật tài liệu API

### 5. **SMTP (Cấu hình email)**

-   `smtp_host`: SMTP Host
-   `smtp_port`: SMTP Port
-   `smtp_encryption`: Mã hóa SMTP
-   `smtp_username`: SMTP Username
-   `smtp_password`: SMTP Password

### 6. **Log (Cấu hình log)**

-   `log_level`: Mức độ log
-   `log_retention_days`: Thời gian lưu log
-   `log_user_activities`: Log hoạt động người dùng
-   `log_api_calls`: Log API calls

## Kiểu dữ liệu hỗ trợ

### String

-   Sử dụng cho text đơn giản
-   Ví dụ: tên ứng dụng, mô tả

### Boolean

-   Sử dụng cho cấu hình bật/tắt
-   Ví dụ: chế độ bảo trì, cho phép đăng ký

### Integer

-   Sử dụng cho số nguyên
-   Ví dụ: port, timeout, độ dài mật khẩu

### JSON

-   Sử dụng cho cấu hình phức tạp
-   Ví dụ: cấu hình API, settings object

### Array

-   Sử dụng cho danh sách giá trị
-   Ví dụ: danh sách timezone, permissions

## Sử dụng

### 1. Truy cập System Settings

```
Dashboard > System Settings > Cài đặt
```

### 2. Chỉnh sửa cấu hình

1. Chọn tab cấu hình cần thiết
2. Thay đổi giá trị các trường
3. Click "Lưu cấu hình"

### 3. Export cấu hình

1. Click nút "Export"
2. File JSON sẽ được tải về
3. Có thể sử dụng để backup hoặc migrate

### 4. Import cấu hình

1. Click nút "Import"
2. Chọn file JSON
3. Click "Import" để áp dụng

### 5. Reset cấu hình

1. Click nút "Reset"
2. Xác nhận reset
3. Cấu hình sẽ về mặc định

## API Endpoints

```php
// System Settings
GET    /system/settings              // Hiển thị trang cấu hình
POST   /system/settings/update       // Cập nhật cấu hình
POST   /system/settings/reset        // Reset cấu hình
GET    /system/settings/export       // Export cấu hình
POST   /system/settings/import       // Import cấu hình
GET    /system/settings/info         // Thông tin hệ thống
```

## Sử dụng trong Code

### Lấy giá trị cấu hình

```php
use App\Models\SystemSetting;

// Lấy giá trị đơn giản
$appName = SystemSetting::getValue('app_name', 'HMTIK');

// Lấy giá trị với kiểu dữ liệu
$maintenanceMode = SystemSetting::getValue('maintenance_mode', false);
$apiTimeout = SystemSetting::getValue('api_timeout', 30);
```

### Cập nhật cấu hình

```php
// Cập nhật giá trị
SystemSetting::setValue('app_name', 'HMTIK Pro', 'string');
SystemSetting::setValue('maintenance_mode', true, 'boolean');
SystemSetting::setValue('api_timeout', 60, 'integer');
```

### Lấy cấu hình theo nhóm

```php
// Lấy tất cả cấu hình SMTP
$smtpSettings = SystemSetting::getByGroup('smtp');

// Lấy tất cả cấu hình dưới dạng array
$allSettings = SystemSetting::getAllAsArray();
```

## Bảo mật

### Phân quyền

-   Chỉ System Admin có thể truy cập
-   Middleware `role:system-admin` bảo vệ tất cả routes
-   Validation đầy đủ cho tất cả inputs

### Cấu hình Public/Private

-   `is_public = true`: Có thể xem mà không cần admin
-   `is_public = false`: Chỉ admin mới xem được

### Logging

-   Tất cả thay đổi cấu hình được log
-   Track user thực hiện thay đổi
-   Backup trước khi thay đổi quan trọng

## Performance

### Caching

-   Sử dụng Cache để lưu cấu hình
-   Clear cache khi cập nhật
-   Eager loading cho relationships

### Database

-   Index trên trường `key` và `group`
-   Soft deletes cho backup
-   Batch operations cho import/export

## Monitoring

### System Information

-   PHP version
-   Laravel version
-   Database driver
-   Cache driver
-   Session driver
-   Queue driver
-   Timezone
-   Locale
-   Debug mode
-   Maintenance mode
-   Storage paths

### Health Checks

-   Database connectivity
-   Cache functionality
-   File permissions
-   Disk space
-   Memory usage

## Troubleshooting

### Lỗi thường gặp

1. **Không thể lưu cấu hình**

    - Kiểm tra quyền database
    - Kiểm tra validation rules
    - Kiểm tra log errors

2. **Import không thành công**

    - Kiểm tra format JSON
    - Kiểm tra encoding
    - Kiểm tra file size

3. **Cache không clear**
    - Clear cache thủ công
    - Restart queue workers
    - Restart web server

### Debug Commands

```bash
# Clear cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# View system settings
php artisan tinker
>>> App\Models\SystemSetting::all();

# Check specific setting
>>> App\Models\SystemSetting::getValue('app_name');
```

## Tùy chỉnh

### Thêm cấu hình mới

1. Tạo migration nếu cần
2. Thêm vào seeder
3. Cập nhật controller
4. Thêm vào view

### Thêm nhóm cấu hình mới

1. Cập nhật controller với nhóm mới
2. Thêm vào view tabs
3. Tạo seeder cho nhóm mới
4. Cập nhật documentation

### Tùy chỉnh giao diện

-   Chỉnh sửa view trong `resources/views/system-settings/`
-   Tùy chỉnh CSS classes
-   Thêm JavaScript validation
-   Tùy chỉnh form fields

## Integration

### Với các module khác

-   User Management: Sử dụng cấu hình user
-   Email System: Sử dụng cấu hình SMTP
-   API System: Sử dụng cấu hình API
-   Logging System: Sử dụng cấu hình log

### Với external services

-   Email providers (Gmail, SendGrid)
-   API services
-   Monitoring services
-   Backup services

## Best Practices

### Cấu hình

-   Sử dụng key có ý nghĩa
-   Mô tả rõ ràng cho mỗi cấu hình
-   Phân loại đúng nhóm
-   Đặt giá trị mặc định hợp lý

### Bảo mật

-   Không lưu sensitive data dưới dạng plain text
-   Sử dụng encryption cho passwords
-   Validate tất cả inputs
-   Log tất cả thay đổi

### Performance

-   Cache cấu hình thường dùng
-   Sử dụng batch operations
-   Optimize database queries
-   Monitor memory usage

## Migration Guide

### Từ config files

1. Export cấu hình hiện tại
2. Import vào system settings
3. Update code để sử dụng SystemSetting
4. Remove old config files

### Từ database

1. Backup database
2. Run migration
3. Run seeder
4. Test functionality

## Support

### Documentation

-   API documentation
-   User guide
-   Developer guide
-   Troubleshooting guide

### Contact

-   System Admin: admin@system.com
-   Technical Support: support@hmtik.com
-   Emergency: +84 123 456 789
