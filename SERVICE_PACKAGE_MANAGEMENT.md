# Hướng dẫn Quản lý Gói Dịch vụ

## 🎯 Tổng quan

Hệ thống quản lý gói dịch vụ cho phép **System Admin** tạo, chỉnh sửa và quản lý các gói dịch vụ với các tính năng và giới hạn khác nhau.

### ✨ Tính năng chính

-   ✅ **Tạo gói dịch vụ** - Định nghĩa gói với giá, thời hạn, giới hạn
-   ✅ **Quản lý tính năng** - Cấp quyền tính năng cho từng gói
-   ✅ **Giới hạn sử dụng** - Kiểm soát số lượng user, project, storage
-   ✅ **Trạng thái hoạt động** - Kích hoạt/vô hiệu hóa gói
-   ✅ **Gói nổi bật** - Đánh dấu gói đặc biệt
-   ✅ **Theo dõi đăng ký** - Xem số lượng user đang sử dụng

## 🗄️ Cấu trúc Database

### Bảng `service_packages`

```sql
- id (Primary Key)
- name (Tên gói)
- slug (URL friendly)
- description (Mô tả)
- price (Giá)
- currency (Đơn vị tiền tệ)
- duration_days (Thời hạn ngày)
- is_active (Trạng thái hoạt động)
- is_featured (Gói nổi bật)
- max_users (Số user tối đa)
- max_projects (Số project tối đa)
- max_storage_gb (Dung lượng GB)
- features (JSON - Tính năng)
- limitations (JSON - Giới hạn)
- sort_order (Thứ tự hiển thị)
- timestamps
```

### Bảng `user_subscriptions`

```sql
- id (Primary Key)
- user_id (Foreign Key)
- service_package_id (Foreign Key)
- start_date (Ngày bắt đầu)
- end_date (Ngày kết thúc)
- status (Trạng thái)
- paid_amount (Số tiền đã trả)
- payment_method (Phương thức thanh toán)
- transaction_id (ID giao dịch)
- notes (Ghi chú)
- auto_renew (Tự động gia hạn)
- timestamps
```

## 🔧 Cài đặt

### 1. **Chạy Migration**

```bash
php artisan migrate
```

### 2. **Chạy Seeder**

```bash
php artisan db:seed --class=ServicePackagePermissionSeeder
```

### 3. **Kiểm tra Permissions**

```bash
php artisan permission:show
```

## 🎯 Quyền truy cập

### System Admin

-   ✅ **view-service-packages** - Xem danh sách gói
-   ✅ **create-service-packages** - Tạo gói mới
-   ✅ **edit-service-packages** - Chỉnh sửa gói
-   ✅ **delete-service-packages** - Xóa gói
-   ✅ **restore-service-packages** - Khôi phục gói
-   ✅ **force-delete-service-packages** - Xóa vĩnh viễn

### Các Role khác

-   ❌ Không có quyền truy cập

## 📋 Hướng dẫn sử dụng

### 1. **Truy cập trang quản lý**

```bash
http://your-domain.com/service-packages
```

### 2. **Tạo gói dịch vụ mới**

1. Click "Tạo gói mới"
2. Điền thông tin cơ bản:

    - **Tên gói**: Tên hiển thị
    - **Mô tả**: Chi tiết về gói
    - **Thứ tự hiển thị**: Sắp xếp trong danh sách

3. Cấu hình giá:

    - **Giá**: Số tiền
    - **Đơn vị tiền tệ**: VND, USD, EUR
    - **Thời hạn**: Số ngày

4. Thiết lập giới hạn:

    - **Số user tối đa**: Giới hạn người dùng
    - **Số project tối đa**: Giới hạn dự án
    - **Dung lượng lưu trữ**: Giới hạn GB

5. Chọn tính năng:

    - ✅ Quản lý người dùng
    - ✅ Quản lý dự án
    - ✅ Tải file lên
    - ✅ Truy cập API
    - ✅ Phân tích nâng cao
    - ✅ Hỗ trợ ưu tiên
    - ✅ Tùy chỉnh thương hiệu
    - ✅ Sao lưu & Khôi phục
    - ✅ Làm việc nhóm
    - ✅ Bảo mật nâng cao

6. Cấu hình trạng thái:
    - **Kích hoạt**: Gói có thể sử dụng
    - **Nổi bật**: Hiển thị đặc biệt

### 3. **Chỉnh sửa gói dịch vụ**

1. Click icon "Chỉnh sửa" bên cạnh gói
2. Cập nhật thông tin cần thiết
3. Lưu thay đổi

### 4. **Quản lý trạng thái**

-   **Kích hoạt/Vô hiệu hóa**: Click icon toggle
-   **Nổi bật/Bỏ nổi bật**: Click icon sao
-   **Xóa gói**: Click icon thùng rác (chỉ khi không có user đăng ký)

## 🎨 Giao diện

### Danh sách gói dịch vụ

-   **Tìm kiếm**: Theo tên, mô tả
-   **Lọc**: Theo trạng thái, nổi bật
-   **Hiển thị**: Bảng với thông tin chi tiết
-   **Thao tác**: Xem, sửa, toggle, xóa

### Form tạo/chỉnh sửa

-   **Thông tin cơ bản**: Tên, mô tả, thứ tự
-   **Thông tin giá**: Giá, tiền tệ, thời hạn
-   **Giới hạn sử dụng**: User, project, storage
-   **Tính năng**: Checkbox các tính năng
-   **Trạng thái**: Active, featured

## 🔐 Bảo mật

### 1. **Kiểm soát truy cập**

-   Chỉ System Admin có quyền quản lý
-   Middleware kiểm tra role và permission
-   Policy kiểm tra quyền chi tiết

### 2. **Validation**

-   Validate dữ liệu đầu vào
-   Kiểm tra ràng buộc business logic
-   Ngăn xóa gói đang có user sử dụng

### 3. **Audit Trail**

-   Log tất cả thao tác CRUD
-   Theo dõi thay đổi trạng thái
-   Backup dữ liệu định kỳ

## 📊 Báo cáo và Thống kê

### 1. **Thống kê gói**

-   Số lượng gói đang hoạt động
-   Gói được sử dụng nhiều nhất
-   Doanh thu theo gói

### 2. **Theo dõi đăng ký**

-   Số user đang sử dụng từng gói
-   Gói sắp hết hạn
-   Tỷ lệ gia hạn

### 3. **Phân tích**

-   Xu hướng đăng ký
-   Hiệu quả marketing
-   ROI của từng gói

## 🚀 Tích hợp

### 1. **Với User Management**

-   Gán gói cho user khi tạo tài khoản
-   Kiểm tra giới hạn khi user thực hiện hành động
-   Tự động khóa tính năng khi hết hạn

### 2. **Với Billing System**

-   Tích hợp thanh toán
-   Tự động gia hạn
-   Gửi thông báo hết hạn

### 3. **Với Analytics**

-   Theo dõi sử dụng tính năng
-   Phân tích hành vi user
-   Tối ưu hóa gói dịch vụ

## 🔧 Troubleshooting

### 1. **Gói không hiển thị**

-   Kiểm tra trạng thái `is_active`
-   Kiểm tra quyền truy cập
-   Clear cache: `php artisan cache:clear`

### 2. **Không thể xóa gói**

-   Kiểm tra có user đang sử dụng
-   Kiểm tra quyền `delete-service-packages`
-   Backup dữ liệu trước khi xóa

### 3. **Lỗi validation**

-   Kiểm tra dữ liệu đầu vào
-   Xem log lỗi: `storage/logs/laravel.log`
-   Kiểm tra migration đã chạy

## 📞 Hỗ trợ

Nếu gặp vấn đề với quản lý gói dịch vụ:

1. **Kiểm tra quyền**: Đảm bảo có role system-admin
2. **Xem log**: Kiểm tra log lỗi
3. **Test migration**: Chạy lại migration nếu cần
4. **Liên hệ**: Contact development team

---

**Hệ thống quản lý gói dịch vụ này giúp System Admin kiểm soát hoàn toàn việc cung cấp dịch vụ và tính phí cho người dùng!** 🎯
