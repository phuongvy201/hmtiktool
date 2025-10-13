# Hướng dẫn đơn giản - Role Team Admin

## Tổng quan

Role `team-admin` được thiết kế để quản lý team cụ thể với quyền hạn giới hạn. Team admin chỉ có thể quản lý những thứ liên quan đến team của mình.

## Cách tạo và sử dụng Team Admin

### 1. Tạo role team-admin

```bash
# Chạy seeder để tạo role và permissions
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Gán role cho user

```php
// Trong tinker hoặc seeder
$user = User::find(1); // ID của user muốn gán role
$user->assignRole('team-admin');
$user->update(['team_id' => 1]); // ID của team
```

### 3. Kiểm tra role trong code

```php
// Kiểm tra xem user có phải team admin không
if ($user->hasRole('team-admin')) {
    // User là team admin
}

// Kiểm tra trong blade template
@if(auth()->user()->hasRole('team-admin'))
    <!-- Hiển thị nội dung cho team admin -->
@endif
```

## Quyền hạn của Team Admin

### ✅ Có thể làm:

-   Xem danh sách người dùng (chỉ trong team)
-   Chỉnh sửa người dùng (chỉ trong team)
-   Xem danh sách vai trò (read-only)
-   Xem và chỉnh sửa team của mình
-   Xem báo cáo của team

### ❌ Không thể làm:

-   Tạo người dùng mới
-   Xóa người dùng
-   Tạo/sửa/xóa vai trò
-   Tạo/sửa/xóa team
-   Truy cập cài đặt hệ thống

## Cách kiểm tra trong Views

### Ẩn nút không phù hợp

```blade
{{-- Ẩn nút tạo user cho team admin --}}
@can('create-users')
@unless(auth()->user()->hasRole('team-admin'))
    <a href="{{ route('users.create') }}">Thêm Người dùng</a>
@endunless
@endcan

{{-- Ẩn nút tạo team cho team admin --}}
@can('create-teams')
@unless(auth()->user()->hasRole('team-admin'))
    <a href="{{ route('teams.create') }}">Thêm Team</a>
@endunless
@endcan

{{-- Ẩn nút tạo role cho team admin --}}
@can('create-roles')
@unless(auth()->user()->hasRole('team-admin'))
    <a href="{{ route('roles.create') }}">Thêm Vai trò</a>
@endunless
@endcan
```

### Lọc dữ liệu theo team

```blade
{{-- Chỉ hiển thị user trong team của team admin --}}
@foreach($users as $user)
    @if(!auth()->user()->hasRole('team-admin') || $user->team_id == auth()->user()->team_id)
        <div>{{ $user->name }}</div>
    @endif
@endforeach
```

## Cách kiểm tra trong Controllers

### Lọc query theo team

```php
public function index(Request $request)
{
    $query = User::with(['roles', 'team']);

    // Lọc theo team cho team admin
    if (auth()->user()->hasRole('team-admin')) {
        $query->where('team_id', auth()->user()->team_id);
    }

    $users = $query->paginate(10);
    return view('users.index', compact('users'));
}
```

### Kiểm tra quyền truy cập

```php
public function show(User $user)
{
    // Kiểm tra xem team admin có thể xem user này không
    if (auth()->user()->hasRole('team-admin')) {
        if ($user->team_id !== auth()->user()->team_id) {
            abort(403, 'Bạn không có quyền xem người dùng này.');
        }
    }

    return view('users.show', compact('user'));
}
```

## Middleware Protection

### Đăng ký middleware

```php
// Trong app/Http/Kernel.php
protected $routeMiddleware = [
    // ... other middlewares
    'team.admin' => \App\Http\Middleware\TeamAdminMiddleware::class,
];
```

### Sử dụng middleware

```php
// Trong routes/web.php
Route::middleware(['auth', 'team.admin'])->group(function () {
    Route::resource('users', UserController::class);
    Route::resource('teams', TeamController::class);
    Route::resource('roles', RoleController::class);
});
```

## Troubleshooting

### Lỗi "hasRole method not found"

**Nguyên nhân**: User model chưa có trait `HasRoles`

**Giải pháp**: Đảm bảo User model có:

```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;
    // ...
}
```

### Lỗi "Role not found"

**Nguyên nhân**: Role `team-admin` chưa được tạo

**Giải pháp**: Chạy seeder

```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Lỗi "Permission denied"

**Nguyên nhân**: User không có permission cần thiết

**Giải pháp**: Kiểm tra permissions trong seeder

```php
$teamAdminPermissions = [
    'view-users',
    'edit-users',
    'view-roles',
    'view-teams',
    'edit-teams',
    // ...
];
```

## Best Practices

### 1. Luôn kiểm tra role trước khi thực hiện action

```php
if (auth()->user()->hasRole('team-admin')) {
    // Xử lý logic cho team admin
} else {
    // Xử lý logic cho user khác
}
```

### 2. Sử dụng middleware để bảo vệ routes

```php
Route::middleware(['auth', 'permission:view-users'])->group(function () {
    // Routes được bảo vệ
});
```

### 3. Ẩn UI không phù hợp trong views

```blade
@unless(auth()->user()->hasRole('team-admin'))
    <!-- Hiển thị nút chỉ cho non-team-admin -->
@endunless
```

## Kết luận

Role `team-admin` cung cấp khả năng quản lý team một cách an toàn. Bằng cách sử dụng `hasRole()` method và kiểm tra quyền trong views/controllers, bạn có thể đảm bảo team admin chỉ có thể truy cập dữ liệu của team mình.

