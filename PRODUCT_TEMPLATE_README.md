# Product Template Management

## Tổng quan

Chức năng Product Template cho phép bạn tạo các template sản phẩm để tái sử dụng khi đăng sản phẩm lên TikTok Shop. Template chứa các thông tin cơ bản của sản phẩm để bạn không cần phải nhập lại các thông tin giống nhau khi đăng cùng một loại sản phẩm.

## Tính năng chính

### 1. Thông tin cơ bản

-   **Product Name**: Tên sản phẩm
-   **Base Price**: Giá cơ bản
-   **List Price**: Giá niêm yết
-   **Weight**: Trọng lượng (kg)
-   **Height, Width, Length**: Kích thước (cm)
-   **Description**: Mô tả sản phẩm
-   **Category**: Danh mục sản phẩm
-   **Images**: Hình ảnh sản phẩm (URLs)
-   **Size Chart**: Bảng size (URL)
-   **Product Video**: Video sản phẩm (URL)

### 2. General Attributes

-   Các thuộc tính chung tùy theo danh mục sản phẩm
-   Được thêm động dựa trên category được chọn

### 3. Product Options & Variants

-   **Options**: Các tùy chọn như Color, Size, Type
-   **Values**: Các giá trị tương ứng (Black, White), (S, M, L), (Hoodie, Sweatshirt)
-   **Variants**: Tự động tạo các combination (Black/S/Hoodie)
-   **Variant Management**:
    -   Price, List Price, Quantity cho từng variant
    -   Images riêng cho từng variant
    -   Set Bulk Price cho nhiều variants cùng lúc
    -   Remove variant

## Cấu trúc Database

### Bảng chính

1. **product_templates**: Template chính
2. **product_template_options**: Các option (Color, Size, Type)
3. **product_template_option_values**: Các giá trị của option (Black, White, S, M, L)
4. **product_template_variants**: Các variant được tạo từ combination
5. **product_template_variant_options**: Bảng pivot liên kết variant với option values

## Cách sử dụng

### 1. Tạo Template mới

1. Vào menu "Product Templates"
2. Click "Tạo Template Mới"
3. Điền thông tin cơ bản:
    - Tên sản phẩm
    - Danh mục
    - Giá cơ bản và giá niêm yết
    - Kích thước và trọng lượng
    - Hình ảnh và video
4. Thêm General Attributes (tùy chọn)
5. Thêm Product Options:
    - Click "+ Thêm tùy chọn"
    - Nhập tên option (VD: Color)
    - Nhập các giá trị phân cách bằng dấu phẩy (VD: Black, White, Red)
6. Xem trước variants sẽ được tạo
7. Click "Tạo Template"

### 2. Quản lý Variants

1. Vào chi tiết template
2. Trong phần "Quản lý Variants":
    - Chỉnh sửa giá, số lượng cho từng variant
    - Chọn nhiều variants và click "Set Bulk Price"
    - Xóa variant không cần thiết

### 3. Chỉnh sửa Template

1. Vào chi tiết template
2. Click "Chỉnh sửa"
3. Cập nhật thông tin cần thiết
4. Lưu thay đổi

## Ví dụ sử dụng

### Template Áo Hoodie

-   **Tên**: "Áo Hoodie Basic"
-   **Category**: "Clothing"
-   **Base Price**: 200,000 VNĐ
-   **Options**:
    -   Color: Black, White, Gray
    -   Size: S, M, L, XL
    -   Type: Hoodie, Sweatshirt
-   **Variants được tạo**: 24 variants (3 x 4 x 2)
    -   Black/S/Hoodie
    -   Black/S/Sweatshirt
    -   White/M/Hoodie
    -   ...

### Set Bulk Price

-   Chọn các variants Black/S, Black/M, Black/L
-   Click "Set Bulk Price"
-   Nhập giá: 180,000 VNĐ
-   Tất cả variants được chọn sẽ có giá 180,000 VNĐ

## API Endpoints

### CRUD Operations

-   `GET /product-templates` - Danh sách templates
-   `GET /product-templates/create` - Form tạo template
-   `POST /product-templates` - Tạo template mới
-   `GET /product-templates/{id}` - Chi tiết template
-   `GET /product-templates/{id}/edit` - Form chỉnh sửa
-   `PUT /product-templates/{id}` - Cập nhật template
-   `DELETE /product-templates/{id}` - Xóa template

### Variant Management

-   `POST /product-templates/{id}/update-variants` - Cập nhật variants
-   `POST /product-templates/{id}/set-bulk-price` - Set bulk price

## Cài đặt

1. Chạy migrations:

```bash
php artisan migrate
```

2. Đảm bảo đã có các bảng cần thiết:

-   product_templates
-   product_template_options
-   product_template_option_values
-   product_template_variants
-   product_template_variant_options

3. Truy cập vào menu "Product Templates" để bắt đầu sử dụng

## Lưu ý

-   Template được liên kết với Team, mỗi team chỉ thấy templates của mình
-   Variants được tạo tự động dựa trên các options đã định nghĩa
-   Có thể chỉnh sửa giá và số lượng cho từng variant riêng biệt
-   Chức năng Set Bulk Price giúp cập nhật giá hàng loạt nhanh chóng
-   General Attributes sẽ được mở rộng theo category trong tương lai

