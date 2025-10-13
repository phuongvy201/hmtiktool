# Role Team Admin - Hướng dẫn sử dụng

## Tổng quan

Role `team-admin` được thiết kế để quản lý team cụ thể với quyền hạn giới hạn. Team admin chỉ có thể quản lý những thứ liên quan đến team của mình và không thể thực hiện các thao tác quản trị hệ thống.

## Quyền hạn của Team Admin

### ✅ Có thể làm:

#### 👥 Quản lý Người dùng (User Management)

-   **Xem danh sách người dùng** - Chỉ hiển thị người dùng trong team của mình
-   **Chỉnh sửa người dùng** - Chỉ có thể chỉnh sửa người dùng trong team của mình
-   **Không thể tạo người dùng mới** - Chỉ system admin mới có thể tạo người dùng
-   **Không thể xóa người dùng** - Chỉ system admin mới có thể xóa người dùng

#### 🏢 Quản lý Team (Team Management)

-   **Xem thông tin team** - Chỉ hiển thị team của mình
-   **Chỉnh sửa team** - Chỉ có thể chỉnh sửa team của mình
-   **Quản lý thành viên** - Thêm/xóa thành viên trong team của mình
-   **Không thể tạo team mới** - Chỉ system admin mới có thể tạo team
-   **Không thể xóa team** - Chỉ system admin mới có thể xóa team

#### 🎭 Quản lý Vai trò (Role Management)

-   **Xem danh sách vai trò** - Chỉ xem (read-only)
-   **Xem chi tiết vai trò** - Chỉ xem (read-only)
-   **Không thể tạo vai trò mới** - Chỉ system admin mới có thể tạo vai trò
-   **Không thể chỉnh sửa vai trò** - Chỉ system admin mới có thể chỉnh sửa vai trò
-   **Không thể xóa vai trò** - Chỉ system admin mới có thể xóa vai trò

#### 📊 Báo cáo và Thống kê

-   **Xem báo cáo tài chính** - Chỉ dữ liệu của team mình
-   **Xem fulfillment** - Chỉ dữ liệu của team mình
-   **Xem sales** - Chỉ dữ liệu của team mình

### ❌ Không thể làm:

#### 🔒 Quản trị Hệ thống

-   Tạo team mới
-   Xóa team
-   Tạo người dùng mới
-   Xóa người dùng
-   Tạo vai trò mới
-   Chỉnh sửa vai trò
-   Xóa vai trò
-   Truy cập cài đặt hệ thống
-   Xem logs hệ thống
-   Quản lý backup

## Cách hoạt động

### 1. Lọc dữ liệu theo Team

Khi team admin đăng nhập, hệ thống sẽ tự động lọc dữ liệu:

```php
// Trong UserController
$query = User::with(['roles', 'team']);

// Áp dụng lọc cho team-admin
if (auth()->user()->hasRole('team-admin')) {
    $query->where('team_id', auth()->user()->team_id);
}
```

### 2. Kiểm tra quyền truy cập

```php
// Kiểm tra xem team admin có thể quản lý user này không
if (!TeamPermissionHelper::canManageUser($user)) {
    abort(403, 'Bạn không có quyền xem người dùng này.');
}
```

### 3. Ẩn các nút không phù hợp

```blade
@can('create-users')
@unless(auth()->user()->hasRole('team-admin'))
    <a href="{{ route('users.create') }}">Thêm Người dùng</a>
@endunless
@endcan
```

## Giao diện người dùng

### Navigation Menu

-   **Quản lý Người dùng** - Chỉ hiển thị "Danh sách người dùng"
-   **Quản lý Vai trò** - Chỉ hiển thị "Danh sách vai trò"
-   **Quản lý Team** - Chỉ hiển thị "Danh sách team"
-   **Không hiển thị** - Nút "Thêm" cho các mục trên

### Danh sách dữ liệu

-   **Users** - Chỉ hiển thị người dùng trong team
-   **Teams** - Chỉ hiển thị team của mình
-   **Roles** - Hiển thị tất cả vai trò (chỉ xem)

### Thao tác

-   **Xem** - Có thể xem chi tiết
-   **Chỉnh sửa** - Chỉ có thể chỉnh sửa dữ liệu của team mình
-   **Xóa** - Không có quyền xóa

## Cấu hình Database

### Role Permissions

```php
$teamAdminPermissions = [
    'view-users',           // Chỉ người dùng trong team
    'edit-users',           // Chỉ người dùng trong team
    'view-roles',           // Xem vai trò (read-only)
    'view-teams',           // Chỉ team của mình
    'edit-teams',           // Chỉ team của mình
    'view-financial-reports', // Báo cáo của team
    'view-fulfillment',     // Fulfillment của team
    'view-sales',           // Sales của team
];
```

### User Model

```php
// Người dùng team-admin phải có team_id
$user->team_id = $teamId;
$user->is_system_user = false; // Phải là team user
```

## Tạo Team Admin

### 1. Tạo role team-admin

```bash
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Gán role cho user

```php
$user = User::find(1);
$user->assignRole('team-admin');
$user->update(['team_id' => $teamId]);
```

### 3. Kiểm tra quyền

```php
if ($user->hasRole('team-admin')) {
    // User là team admin
}
```

## Bảo mật

### Middleware Protection

```php
// Trong routes/web.php
Route::middleware(['auth', 'permission:view-users'])->group(function () {
    Route::resource('users', UserController::class);
});
```

### Controller Protection

```php
// Kiểm tra quyền trong controller
$this->authorize('view-users');

// Kiểm tra team admin
if (auth()->user()->hasRole('team-admin')) {
    // Lọc dữ liệu theo team
}
```

### View Protection

```blade
@can('create-users')
@unless(auth()->user()->hasRole('team-admin'))
    <!-- Hiển thị nút tạo -->
@endunless
@endcan
```

## Troubleshooting

### Lỗi thường gặp

1. **"Bạn không có quyền xem người dùng này"**

    - Kiểm tra xem user có thuộc team của team admin không
    - Đảm bảo team admin có team_id

2. **"Team Admin không thể tạo team mới"**

    - Đây là hành vi bình thường
    - Chỉ system admin mới có thể tạo team

3. **"Team Admin không thể quản lý vai trò"**

    - Đây là hành vi bình thường
    - Team admin chỉ có thể xem vai trò

4. **Không hiển thị dữ liệu**
    - Kiểm tra xem team admin có team_id không
    - Kiểm tra xem có user nào trong team không

### Debug

```php
// Kiểm tra role
dd(auth()->user()->roles->pluck('name'));

// Kiểm tra team
dd(auth()->user()->team);

// Kiểm tra permissions
dd(auth()->user()->getAllPermissions()->pluck('name'));
```

## Best Practices

### 1. Luôn kiểm tra quyền

```php
// Trước khi thực hiện action
if (!TeamPermissionHelper::canManageUser($user)) {
    abort(403, 'Không có quyền truy cập.');
}
```

### 2. Lọc dữ liệu

```php
// Luôn lọc dữ liệu theo team cho team admin
$query = User::query();
if (auth()->user()->hasRole('team-admin')) {
    $query->where('team_id', auth()->user()->team_id);
}
```

### 3. Ẩn UI không phù hợp

```blade
@unless(auth()->user()->hasRole('team-admin'))
    <!-- Hiển thị nút chỉ cho non-team-admin -->
@endunless
```

## Kết luận

Role `team-admin` cung cấp khả năng quản lý team một cách an toàn và có kiểm soát. Team admin có thể quản lý thành viên và thông tin team của mình mà không ảnh hưởng đến các team khác hoặc hệ thống tổng thể.

Điều này đảm bảo tính bảo mật và phân quyền rõ ràng trong hệ thống quản lý team.
