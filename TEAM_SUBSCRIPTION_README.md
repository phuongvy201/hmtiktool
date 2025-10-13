# Hướng dẫn Quản lý Gói Dịch vụ cho Team

## Tổng quan

Chức năng này cho phép quản trị viên gán các gói dịch vụ cho các team trong hệ thống. Mỗi team có thể có nhiều gói dịch vụ với thời gian khác nhau, nhưng chỉ có một gói dịch vụ đang hoạt động tại một thời điểm.

## Cấu trúc Database

### Bảng `team_subscriptions`

```sql
CREATE TABLE team_subscriptions (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    team_id BIGINT NOT NULL,
    service_package_id BIGINT NOT NULL,
    assigned_by BIGINT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired', 'cancelled', 'pending') DEFAULT 'active',
    paid_amount DECIMAL(10,2) NULL,
    payment_method VARCHAR(255) NULL,
    transaction_id VARCHAR(255) NULL,
    notes TEXT NULL,
    auto_renew BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
    FOREIGN KEY (service_package_id) REFERENCES service_packages(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE SET NULL
);
```

## Models

### TeamSubscription Model

Model này quản lý thông tin gói dịch vụ của team với các relationship:

-   `team()`: Quan hệ với Team
-   `servicePackage()`: Quan hệ với ServicePackage
-   `assignedBy()`: Quan hệ với User (người gán)

### Các Methods chính:

-   `isActive()`: Kiểm tra gói có đang hoạt động không
-   `isExpired()`: Kiểm tra gói đã hết hạn chưa
-   `getRemainingDaysAttribute()`: Tính số ngày còn lại
-   `getFormattedPaidAmountAttribute()`: Format số tiền thanh toán
-   `getStatusTextAttribute()`: Lấy text trạng thái tiếng Việt

### Team Model (đã cập nhật)

Thêm các methods mới:

-   `subscriptions()`: Lấy tất cả gói dịch vụ của team
-   `activeSubscriptions()`: Lấy các gói đang hoạt động
-   `currentSubscription()`: Lấy gói hiện tại đang hoạt động
-   `hasActiveSubscription()`: Kiểm tra team có gói đang hoạt động không

## Routes

```php
// Team Subscription management routes
Route::resource('team-subscriptions', TeamSubscriptionController::class);
Route::post('/teams/{team}/assign-package', [TeamSubscriptionController::class, 'assignToTeam'])
    ->name('team-subscriptions.assign-to-team');
Route::get('/teams/{team}/subscriptions', [TeamSubscriptionController::class, 'teamSubscriptions'])
    ->name('team-subscriptions.team-subscriptions');
```

## Controller

### TeamSubscriptionController

Các methods chính:

-   `index()`: Hiển thị danh sách tất cả gói dịch vụ team
-   `create()`: Form tạo gói dịch vụ mới
-   `store()`: Lưu gói dịch vụ mới
-   `show()`: Hiển thị chi tiết gói dịch vụ
-   `edit()`: Form chỉnh sửa gói dịch vụ
-   `update()`: Cập nhật gói dịch vụ
-   `destroy()`: Xóa gói dịch vụ
-   `assignToTeam()`: Gán gói dịch vụ cho team cụ thể
-   `teamSubscriptions()`: Hiển thị tất cả gói dịch vụ của một team

## Views

### 1. `team-subscriptions/index.blade.php`

-   Danh sách tất cả gói dịch vụ team
-   Bộ lọc theo team, gói dịch vụ, trạng thái, ngày
-   Thao tác xem, sửa, xóa

### 2. `team-subscriptions/create.blade.php`

-   Form tạo gói dịch vụ mới
-   Chọn team và gói dịch vụ
-   Nhập thông tin thời gian, thanh toán
-   JavaScript tự động tính ngày kết thúc

### 3. `team-subscriptions/show.blade.php`

-   Hiển thị chi tiết gói dịch vụ
-   Thông tin team, gói dịch vụ, thanh toán
-   Lịch sử và thông tin gán

### 4. `team-subscriptions/edit.blade.php`

-   Form chỉnh sửa gói dịch vụ
-   Tương tự form create nhưng có dữ liệu sẵn

### 5. `team-subscriptions/team-subscriptions.blade.php`

-   Hiển thị tất cả gói dịch vụ của một team cụ thể
-   Thông tin team và gói hiện tại

### 6. Cập nhật `teams/show.blade.php`

-   Thêm section hiển thị gói dịch vụ hiện tại
-   Nút gán gói dịch vụ mới
-   Lịch sử gói dịch vụ

## Tính năng chính

### 1. Gán gói dịch vụ

-   Chọn team và gói dịch vụ
-   Nhập thời gian bắt đầu/kết thúc
-   Thông tin thanh toán (tùy chọn)
-   Ghi chú

### 2. Quản lý trạng thái

-   **Active**: Đang hoạt động
-   **Pending**: Chờ xử lý
-   **Expired**: Đã hết hạn
-   **Cancelled**: Đã hủy

### 3. Kiểm tra trùng lặp

-   Hệ thống kiểm tra team đã có gói dịch vụ đang hoạt động chưa
-   Ngăn chặn gán trùng lặp gói dịch vụ

### 4. Tính toán tự động

-   Tự động tính ngày kết thúc dựa trên thời hạn gói
-   Tính số ngày còn lại
-   Format số tiền thanh toán

### 5. Lịch sử và theo dõi

-   Lưu người gán gói dịch vụ
-   Thời gian tạo và cập nhật
-   Lịch sử tất cả gói dịch vụ của team

## Sử dụng

### 1. Truy cập quản lý gói dịch vụ team

```
/team-subscriptions
```

### 2. Gán gói dịch vụ mới

```
/team-subscriptions/create
```

### 3. Xem chi tiết gói dịch vụ

```
/team-subscriptions/{id}
```

### 4. Xem gói dịch vụ của team cụ thể

```
/teams/{team}/subscriptions
```

### 5. Từ trang chi tiết team

```
/teams/{team}
```

-   Xem gói dịch vụ hiện tại
-   Nút gán gói mới
-   Lịch sử gói dịch vụ

## Validation

### Rules cho việc tạo/cập nhật:

```php
'team_id' => 'required|exists:teams,id',
'service_package_id' => 'required|exists:service_packages,id',
'start_date' => 'required|date',
'end_date' => 'required|date|after:start_date',
'status' => 'required|in:active,expired,cancelled,pending',
'paid_amount' => 'nullable|numeric|min:0',
'payment_method' => 'nullable|string|max:255',
'transaction_id' => 'nullable|string|max:255',
'notes' => 'nullable|string',
'auto_renew' => 'boolean',
```

## Migration

Chạy migration để tạo bảng:

```bash
php artisan migrate
```

## Lưu ý

1. **Trùng lặp gói dịch vụ**: Hệ thống sẽ kiểm tra và ngăn chặn việc gán trùng lặp gói dịch vụ đang hoạt động cho cùng một team.

2. **Tính toán thời gian**: Ngày kết thúc được tính tự động dựa trên thời hạn gói dịch vụ, nhưng có thể điều chỉnh thủ công.

3. **Trạng thái**: Chỉ có một gói dịch vụ có thể ở trạng thái "active" tại một thời điểm cho mỗi team.

4. **Xóa gói dịch vụ**: Khi xóa gói dịch vụ, tất cả thông tin liên quan sẽ bị xóa vĩnh viễn.

5. **Backup**: Nên backup dữ liệu trước khi thực hiện các thao tác quan trọng.

## Mở rộng tương lai

1. **Tự động gia hạn**: Tính năng tự động gia hạn gói dịch vụ
2. **Thông báo**: Gửi email thông báo khi gói sắp hết hạn
3. **Báo cáo**: Thống kê và báo cáo gói dịch vụ
4. **API**: REST API cho việc tích hợp với hệ thống khác
5. **Webhook**: Gửi webhook khi có thay đổi trạng thái gói dịch vụ
