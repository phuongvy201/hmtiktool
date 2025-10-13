# Backup & Restore Management - HMTIK

## Tổng quan

Hệ thống quản lý backup & restore cho phép tạo bản sao lưu dữ liệu, khôi phục dữ liệu và quản lý lịch sử backup. Tính năng này đảm bảo an toàn dữ liệu và khả năng phục hồi khi cần thiết.

## Tính năng chính

### 1. **Tạo Backup**

-   ✅ Tạo backup toàn bộ database
-   ✅ Backup theo bảng cụ thể
-   ✅ Nén file (Gzip)
-   ✅ Mã hóa backup
-   ✅ Loại trừ bảng không cần thiết
-   ✅ Backup tự động

### 2. **Quản lý Backup**

-   ✅ Xem danh sách backup
-   ✅ Chi tiết thông tin backup
-   ✅ Tải xuống file backup
-   ✅ Xóa backup cũ
-   ✅ Export danh sách backup

### 3. **Khôi phục dữ liệu**

-   ✅ Restore từ backup
-   ✅ Xác nhận trước khi restore
-   ✅ Log lịch sử restore
-   ✅ Kiểm tra tính toàn vẹn

### 4. **Giám sát và báo cáo**

-   ✅ Trạng thái hệ thống backup
-   ✅ Thống kê backup
-   ✅ Điểm sức khỏe hệ thống
-   ✅ Theo dõi dung lượng

## Cấu trúc Database

### Backup Logs Table

```sql
backup_logs
├── id (Primary Key)
├── filename (VARCHAR) - Tên file backup
├── type (VARCHAR) - backup, restore
├── status (VARCHAR) - success, failed, in_progress
├── description (TEXT, nullable) - Mô tả
├── file_path (VARCHAR, nullable) - Đường dẫn file
├── file_size (VARCHAR, nullable) - Kích thước file
├── tables_count (INTEGER, default 0) - Số bảng
├── records_count (INTEGER, default 0) - Số records
├── tables_list (JSON, nullable) - Danh sách bảng
├── excluded_tables (JSON, nullable) - Bảng loại trừ
├── compression_type (VARCHAR, default 'gzip') - Loại nén
├── is_encrypted (BOOLEAN, default false) - Có mã hóa
├── encryption_key (VARCHAR, nullable) - Khóa mã hóa
├── started_at (TIMESTAMP) - Thời gian bắt đầu
├── completed_at (TIMESTAMP, nullable) - Thời gian hoàn thành
├── duration_seconds (INTEGER, nullable) - Thời gian thực hiện
├── error_message (TEXT, nullable) - Thông báo lỗi
├── created_by (FOREIGN KEY) - Người tạo
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

## Các trang chính

### 1. **Backup Index (`/backups`)**

-   Danh sách tất cả backup
-   Thống kê tổng quan
-   Thao tác nhanh (tạo, cleanup, export)
-   Phân trang và tìm kiếm

### 2. **Tạo Backup (`/backups/create`)**

-   Form tạo backup mới
-   Tùy chọn nén và mã hóa
-   Chọn bảng loại trừ
-   Thông tin hệ thống

### 3. **Chi tiết Backup (`/backups/{id}`)**

-   Thông tin chi tiết backup
-   Thao tác (download, restore, delete)
-   Thông tin kỹ thuật
-   Lịch sử thực hiện

### 4. **Trạng thái Backup (`/backups/status`)**

-   Tổng quan hệ thống
-   Điểm sức khỏe
-   Backup gần đây
-   Thống kê chi tiết

## API Endpoints

### Backup Management

```php
GET    /backups                    - Danh sách backup
GET    /backups/create             - Form tạo backup
POST   /backups                    - Tạo backup mới
GET    /backups/{id}               - Chi tiết backup
GET    /backups/{id}/download      - Tải xuống backup
POST   /backups/{id}/restore       - Restore từ backup
DELETE /backups/{id}               - Xóa backup
POST   /backups/cleanup            - Dọn dẹp backup cũ
POST   /backups/auto-backup        - Tạo backup tự động
GET    /backups/status             - Trạng thái hệ thống
GET    /backups/export             - Export danh sách
```

## Artisan Commands

### Tạo Backup

```bash
# Tạo backup cơ bản
php artisan backup:create

# Tạo backup với mô tả
php artisan backup:create --description="Backup trước khi cập nhật"

# Tạo backup với mã hóa
php artisan backup:create --encrypt --key="my-secret-key"

# Tạo backup cho bảng cụ thể
php artisan backup:create --tables=users,teams,roles

# Tạo backup loại trừ bảng
php artisan backup:create --exclude=sessions,failed_jobs
```

### Cleanup Backup

```bash
# Cleanup backup cũ (giữ lại 30 ngày)
php artisan backup:cleanup

# Cleanup với số ngày tùy chỉnh
php artisan backup:cleanup --days=7

# Dry run (chỉ xem, không xóa)
php artisan backup:cleanup --dry-run

# Force cleanup (không hỏi xác nhận)
php artisan backup:cleanup --force
```

## Code Usage Examples

### Tạo Backup Programmatically

```php
use App\Services\BackupService;

$backupService = new BackupService();

// Tạo backup cơ bản
$backup = $backupService->createBackup([
    'description' => 'Backup tự động hàng ngày',
    'compression_type' => 'gzip',
    'is_encrypted' => false,
]);

// Tạo backup cho bảng cụ thể
$backup = $backupService->createPartialBackup(['users', 'teams'], [
    'description' => 'Backup dữ liệu user',
    'is_encrypted' => true,
    'encryption_key' => 'secret-key',
]);
```

### Restore từ Backup

```php
use App\Models\BackupLog;

$backupLog = BackupLog::find(1);
$backupService = new BackupService();

// Restore từ backup
$restoreLog = $backupService->restoreBackup($backupLog, [
    'description' => 'Restore sau khi sửa lỗi',
]);
```

### Kiểm tra trạng thái

```php
$backupService = new BackupService();
$status = $backupService->getBackupStatus();

echo "Tổng backup: " . $status['total_backups'];
echo "Thành công: " . $status['successful_backups'];
echo "Tổng dung lượng: " . $status['total_size'];
```

## Bảo mật

### 1. **Phân quyền**

-   Chỉ system-admin có quyền truy cập
-   Permissions chi tiết cho từng hành động
-   Logging tất cả hoạt động

### 2. **Mã hóa**

-   Hỗ trợ mã hóa AES-256-CBC
-   Khóa mã hóa được lưu an toàn
-   Backup nhạy cảm được mã hóa

### 3. **Xác thực**

-   Kiểm tra quyền trước mỗi thao tác
-   Xác nhận trước khi restore
-   Validation đầy đủ

## Cấu hình

### Backup Directory

```php
// config/backup.php
return [
    'backup_path' => storage_path('app/backups'),
    'excluded_tables' => [
        'migrations',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
        'sessions',
    ],
    'compression' => 'gzip',
    'encryption' => false,
];
```

### Scheduled Backup

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule): void
{
    // Backup hàng ngày lúc 2:00 AM
    $schedule->command('backup:create --description="Backup tự động hàng ngày"')
             ->dailyAt('02:00');

    // Cleanup backup cũ hàng tuần
    $schedule->command('backup:cleanup --days=30')
             ->weekly();
}
```

## Monitoring

### Health Checks

-   Kiểm tra dung lượng ổ đĩa
-   Theo dõi tỷ lệ backup thành công
-   Cảnh báo backup thất bại
-   Điểm sức khỏe hệ thống

### Logging

```php
// Log backup thành công
Log::info('Backup created successfully', [
    'filename' => $backup->filename,
    'size' => $backup->file_size,
    'duration' => $backup->duration_seconds,
]);

// Log backup thất bại
Log::error('Backup failed', [
    'filename' => $filename,
    'error' => $e->getMessage(),
]);
```

## Troubleshooting

### Backup thất bại

1. Kiểm tra dung lượng ổ đĩa
2. Kiểm tra quyền thư mục backup
3. Xem log lỗi chi tiết
4. Kiểm tra kết nối database

### Restore thất bại

1. Kiểm tra file backup có tồn tại
2. Kiểm tra khóa mã hóa (nếu có)
3. Kiểm tra dung lượng database
4. Xem log lỗi SQL

### Performance Issues

1. Tối ưu thời gian backup
2. Sử dụng nén để giảm dung lượng
3. Loại trừ bảng không cần thiết
4. Chạy backup trong background

## Best Practices

### 1. **Lập lịch backup**

-   Backup hàng ngày vào giờ thấp điểm
-   Backup trước khi cập nhật hệ thống
-   Backup định kỳ theo tuần/tháng

### 2. **Quản lý dung lượng**

-   Cleanup backup cũ định kỳ
-   Sử dụng nén để tiết kiệm dung lượng
-   Monitoring dung lượng ổ đĩa

### 3. **Bảo mật**

-   Mã hóa backup chứa dữ liệu nhạy cảm
-   Lưu trữ backup ở nhiều nơi
-   Kiểm tra tính toàn vẹn backup

### 4. **Testing**

-   Test restore định kỳ
-   Kiểm tra backup sau khi tạo
-   Validate dữ liệu sau restore

## Integration

### Với System Settings

-   Cấu hình backup từ system settings
-   Tích hợp với logging system
-   Monitoring qua system dashboard

### Với User Management

-   Log người tạo backup
-   Phân quyền theo role
-   Audit trail đầy đủ

### Với Notification System

-   Thông báo backup thành công/thất bại
-   Cảnh báo dung lượng thấp
-   Alert khi backup thất bại

---

**Hệ thống backup & restore đảm bảo an toàn dữ liệu và khả năng phục hồi cho HMTIK!** 🔒💾
