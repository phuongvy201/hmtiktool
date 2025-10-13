# Hệ thống Quản lý Vai trò (Role Management System)

## Tổng quan

Hệ thống quản lý vai trò được xây dựng trên Laravel với package Spatie Laravel Permission, cung cấp khả năng quản lý vai trò và phân quyền một cách linh hoạt và bảo mật.

## Tính năng chính

### 🔐 Quản lý Vai trò (Role Management)

-   ✅ **Tạo vai trò mới** - Tạo vai trò với tên và phân quyền
-   ✅ **Chỉnh sửa vai trò** - Cập nhật thông tin và quyền hạn
-   ✅ **Xem chi tiết vai trò** - Hiển thị thông tin chi tiết và người dùng
-   ✅ **Xóa vai trò** - Xóa vai trò (có kiểm tra ràng buộc)
-   ✅ **Tìm kiếm và lọc** - Tìm kiếm theo tên, lọc theo số quyền/người dùng

### 🎯 Phân quyền (Permission Management)

-   ✅ **Quản lý người dùng** - view-users, create-users, edit-users, delete-users
-   ✅ **Quản lý vai trò** - view-roles, create-roles, edit-roles, delete-roles
-   ✅ **Quản lý team** - view-teams, create-teams, edit-teams, delete-teams
-   ✅ **Báo cáo tài chính** - view-financial-reports, create-financial-reports, edit-financial-reports, delete-financial-reports
-   ✅ **Fulfillment** - view-fulfillment, create-fulfillment, edit-fulfillment, delete-fulfillment
-   ✅ **Sales** - view-sales, create-sales, edit-sales, delete-sales
-   ✅ **Hệ thống** - view-system-settings, edit-system-settings, view-logs, manage-backups

## Cấu trúc Vai trò

### 1. System Admin

-   **Quyền hạn**: Tất cả quyền trong hệ thống
-   **Mô tả**: Quản trị viên hệ thống, có toàn quyền truy cập

### 2. Manager

-   **Quyền hạn**:
    -   Quản lý người dùng (không xóa)
    -   Xem vai trò
    -   Quản lý team (không xóa)
    -   Quản lý báo cáo tài chính (không xóa)
    -   Quản lý fulfillment (không xóa)
    -   Quản lý sales (không xóa)
-   **Mô tả**: Quản lý cấp trung, có quyền quản lý nhưng không xóa

### 3. User

-   **Quyền hạn**:
    -   Xem người dùng
    -   Xem team
    -   Xem báo cáo tài chính
    -   Xem fulfillment
    -   Xem sales
-   **Mô tả**: Người dùng thông thường, chỉ có quyền xem

### 4. Viewer

-   **Quyền hạn**:
    -   Xem báo cáo tài chính
    -   Xem fulfillment
    -   Xem sales
-   **Mô tả**: Người dùng chỉ đọc, không có quyền quản lý

## Hướng dẫn sử dụng

### 1. Khởi tạo hệ thống

```bash
# Chạy migration
php artisan migrate

# Chạy seeder để tạo roles và permissions
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Truy cập quản lý vai trò

1. Đăng nhập với tài khoản có quyền `view-roles`
2. Vào menu **Quản lý Vai trò** trong navigation
3. Chọn **Danh sách vai trò** để xem tất cả vai trò

### 3. Tạo vai trò mới

1. Click **Thêm Vai trò** từ trang danh sách
2. Nhập tên vai trò
3. Chọn các quyền hạn cần thiết
4. Sử dụng **Chọn nhanh theo nhóm** để chọn nhiều quyền cùng lúc
5. Click **Tạo vai trò**

### 4. Chỉnh sửa vai trò

1. Từ danh sách vai trò, click icon **Chỉnh sửa**
2. Cập nhật tên vai trò nếu cần
3. Thêm/bớt quyền hạn
4. Click **Cập nhật vai trò**

### 5. Xem chi tiết vai trò

1. Click icon **Xem** từ danh sách vai trò
2. Xem thông tin chi tiết:
    - Thông tin cơ bản
    - Danh sách quyền hạn
    - Người dùng có vai trò này
    - Phân loại quyền hạn theo nhóm

### 6. Xóa vai trò

1. Click icon **Xóa** từ danh sách vai trò
2. Xác nhận xóa
3. **Lưu ý**: Không thể xóa vai trò đang có người dùng sử dụng

## Tìm kiếm và Lọc

### Tìm kiếm

-   Tìm kiếm theo tên vai trò
-   Hỗ trợ tìm kiếm một phần tên

### Lọc

-   **Số quyền**: Không có quyền, 1-5 quyền, 6-10 quyền, 10+ quyền
-   **Số người dùng**: Không có người dùng, 1-5 người dùng, 6-10 người dùng, 10+ người dùng

## Bảo mật

### Kiểm tra quyền trong Controller

```php
// Kiểm tra quyền trước khi thực hiện action
$this->authorize('view-roles');
$this->authorize('create-roles');
$this->authorize('edit-roles');
$this->authorize('delete-roles');
```

### Kiểm tra quyền trong View

```blade
@can('view-roles')
    <!-- Hiển thị nội dung cho người có quyền xem -->
@endcan

@can('create-roles')
    <!-- Hiển thị nút tạo vai trò -->
@endcan
```

### Kiểm tra vai trò

```blade
@role('system-admin')
    <!-- Chỉ hiển thị cho system admin -->
@endrole
```

## API Endpoints

### Roles

-   `GET /roles` - Danh sách vai trò
-   `GET /roles/create` - Form tạo vai trò
-   `POST /roles` - Tạo vai trò mới
-   `GET /roles/{role}` - Xem chi tiết vai trò
-   `GET /roles/{role}/edit` - Form chỉnh sửa vai trò
-   `PUT /roles/{role}` - Cập nhật vai trò
-   `DELETE /roles/{role}` - Xóa vai trò

## Middleware

### Permission Middleware

```php
Route::middleware('permission:view-roles')->group(function () {
    Route::resource('roles', RoleController::class);
});
```

### Role Middleware

```php
Route::middleware('role:system-admin')->group(function () {
    // Routes chỉ dành cho system admin
});
```

## Giao diện

### Thiết kế

-   🎨 **Dark theme** - Giao diện tối hiện đại
-   🎨 **Responsive** - Tương thích mobile và desktop
-   🎨 **Interactive** - Hover effects và transitions
-   🎨 **Icons** - SVG icons nhất quán

### Components

-   **Role Card** - Hiển thị thông tin vai trò
-   **Permission Grid** - Danh sách quyền hạn
-   **User Avatars** - Hiển thị người dùng có vai trò
-   **Quick Select** - Chọn nhanh quyền theo nhóm

## Troubleshooting

### Lỗi thường gặp

1. **Không thể xóa vai trò**

    - Kiểm tra xem vai trò có người dùng nào đang sử dụng không
    - Chỉ có thể xóa vai trò không có người dùng

2. **Không hiển thị menu**

    - Kiểm tra quyền của user hiện tại
    - Đảm bảo user có role và permissions phù hợp

3. **Lỗi permission denied**
    - Kiểm tra middleware trong routes
    - Đảm bảo user có quyền truy cập

### Debug

```bash
# Xem tất cả permissions
php artisan tinker
>>> Spatie\Permission\Models\Permission::all()->pluck('name');

# Xem tất cả roles
>>> Spatie\Permission\Models\Role::all()->pluck('name');

# Kiểm tra quyền của user
>>> $user = App\Models\User::find(1);
>>> $user->getAllPermissions()->pluck('name');
```

## Tùy chỉnh

### Thêm quyền mới

1. Thêm permission vào `RolePermissionSeeder.php`
2. Chạy lại seeder hoặc tạo permission thủ công
3. Gán permission cho các role phù hợp

### Tạo role mới

1. Tạo method mới trong `RolePermissionSeeder.php`
2. Định nghĩa permissions cho role
3. Chạy seeder hoặc tạo thủ công

### Tùy chỉnh giao diện

-   Chỉnh sửa views trong `resources/views/roles/`
-   Tùy chỉnh CSS classes và styling
-   Thêm JavaScript cho interactions

## Kết luận

Hệ thống quản lý vai trò cung cấp một giải pháp hoàn chỉnh cho việc phân quyền trong ứng dụng Laravel. Với giao diện thân thiện và tính năng đầy đủ, hệ thống đảm bảo bảo mật và dễ sử dụng cho người quản trị.
