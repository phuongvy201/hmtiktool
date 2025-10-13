# Hệ thống Quản lý Team (Team Management System)

## Tổng quan

Hệ thống quản lý team được xây dựng trên Laravel, cung cấp khả năng quản lý teams và tổ chức người dùng một cách hiệu quả. Hệ thống hỗ trợ phân chia người dùng theo teams, quản lý thành viên và theo dõi trạng thái hoạt động.

## Tính năng chính

### 🏢 Quản lý Team (Team Management)

-   ✅ **Tạo team mới** - Tạo team với tên, mô tả và trạng thái
-   ✅ **Chỉnh sửa team** - Cập nhật thông tin team và thành viên
-   ✅ **Xem chi tiết team** - Hiển thị thông tin chi tiết và danh sách thành viên
-   ✅ **Xóa team** - Xóa team (có kiểm tra ràng buộc)
-   ✅ **Tìm kiếm và lọc** - Tìm kiếm theo tên/mô tả, lọc theo trạng thái/số thành viên

### 👥 Quản lý Thành viên (Member Management)

-   ✅ **Thêm thành viên** - Gán người dùng vào team
-   ✅ **Xóa thành viên** - Loại bỏ người dùng khỏi team
-   ✅ **Chuyển team** - Di chuyển người dùng giữa các teams
-   ✅ **Hiển thị avatar** - Avatar và thông tin thành viên
-   ✅ **Thống kê thành viên** - Số lượng và trạng thái thành viên

### 📊 Thống kê và Báo cáo

-   ✅ **Thống kê team** - Số lượng team, thành viên
-   ✅ **Thống kê thành viên** - Đã xác thực, chưa xác thực
-   ✅ **Trạng thái team** - Hoạt động, không hoạt động, tạm ngưng

## Cấu trúc Team

### Trạng thái Team

-   **Hoạt động (Active)** - Team đang hoạt động bình thường
-   **Không hoạt động (Inactive)** - Team tạm thời ngừng hoạt động
-   **Tạm ngưng (Suspended)** - Team bị đình chỉ hoạt động

### Thành viên Team

-   **Team Level Users** - Chỉ người dùng Team Level mới có thể tham gia team
-   **System Level Users** - Không thể tham gia team (quản trị hệ thống)
-   **Một team duy nhất** - Mỗi người dùng chỉ có thể thuộc về một team

## Hướng dẫn sử dụng

### 1. Khởi tạo hệ thống

```bash
# Chạy migration
php artisan migrate

# Chạy seeder để tạo dữ liệu mẫu
php artisan db:seed --class=RolePermissionSeeder
```

### 2. Truy cập quản lý team

1. Đăng nhập với tài khoản có quyền `view-teams`
2. Vào menu **Quản lý Team** trong navigation
3. Chọn **Danh sách team** để xem tất cả teams

### 3. Tạo team mới

1. Click **Thêm Team** từ trang danh sách
2. Nhập thông tin cơ bản:
    - **Tên team** - Tên hiển thị của team
    - **Trạng thái** - Hoạt động/Không hoạt động/Tạm ngưng
    - **Mô tả** - Mô tả về team (tùy chọn)
3. Chọn thành viên cho team:
    - Sử dụng checkbox để chọn người dùng
    - Sử dụng **Chọn tất cả** để chọn nhanh
    - Chỉ hiển thị người dùng Team Level
4. Click **Tạo Team**

### 4. Chỉnh sửa team

1. Từ danh sách team, click icon **Chỉnh sửa**
2. Cập nhật thông tin team nếu cần
3. Quản lý thành viên:
    - Thêm thành viên mới
    - Loại bỏ thành viên hiện tại
    - Thẻ "Hiện tại" cho biết người dùng đang thuộc team
4. Click **Cập nhật Team**

### 5. Xem chi tiết team

1. Click icon **Xem** từ danh sách team
2. Xem thông tin chi tiết:
    - Thông tin cơ bản team
    - Danh sách thành viên với vai trò
    - Thống kê thành viên
    - Trạng thái xác thực email

### 6. Xóa team

1. Click icon **Xóa** từ danh sách team
2. Xác nhận xóa
3. **Lưu ý**: Không thể xóa team đang có thành viên

## Tìm kiếm và Lọc

### Tìm kiếm

-   Tìm kiếm theo tên team hoặc mô tả
-   Hỗ trợ tìm kiếm một phần từ khóa

### Lọc

-   **Trạng thái**: Hoạt động, Không hoạt động, Tạm ngưng
-   **Số thành viên**: Không có thành viên, 1-5 thành viên, 6-10 thành viên, 10+ thành viên

## Bảo mật

### Kiểm tra quyền trong Controller

```php
// Kiểm tra quyền trước khi thực hiện action
$this->authorize('view-teams');
$this->authorize('create-teams');
$this->authorize('edit-teams');
$this->authorize('delete-teams');
```

### Kiểm tra quyền trong View

```blade
@can('view-teams')
    <!-- Hiển thị nội dung cho người có quyền xem -->
@endcan

@can('create-teams')
    <!-- Hiển thị nút tạo team -->
@endcan
```

## API Endpoints

### Teams

-   `GET /teams` - Danh sách teams
-   `GET /teams/create` - Form tạo team
-   `POST /teams` - Tạo team mới
-   `GET /teams/{team}` - Xem chi tiết team
-   `GET /teams/{team}/edit` - Form chỉnh sửa team
-   `PUT /teams/{team}` - Cập nhật team
-   `DELETE /teams/{team}` - Xóa team

## Middleware

### Permission Middleware

```php
Route::middleware('permission:view-teams')->group(function () {
    Route::resource('teams', TeamController::class);
});
```

## Giao diện

### Thiết kế

-   🎨 **Dark theme** - Giao diện tối hiện đại
-   🎨 **Responsive** - Tương thích mobile và desktop
-   🎨 **Interactive** - Hover effects và transitions
-   🎨 **Icons** - SVG icons nhất quán

### Components

-   **Team Card** - Hiển thị thông tin team
-   **Member Grid** - Danh sách thành viên với avatar
-   **Status Badges** - Hiển thị trạng thái team
-   **Statistics Cards** - Thống kê team và thành viên

## Database Schema

### Teams Table

```sql
teams
├── id (Primary Key)
├── name (VARCHAR) - Tên team
├── description (TEXT) - Mô tả team
├── status (ENUM) - Trạng thái: active, inactive, suspended
├── created_at (TIMESTAMP)
└── updated_at (TIMESTAMP)
```

### Users Table (Relationship)

```sql
users
├── id (Primary Key)
├── name (VARCHAR)
├── email (VARCHAR)
├── team_id (Foreign Key -> teams.id)
├── is_system_user (BOOLEAN)
└── ...
```

## Troubleshooting

### Lỗi thường gặp

1. **Không thể xóa team**

    - Kiểm tra xem team có thành viên nào không
    - Chỉ có thể xóa team không có thành viên

2. **Không hiển thị người dùng để thêm**

    - Đảm bảo có người dùng Team Level trong hệ thống
    - Kiểm tra trường `is_system_user` trong bảng users

3. **Người dùng không thể tham gia team**

    - Kiểm tra xem người dùng đã thuộc team khác chưa
    - Mỗi người dùng chỉ có thể thuộc một team

4. **Lỗi permission denied**
    - Kiểm tra middleware trong routes
    - Đảm bảo user có quyền truy cập

### Debug

```bash
# Xem tất cả teams
php artisan tinker
>>> App\Models\Team::all();

# Xem team với thành viên
>>> App\Models\Team::with('users')->get();

# Kiểm tra người dùng Team Level
>>> App\Models\User::where('is_system_user', false)->get();

# Kiểm tra người dùng không có team
>>> App\Models\User::whereNull('team_id')->get();
```

## Tùy chỉnh

### Thêm trạng thái mới

1. Cập nhật migration để thêm trạng thái mới
2. Cập nhật validation rules trong controller
3. Cập nhật view để hiển thị trạng thái mới

### Thêm thông tin team

1. Tạo migration để thêm cột mới
2. Cập nhật model Team với fillable fields
3. Cập nhật controller và views

### Tùy chỉnh giao diện

-   Chỉnh sửa views trong `resources/views/teams/`
-   Tùy chỉnh CSS classes và styling
-   Thêm JavaScript cho interactions

## Tích hợp với Role Management

### Phân quyền Team

-   `view-teams` - Xem danh sách và chi tiết team
-   `create-teams` - Tạo team mới
-   `edit-teams` - Chỉnh sửa thông tin team
-   `delete-teams` - Xóa team

### Vai trò và Team

-   **System Admin** - Quản lý tất cả teams
-   **Manager** - Quản lý teams trong phạm vi quyền
-   **User** - Xem thông tin team
-   **Viewer** - Chỉ xem thông tin cơ bản

## Kết luận

Hệ thống quản lý team cung cấp một giải pháp hoàn chỉnh cho việc tổ chức và quản lý người dùng theo nhóm. Với giao diện thân thiện và tính năng đầy đủ, hệ thống đảm bảo hiệu quả trong việc phân chia công việc và quản lý tổ chức.
