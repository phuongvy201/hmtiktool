# Hướng dẫn Quản lý Role và Bảo mật

## 🛡️ Bảo mật System Admin

### Vấn đề: System Admin xóa chính mình

**Câu hỏi:** Nếu tôi có role là system admin và tôi xóa thành viên là tôi thì sao?

**Trả lời:** Hệ thống đã được thiết kế để ngăn chặn việc này với các biện pháp bảo mật sau:

### 1. **Ngăn xóa chính mình**

-   System admin **KHÔNG THỂ** xóa chính mình
-   Hiển thị thông báo: "Bạn không thể xóa chính mình. Vui lòng liên hệ admin khác để thực hiện thao tác này."

### 2. **Bảo vệ System Admin cuối cùng**

-   Không thể xóa system admin cuối cùng trong hệ thống
-   Đảm bảo luôn có ít nhất 1 system admin để quản lý hệ thống

### 3. **Kiểm soát quyền theo Role**

-   **Team Admin**: Chỉ có thể xóa user trong team của mình
-   **Team Admin**: Không thể xóa user có role cao hơn (system-admin, manager)
-   **System Admin**: Có thể xóa bất kỳ user nào (trừ chính mình)

## 🔧 Các biện pháp bảo mật đã triển khai

### 1. **UserPolicy**

```php
// Kiểm tra quyền xóa user
public function delete(User $user, User $model): bool
{
    // Không thể xóa chính mình
    if ($model->id === $user->id) {
        return false;
    }

    // Không thể xóa system admin cuối cùng
    if ($model->hasRole('system-admin')) {
        $systemAdminCount = User::role('system-admin')->count();
        if ($systemAdminCount <= 1) {
            return false;
        }
    }

    // Team admin chỉ có thể xóa user trong team
    if ($user->hasRole('team-admin')) {
        if ($model->team_id !== $user->team_id) {
            return false;
        }
    }

    return true;
}
```

### 2. **Middleware PreventSelfDeletion**

```php
// Ngăn xóa chính mình ở tầng middleware
if ($user && $user->id === Auth::id()) {
    return redirect()->back()->with('error', 'Bạn không thể xóa chính mình.');
}
```

### 3. **Component Delete User Button**

-   Hiển thị tooltip giải thích tại sao không thể xóa
-   Disable nút xóa khi không có quyền
-   Hiển thị thông báo rõ ràng

## 🎯 Quản lý Role

### 1. **Xem danh sách Role**

```bash
php artisan admin:manage list
```

### 2. **Thêm System Admin**

```bash
php artisan admin:manage add --email=admin@example.com
```

### 3. **Xóa System Admin**

```bash
php artisan admin:manage remove --email=admin@example.com
```

### 4. **Chuyển quyền System Admin**

```bash
php artisan admin:manage transfer --email=old@example.com --new-admin=new@example.com
```

## 📋 Các Route và View

### Role Management Routes

-   `GET /roles` - Danh sách roles
-   `GET /roles/create` - Tạo role mới
-   `POST /roles` - Lưu role mới
-   `GET /roles/{role}` - Xem chi tiết role
-   `GET /roles/{role}/edit` - Chỉnh sửa role
-   `PUT /roles/{role}` - Cập nhật role
-   `DELETE /roles/{role}` - Xóa role

### User Management Routes

-   `GET /users` - Danh sách users
-   `GET /users/create` - Tạo user mới
-   `POST /users` - Lưu user mới
-   `GET /users/{user}` - Xem chi tiết user
-   `GET /users/{user}/edit` - Chỉnh sửa user
-   `PUT /users/{user}` - Cập nhật user
-   `DELETE /users/{user}` - Xóa user (có bảo vệ)

## 🔐 Phân quyền theo Role

### System Admin

-   ✅ Xem tất cả users và roles
-   ✅ Tạo, chỉnh sửa, xóa users (trừ chính mình)
-   ✅ Tạo, chỉnh sửa, xóa roles
-   ✅ Quản lý toàn bộ hệ thống

### Team Admin

-   ✅ Xem users trong team của mình
-   ✅ Tạo, chỉnh sửa, xóa users trong team (trừ user có role cao hơn)
-   ❌ Không thể tạo, chỉnh sửa, xóa roles
-   ❌ Không thể xóa chính mình

### Manager

-   ✅ Xem users và roles
-   ✅ Tạo, chỉnh sửa users
-   ❌ Không thể xóa users
-   ❌ Không thể quản lý roles

### User

-   ✅ Xem thông tin cơ bản
-   ❌ Không thể quản lý users/roles

### Viewer

-   ✅ Xem thông tin (chỉ đọc)
-   ❌ Không thể thực hiện thao tác nào

## 🚨 Các trường hợp đặc biệt

### 1. **System Admin cuối cùng**

-   Không thể xóa system admin cuối cùng
-   Phải tạo system admin mới trước khi xóa

### 2. **Team Admin xóa user**

-   Chỉ có thể xóa user trong team của mình
-   Không thể xóa user có role cao hơn

### 3. **Xóa chính mình**

-   Tất cả role đều không thể xóa chính mình
-   Phải liên hệ admin khác để thực hiện

## 📞 Hỗ trợ

Nếu gặp vấn đề về quản lý role hoặc bảo mật, vui lòng:

1. Kiểm tra logs trong `storage/logs/`
2. Sử dụng command `php artisan admin:manage list` để kiểm tra system admin
3. Liên hệ developer để được hỗ trợ
