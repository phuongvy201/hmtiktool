# Hệ thống Quản lý Team Admin - Hướng dẫn chi tiết

## 🎯 Tổng quan

Hệ thống đã được thiết kế lại để phân chia rõ ràng quyền hạn giữa **System Admin** và **Team Admin**:

### 🔐 System Admin

-   Quản lý toàn bộ users và teams trong hệ thống
-   Có quyền tạo, sửa, xóa users, teams, roles
-   Truy cập tất cả dữ liệu hệ thống

### 👥 Team Admin

-   Chỉ quản lý thành viên trong team của mình
-   Có đầy đủ chức năng CRUD cho team members
-   Gán vai trò cho thành viên trong team

## 📁 Cấu trúc Files mới

### Controllers

```
app/Http/Controllers/
├── UserController.php          # System Admin - Quản lý toàn bộ users
├── TeamController.php          # System Admin - Quản lý toàn bộ teams
├── RoleController.php          # System Admin - Quản lý roles
└── TeamAdminController.php     # Team Admin - Quản lý team members
```

### Views

```
resources/views/
├── users/                      # System Admin views
│   ├── index.blade.php
│   ├── create.blade.php
│   ├── edit.blade.php
│   └── show.blade.php
├── teams/                      # System Admin views
├── roles/                      # System Admin views
└── team-admin/                 # Team Admin views
    └── users/
        ├── index.blade.php     # Danh sách thành viên team
        ├── create.blade.php    # Thêm thành viên mới
        ├── edit.blade.php      # Chỉnh sửa thành viên
        └── show.blade.php      # Xem chi tiết thành viên
```

### Routes

```php
// System Admin routes
Route::resource('users', UserController::class);
Route::resource('teams', TeamController::class);
Route::resource('roles', RoleController::class);

// Team Admin routes
Route::middleware('role:team-admin')->prefix('team-admin')->name('team-admin.')->group(function () {
    Route::get('/dashboard', [TeamAdminController::class, 'dashboard'])->name('dashboard');
    Route::resource('users', TeamAdminController::class);
});
```

## 🚀 Cách sử dụng

### 1. Tạo Team Admin

```bash
# Chạy seeder để tạo role và permissions
php artisan db:seed --class=RolePermissionSeeder
```

```php
// Gán role cho user
$user = User::find(1);
$user->assignRole('team-admin');
$user->update(['team_id' => 1]); // ID của team
```

### 2. Truy cập Team Admin Panel

Team Admin sẽ thấy menu **"Quản lý Thành viên Team"** thay vì **"Quản lý Người dùng"**

URL: `/team-admin/users`

### 3. Chức năng Team Admin

#### ✅ Có thể làm:

-   **Xem danh sách** thành viên trong team
-   **Thêm thành viên mới** vào team
-   **Chỉnh sửa** thông tin thành viên
-   **Gán vai trò** cho thành viên
-   **Xóa thành viên** khỏi team (không xóa user)
-   **Xem thống kê** team

#### ❌ Không thể làm:

-   Tạo/sửa/xóa teams
-   Tạo/sửa/xóa roles
-   Quản lý users ngoài team
-   Truy cập system settings

## 🎨 Giao diện

### Team Admin Dashboard

-   **Header**: Hiển thị tên team
-   **Search & Filter**: Tìm kiếm theo tên, email, vai trò, trạng thái
-   **Table**: Danh sách thành viên với thông tin chi tiết
-   **Statistics**: Thống kê tổng thành viên, đã xác thực, vai trò khác nhau
-   **Actions**: Xem, sửa, xóa thành viên

### Form Thêm Thành viên

-   **Fields**: Tên, email, mật khẩu, vai trò
-   **Auto-assign**: Tự động gán vào team hiện tại
-   **Validation**: Kiểm tra email unique, password min 8 chars
-   **Help**: Hướng dẫn và lưu ý quan trọng

### Form Chỉnh sửa

-   **Pre-filled**: Thông tin hiện tại của thành viên
-   **Optional password**: Chỉ thay đổi nếu cần
-   **Role selection**: Chọn vai trò mới
-   **Status display**: Hiển thị trạng thái xác thực email

## 🔒 Bảo mật

### Middleware Protection

```php
// TeamAdminController
public function __construct()
{
    $this->middleware('auth');
    $this->middleware('role:team-admin');
}
```

### Data Filtering

```php
// Chỉ lấy users trong team
$query = User::where('team_id', auth()->user()->team_id)
    ->where('is_system_user', false);
```

### Access Control

```php
// Kiểm tra user thuộc team
if ($user->team_id !== auth()->user()->team_id) {
    abort(403, 'Bạn không có quyền truy cập.');
}
```

### Self-Protection

```php
// Không cho phép xóa chính mình
if ($user->id === auth()->id()) {
    return redirect()->with('error', 'Bạn không thể xóa chính mình.');
}
```

## 📊 Thống kê Team

### Metrics hiển thị:

-   **Tổng thành viên**: Số lượng thành viên trong team
-   **Đã xác thực**: Số thành viên đã verify email
-   **Vai trò khác nhau**: Số loại vai trò được sử dụng

### Code example:

```php
$stats = [
    'total_members' => $teamMembers->count(),
    'verified_members' => $teamMembers->where('email_verified_at', '!=', null)->count(),
    'different_roles' => $teamMembers->pluck('roles')->flatten()->unique('id')->count(),
];
```

## 🎯 Navigation Logic

### System Admin

```
Quản lý Người dùng
├── Danh sách người dùng (/users)
└── Thêm người dùng (/users/create)
```

### Team Admin

```
Quản lý Người dùng
└── Quản lý Thành viên Team (/team-admin/users)
```

## 🔧 Troubleshooting

### Lỗi "Route not found"

-   Kiểm tra routes đã được đăng ký trong `web.php`
-   Đảm bảo middleware `role:team-admin` hoạt động

### Lỗi "Permission denied"

-   Kiểm tra user có role `team-admin`
-   Kiểm tra user có `team_id`

### Lỗi "User not found"

-   Kiểm tra user có thuộc team của team admin
-   Kiểm tra `is_system_user = false`

### Lỗi "Cannot delete self"

-   Team admin không thể xóa chính mình
-   Sử dụng chức năng khác để thay đổi role

## 📝 Best Practices

### 1. Luôn kiểm tra quyền

```php
if ($user->team_id !== auth()->user()->team_id) {
    abort(403, 'Không có quyền truy cập.');
}
```

### 2. Sử dụng middleware

```php
Route::middleware(['auth', 'role:team-admin'])->group(function () {
    // Team admin routes
});
```

### 3. Lọc dữ liệu theo team

```php
$query->where('team_id', auth()->user()->team_id);
```

### 4. Hiển thị UI phù hợp

```blade
@if(auth()->user()->hasRole('team-admin'))
    <!-- Team admin specific UI -->
@else
    <!-- System admin UI -->
@endif
```

## 🎉 Kết luận

Hệ thống mới cung cấp:

✅ **Phân quyền rõ ràng** giữa System Admin và Team Admin  
✅ **Giao diện riêng biệt** cho từng loại admin  
✅ **Chức năng đầy đủ** cho team admin quản lý thành viên  
✅ **Bảo mật cao** với middleware và access control  
✅ **UX tốt** với thống kê và hướng dẫn

Team Admin giờ đây có thể quản lý team của mình một cách độc lập và hiệu quả! 🚀
