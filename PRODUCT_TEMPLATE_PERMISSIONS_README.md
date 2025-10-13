# Hệ thống phân quyền Product Template

## 🎯 **Tổng quan:**

Hệ thống phân quyền Product Template được thiết kế theo role-based access control (RBAC) với 3 cấp độ quyền khác nhau:

## 👥 **Phân quyền theo Role:**

### **1. System Admin**

-   ❌ **KHÔNG có quyền** truy cập Product Templates
-   ✅ **Chỉ quản lý hệ thống chung:** User, Team, Role, System Settings, Backup
-   🔒 **Bị chặn hoàn toàn** khỏi tất cả routes Product Template

### **2. Team Admin**

-   ✅ **Full quyền (CRUD)** với tất cả template thuộc team mình quản lý
-   👀 **Xem được** template của tất cả thành viên trong team
-   ✏️ **Chỉnh sửa được** template của tất cả thành viên trong team
-   🗑️ **Xóa được** template của tất cả thành viên trong team
-   ➕ **Tạo được** template mới cho team

### **3. Seller (Thành viên team)**

-   ✅ **Full quyền (CRUD)** nhưng chỉ với template do chính mình tạo ra
-   👀 **Chỉ xem được** template do mình tạo
-   ✏️ **Chỉ chỉnh sửa được** template do mình tạo
-   🗑️ **Chỉ xóa được** template do mình tạo
-   ➕ **Tạo được** template mới cho team
-   ❌ **KHÔNG được phép** chỉnh sửa template của seller khác

## 🏗️ **Kiến trúc Implementation:**

### **1. Database Schema:**

```sql
-- Thêm user_id vào bảng product_templates
ALTER TABLE product_templates ADD COLUMN user_id BIGINT UNSIGNED;
ALTER TABLE product_templates ADD FOREIGN KEY (user_id) REFERENCES users(id);
ALTER TABLE product_templates ADD INDEX idx_team_user (team_id, user_id);
```

### **2. Model Relationships:**

```php
// ProductTemplate Model
public function user(): BelongsTo
{
    return $this->belongsTo(User::class);
}

public function scopeByUser($query, $userId)
{
    return $query->where('user_id', $userId);
}
```

### **3. Policy Implementation:**

```php
// ProductTemplatePolicy
public function viewAny(User $user): bool
{
    // System Admin: Không có quyền xem template
    if ($user->hasRole('system-admin')) {
        return false;
    }
    return true;
}

public function view(User $user, ProductTemplate $template): bool
{
    // System Admin: Không có quyền
    if ($user->hasRole('system-admin')) {
        return false;
    }

    // Kiểm tra team
    if ($user->team_id !== $template->team_id) {
        return false;
    }

    // Team Admin: Xem tất cả
    if ($user->hasRole('team-admin')) {
        return true;
    }

    // Seller: Chỉ xem template của mình
    return $user->id === $template->user_id;
}
```

### **4. Middleware Protection:**

```php
// CheckProductTemplateAccess Middleware
public function handle(Request $request, Closure $next): Response
{
    $user = Auth::user();

    // System Admin: Chặn hoàn toàn
    if ($user->hasRole('system-admin')) {
        abort(403, 'System Admin không có quyền truy cập Product Templates');
    }

    // Kiểm tra quyền truy cập template cụ thể
    if ($request->route('product_template')) {
        $template = $request->route('product_template');

        // Team Admin: Truy cập tất cả
        if ($user->hasRole('team-admin')) {
            return $next($request);
        }

        // Seller: Chỉ truy cập template của mình
        if ($user->id !== $template->user_id) {
            abort(403, 'Bạn chỉ có thể truy cập template do chính mình tạo');
        }
    }

    return $next($request);
}
```

## 🔄 **Flow hoạt động:**

### **1. Khi User truy cập Product Templates:**

```
1. Middleware kiểm tra role
   ├─ System Admin → 403 Forbidden
   ├─ Team Admin → Tiếp tục
   └─ Seller → Tiếp tục

2. Controller sử dụng Policy
   ├─ viewAny() → Lấy danh sách template phù hợp
   ├─ view() → Kiểm tra quyền xem template cụ thể
   ├─ update() → Kiểm tra quyền chỉnh sửa
   └─ delete() → Kiểm tra quyền xóa

3. View hiển thị theo quyền
   ├─ Chỉ hiển thị template user có quyền xem
   ├─ Chỉ hiển thị nút chỉnh sửa nếu có quyền
   └─ Hiển thị thông tin người tạo template
```

### **2. Khi tạo template mới:**

```php
ProductTemplate::create([
    'user_id' => Auth::user()->id,  // Tự động gán người tạo
    'team_id' => Auth::user()->team->id,
    // ... other fields
]);
```

## 📊 **Scenarios (Các tình huống):**

### **Scenario 1: System Admin**

```
User: System Admin
Action: Truy cập /product-templates
Result: 403 Forbidden - "System Admin không có quyền truy cập Product Templates"
```

### **Scenario 2: Team Admin**

```
User: Team Admin
Team: Team A
Templates: [Template 1 (User A), Template 2 (User B), Template 3 (User C)]
Result: Có thể xem, chỉnh sửa, xóa tất cả 3 templates
```

### **Scenario 3: Seller**

```
User: Seller A
Team: Team A
Templates: [Template 1 (User A), Template 2 (User B), Template 3 (User C)]
Result: Chỉ xem và quản lý được Template 1 (do mình tạo)
```

### **Scenario 4: Cross-team Access**

```
User: Seller A (Team A)
Template: Template X (Team B)
Result: 403 Forbidden - "Bạn không có quyền truy cập template này"
```

## 🎨 **UI/UX Changes:**

### **1. Navigation Menu:**

-   System Admin: Ẩn link "Product Templates"
-   Team Admin & Seller: Hiển thị link "Product Templates"

### **2. Dashboard:**

-   System Admin: Ẩn card "Product Templates"
-   Team Admin & Seller: Hiển thị card "Product Templates"

### **3. Template List:**

-   Hiển thị thông tin người tạo template
-   Chỉ hiển thị nút "Chỉnh sửa" nếu có quyền
-   Team Admin thấy cảnh báo "Template của thành viên khác"

### **4. Error Messages:**

-   403: "System Admin không có quyền truy cập Product Templates"
-   403: "Bạn chỉ có thể truy cập template do chính mình tạo"
-   403: "Bạn không có quyền truy cập template này"

## 🔧 **Testing:**

### **1. Test Routes:**

```bash
# System Admin
GET /product-templates → 403 Forbidden

# Team Admin
GET /product-templates → 200 OK (tất cả templates)

# Seller
GET /product-templates → 200 OK (chỉ templates của mình)
```

### **2. Test API:**

```bash
# Test Policy
php artisan tinker
>>> $user = User::find(1);
>>> $template = ProductTemplate::find(1);
>>> $user->can('view', $template);
```

## 🚀 **Benefits (Lợi ích):**

### **1. Security:**

-   Phân quyền rõ ràng theo role
-   Bảo vệ dữ liệu khỏi truy cập trái phép
-   Kiểm tra quyền ở nhiều lớp (Middleware, Policy, Controller)

### **2. User Experience:**

-   UI thích ứng theo quyền
-   Thông báo lỗi rõ ràng
-   Hiển thị thông tin phù hợp

### **3. Maintainability:**

-   Code có cấu trúc rõ ràng
-   Dễ dàng mở rộng quyền
-   Tách biệt logic phân quyền

### **4. Scalability:**

-   Hỗ trợ nhiều team
-   Dễ dàng thêm role mới
-   Performance tốt với indexing

## 🔮 **Future Improvements:**

### **1. Advanced Permissions:**

-   Permission-based thay vì role-based
-   Custom permissions cho từng template
-   Time-based permissions

### **2. Audit Trail:**

-   Log tất cả thao tác CRUD
-   Track người thay đổi template
-   History của template

### **3. Template Sharing:**

-   Chia sẻ template giữa các seller
-   Template collaboration
-   Template approval workflow

### **4. Bulk Operations:**

-   Bulk edit với permission check
-   Bulk delete với confirmation
-   Bulk export theo quyền
