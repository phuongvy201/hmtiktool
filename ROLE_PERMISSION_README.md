# Hệ thống Phân quyền - Role & Permission System

## Tổng quan

Hệ thống phân quyền được xây dựng dựa trên Spatie Permission package với 2 cấp độ:

### 1. SYSTEM LEVEL (Cấp hệ thống)

-   **system-admin**: Quản trị viên hệ thống (toàn quyền)
-   **system-accountant**: Kế toán hệ thống (quản lý tài chính)
-   **system-fulfill-manager**: Quản lý fulfillment hệ thống

### 2. TENANT/TEAM LEVEL (Cấp team)

-   **team-admin**: Quản trị viên team
-   **seller**: Nhân viên bán hàng
-   **accountant**: Kế toán team
-   **fulfill**: Nhân viên fulfillment
-   **viewer**: Người xem (chỉ đọc)

## Cài đặt và Chạy

### 1. Chạy migrations

```bash
php artisan migrate
```

### 2. Chạy seeders

```bash
php artisan db:seed
```

### 3. Tạo user đầu tiên (System Admin)

```bash
php artisan tinker
```

```php
$user = \App\Models\User::create([
    'name' => 'System Admin',
    'email' => 'admin@example.com',
    'password' => Hash::make('password'),
    'is_system_user' => true
]);
$user->assignRole('system-admin');
```

## Sử dụng trong Code

### 1. Kiểm tra quyền trong Controller

```php
// Kiểm tra permission
$this->authorize('view-users');

// Hoặc sử dụng middleware
Route::middleware('permission:view-users')->group(function () {
    // Routes here
});
```

### 2. Kiểm tra role trong Controller

```php
// Kiểm tra role
if (auth()->user()->hasRole('system-admin')) {
    // Logic here
}

// Hoặc sử dụng middleware
Route::middleware('role:system-admin')->group(function () {
    // Routes here
});
```

### 3. Kiểm tra trong Blade Views

```php
{{-- Kiểm tra permission --}}
@can('view-users')
    <a href="{{ route('users.index') }}">Quản lý Users</a>
@endcan

{{-- Kiểm tra role --}}
@role('system-admin')
    <a href="{{ route('system.settings') }}">Cài đặt hệ thống</a>
@endrole

{{-- Kiểm tra nhiều roles --}}
@hasanyrole('system-admin|team-admin')
    <a href="{{ route('users.create') }}">Tạo User</a>
@endhasanyrole
```

### 4. Kiểm tra trong Model

```php
// Kiểm tra user có phải system user không
if ($user->isSystemUser()) {
    // Logic for system user
}

// Kiểm tra user có phải tenant user không
if ($user->isTenantUser()) {
    // Logic for tenant user
}

// Lấy role level
$roleLevel = $user->getRoleLevel(); // 'system' hoặc 'tenant'
```

## Permissions Available

### User Management

-   `view-users`
-   `create-users`
-   `edit-users`
-   `delete-users`

### Team Management

-   `view-teams`
-   `create-teams`
-   `edit-teams`
-   `delete-teams`

### Role Management

-   `view-roles`
-   `create-roles`
-   `edit-roles`
-   `delete-roles`

### System Management

-   `view-system-settings`
-   `edit-system-settings`

### Financial Management

-   `view-financial-reports`
-   `create-financial-reports`
-   `edit-financial-reports`

### Fulfillment Management

-   `view-fulfillment`
-   `create-fulfillment`
-   `edit-fulfillment`
-   `delete-fulfillment`

### Sales Management

-   `view-sales`
-   `create-sales`
-   `edit-sales`
-   `delete-sales`

### Read-only Permissions

-   `view-dashboard`
-   `view-reports`

## Tạo User mới với Role

```php
// Tạo system user
$systemUser = User::create([
    'name' => 'System Accountant',
    'email' => 'accountant@system.com',
    'password' => Hash::make('password'),
    'is_system_user' => true
]);
$systemUser->assignRole('system-accountant');

// Tạo team user
$teamUser = User::create([
    'name' => 'Team Seller',
    'email' => 'seller@team.com',
    'password' => Hash::make('password'),
    'team_id' => 1,
    'is_system_user' => false
]);
$teamUser->assignRole('seller');
```

## Tạo Team mới

```php
$team = Team::create([
    'name' => 'Team A',
    'description' => 'Team A description',
    'status' => 'active'
]);
```

## Middleware Available

### Permission Middleware

```php
Route::middleware('permission:view-users')->group(function () {
    // Routes that require view-users permission
});
```

### Role Middleware

```php
Route::middleware('role:system-admin')->group(function () {
    // Routes that require system-admin role
});
```

## Security Features

1. **Role-based Access Control (RBAC)**: Phân quyền dựa trên vai trò
2. **Permission-based Access Control**: Phân quyền chi tiết
3. **Team Isolation**: Users chỉ có thể truy cập dữ liệu của team mình
4. **System vs Tenant Separation**: Tách biệt rõ ràng giữa system và tenant users

## Best Practices

1. **Luôn kiểm tra quyền**: Sử dụng `@can` directive trong views và `authorize()` trong controllers
2. **Sử dụng middleware**: Bảo vệ routes với middleware phù hợp
3. **Kiểm tra team**: Đảm bảo users chỉ truy cập dữ liệu của team mình
4. **Logging**: Ghi log các hoạt động quan trọng
5. **Regular Audits**: Kiểm tra định kỳ quyền truy cập

## Troubleshooting

### Lỗi "Permission not found"

-   Chạy lại seeder: `php artisan db:seed --class=RolePermissionSeeder`
-   Clear cache: `php artisan cache:clear`

### Lỗi "Role not found"

-   Kiểm tra tên role có đúng không
-   Đảm bảo role đã được tạo trong seeder

### Lỗi "Team not found"

-   Kiểm tra team_id có tồn tại không
-   Đảm bảo team có status 'active'
