# Hệ thống Quản lý Sản phẩm - Product Management System

## Tổng quan

Hệ thống quản lý sản phẩm được thiết kế để quản lý sản phẩm theo role level team, với khả năng tích hợp với Product Templates để tính toán giá tự động.

## Tính năng chính

### 1. Quản lý Sản phẩm

-   **Tạo sản phẩm mới**: Với các trường cơ bản như title, description, SKU, price, product_image
-   **Chỉnh sửa sản phẩm**: Cập nhật thông tin sản phẩm
-   **Xem chi tiết sản phẩm**: Hiển thị đầy đủ thông tin và giá tính toán
-   **Xóa sản phẩm**: Xóa sản phẩm khỏi hệ thống
-   **Thay đổi trạng thái**: Kích hoạt/vô hiệu hóa sản phẩm

### 2. Tích hợp với Product Templates

-   **Tự động tính giá**: Giá sản phẩm = Giá sản phẩm + Giá template
-   **Xem trước giá**: Hiển thị giá tính toán real-time khi tạo/chỉnh sửa
-   **Quản lý template**: Liên kết sản phẩm với template có sẵn

### 3. Phân quyền theo Role

-   **System Admin**: Toàn quyền quản lý
-   **Manager**: Toàn quyền quản lý
-   **Team Admin**: Quản lý sản phẩm trong team
-   **Seller**: Xem, tạo, chỉnh sửa sản phẩm
-   **Accountant**: Chỉ xem sản phẩm
-   **Fulfill**: Xem và chỉnh sửa sản phẩm
-   **Viewer**: Chỉ xem sản phẩm

## Cấu trúc Database

### Bảng `products`

```sql
- id (Primary Key)
- user_id (Foreign Key -> users)
- team_id (Foreign Key -> teams)
- product_template_id (Foreign Key -> product_templates)
- title (String)
- description (Text, nullable)
- sku (String, unique)
- price (Decimal)
- product_image (String, nullable)
- status (Enum: draft, active, inactive)
- is_active (Boolean)
- created_at, updated_at
```

## API Endpoints

### Sản phẩm

-   `GET /products` - Danh sách sản phẩm
-   `GET /products/create` - Form tạo sản phẩm
-   `POST /products` - Tạo sản phẩm mới
-   `GET /products/{id}` - Chi tiết sản phẩm
-   `GET /products/{id}/edit` - Form chỉnh sửa
-   `PUT /products/{id}` - Cập nhật sản phẩm
-   `DELETE /products/{id}` - Xóa sản phẩm
-   `POST /products/{id}/toggle-status` - Thay đổi trạng thái
-   `GET /products/by-template` - Lấy sản phẩm theo template

## Sử dụng trong Dashboard

### Component Product Management

Component `resources/views/components/product-management.blade.php` được tích hợp vào tất cả các dashboard:

-   **Team Admin Dashboard**: Quản lý đầy đủ
-   **Manager Dashboard**: Quản lý đầy đủ
-   **User Dashboard**: Quản lý theo quyền
-   **Viewer Dashboard**: Chỉ xem
-   **System Admin Dashboard**: Quản lý toàn hệ thống

### Thống kê hiển thị

-   Số lượng templates trong team
-   Số lượng sản phẩm trong team
-   Truy cập nhanh đến các chức năng

## Tính năng đặc biệt

### 1. Tính giá tự động

```php
// Trong Product Model
public function getTotalPriceAttribute()
{
    $templatePrice = $this->productTemplate ? $this->productTemplate->base_price : 0;
    return $this->price + $templatePrice;
}
```

### 2. Upload ảnh sản phẩm

-   Hỗ trợ định dạng: JPEG, PNG, JPG, GIF
-   Kích thước tối đa: 2MB
-   Lưu trữ trong thư mục `storage/app/public/products/`

### 3. Tìm kiếm và lọc

-   Tìm kiếm theo tên, SKU, mô tả
-   Lọc theo trạng thái (draft, active, inactive)
-   Lọc theo template
-   Phân trang tự động

## Cài đặt và Chạy

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Chạy Seeder

```bash
php artisan db:seed --class=ProductPermissionsSeeder
```

### 3. Tạo Storage Link (nếu chưa có)

```bash
php artisan storage:link
```

## Quyền truy cập

### Permissions được tạo:

-   `view-products` - Xem sản phẩm
-   `create-products` - Tạo sản phẩm
-   `update-products` - Chỉnh sửa sản phẩm
-   `delete-products` - Xóa sản phẩm
-   `view-product-templates` - Xem templates

### Middleware sử dụng:

-   `auth` - Yêu cầu đăng nhập
-   `permission:view-products` - Kiểm tra quyền xem
-   `permission:create-products` - Kiểm tra quyền tạo
-   `permission:update-products` - Kiểm tra quyền chỉnh sửa
-   `permission:delete-products` - Kiểm tra quyền xóa

## Giao diện

### 1. Danh sách sản phẩm (`/products`)

-   Bảng hiển thị với thông tin cơ bản
-   Hình ảnh thumbnail
-   Trạng thái với màu sắc
-   Giá tính toán tự động
-   Các nút thao tác theo quyền

### 2. Form tạo/chỉnh sửa

-   Layout 2 cột responsive
-   Upload ảnh với preview
-   Xem trước giá tính toán
-   Validation real-time
-   Tích hợp với templates

### 3. Chi tiết sản phẩm

-   Hiển thị đầy đủ thông tin
-   Ảnh sản phẩm lớn
-   Thông tin template liên kết
-   Lịch sử tạo/cập nhật

## Bảo mật

### 1. Kiểm tra quyền

-   Mỗi action đều kiểm tra permission
-   Chỉ hiển thị nút theo quyền
-   Redirect nếu không có quyền

### 2. Validation

-   Validate dữ liệu đầu vào
-   Kiểm tra SKU unique
-   Validate file upload
-   Sanitize dữ liệu

### 3. Team Isolation

-   Sản phẩm chỉ thuộc về team
-   Không thể truy cập sản phẩm team khác
-   Kiểm tra team_id trong mọi query

## Troubleshooting

### Lỗi thường gặp:

1. **Lỗi permission**: Chạy lại seeder
2. **Lỗi upload ảnh**: Kiểm tra storage link
3. **Lỗi template**: Đảm bảo template tồn tại và active
4. **Lỗi team**: Kiểm tra user đã được assign team

### Debug:

```bash
# Xem logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## Tương lai

### Tính năng dự kiến:

-   Import/Export sản phẩm
-   Bulk operations
-   Advanced filtering
-   Product variants
-   Inventory management
-   Integration với e-commerce platforms
