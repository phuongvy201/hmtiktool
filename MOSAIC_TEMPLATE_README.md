# HMTik - Mosaic Lite Template Integration

Dự án này đã được tích hợp với template Mosaic Lite để tạo giao diện admin dashboard hiện đại và đẹp mắt.

## 🎨 Tính năng Template

### Layout Mosaic
- **Sidebar responsive**: Tự động thu gọn trên mobile và có thể mở rộng trên desktop
- **Header với user menu**: Dropdown menu cho profile và logout
- **Color scheme**: Sử dụng palette Slate với các màu sắc chuyên nghiệp
- **Typography**: Font Inter cho giao diện hiện đại

### Components
- **Stats Cards**: Hiển thị thống kê với icons và màu sắc
- **Action Cards**: Cards cho các chức năng chính với hover effects
- **Tables**: Styling cho bảng dữ liệu
- **Forms**: Input fields với validation
- **Buttons**: Các loại button khác nhau (primary, secondary, success, danger)
- **Badges**: Hiển thị trạng thái
- **Modals**: Popup dialogs
- **Toast notifications**: Thông báo tạm thời

### JavaScript Features
- **Alpine.js**: Reactive components và state management
- **Sidebar toggle**: Lưu trạng thái sidebar trong localStorage
- **Form validation**: Validation tự động cho forms
- **Toast notifications**: Hệ thống thông báo
- **Loading states**: Hiển thị trạng thái loading
- **Utility functions**: Format currency, date, debounce, copy to clipboard

## 🚀 Cài đặt và Chạy

### 1. Cài đặt Dependencies
```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node.js dependencies
npm install
```

### 2. Cấu hình Environment
```bash
# Copy file environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Cấu hình database trong .env
```

### 3. Database Setup
```bash
# Chạy migrations
php artisan migrate

# Chạy seeders (nếu có)
php artisan db:seed
```

### 4. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 5. Chạy Server
```bash
php artisan serve
```

## 📁 Cấu trúc Files

```
resources/
├── views/
│   ├── layouts/
│   │   ├── app.blade.php          # Layout cũ
│   │   └── mosaic.blade.php       # Layout Mosaic mới
│   ├── components/
│   │   └── stats-card.blade.php   # Component stats card
│   └── dashboard.blade.php        # Dashboard với template mới
├── css/
│   └── app.css                    # Styles cho Mosaic template
└── js/
    └── app.js                     # JavaScript với Alpine.js
```

## 🎯 Sử dụng Template

### 1. Sử dụng Layout Mosaic
```php
@extends('layouts.mosaic')

@section('title', 'Page Title')

@section('content')
    <!-- Your content here -->
@endsection
```

### 2. Sử dụng Stats Card Component
```php
<x-stats-card 
    title="Total Users" 
    value="{{ $userCount }}" 
    icon="<svg>...</svg>"
    color="blue"
    trend="up"
    trendValue="+12%"
/>
```

### 3. Sử dụng CSS Classes
```html
<!-- Buttons -->
<button class="btn-primary">Primary Button</button>
<button class="btn-secondary">Secondary Button</button>
<button class="btn-success">Success Button</button>
<button class="btn-danger">Danger Button</button>

<!-- Forms -->
<input type="text" class="form-input" placeholder="Enter text">
<label class="form-label">Label</label>

<!-- Tables -->
<div class="table-container">
    <table class="table">
        <!-- Table content -->
    </table>
</div>

<!-- Badges -->
<span class="badge badge-success">Success</span>
<span class="badge badge-warning">Warning</span>
<span class="badge badge-danger">Danger</span>
```

### 4. JavaScript Functions
```javascript
// Show toast notification
window.showToast('Thông báo thành công!', 'success');

// Set loading state
window.setLoading(buttonElement, true);

// Format currency
window.utils.formatCurrency(1000000); // "1.000.000 ₫"

// Format date
window.utils.formatDate('2024-01-01'); // "01/01/2024"

// Copy to clipboard
window.utils.copyToClipboard('Text to copy');
```

## 🎨 Customization

### 1. Thay đổi Color Scheme
Chỉnh sửa file `resources/css/app.css`:
```css
/* Thay đổi primary color */
.btn-primary {
    @apply bg-indigo-500 hover:bg-indigo-600;
}
```

### 2. Thêm Components mới
Tạo file trong `resources/views/components/`:
```php
<!-- resources/views/components/my-component.blade.php -->
@props(['title', 'content'])

<div class="bg-white rounded-lg shadow-sm border border-slate-200 p-6">
    <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ $title }}</h3>
    <div class="text-slate-600">{{ $content }}</div>
</div>
```

### 3. Thêm JavaScript Functions
Thêm vào file `resources/js/app.js`:
```javascript
window.myFunction = function() {
    // Your function logic
};
```

## 📱 Responsive Design

Template được thiết kế responsive với các breakpoints:
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px  
- **Desktop**: > 1024px
- **Large Desktop**: > 1280px

## 🔧 Troubleshooting

### 1. Sidebar không hoạt động
- Kiểm tra Alpine.js đã được load
- Kiểm tra console errors
- Đảm bảo `x-data` attributes được set đúng

### 2. Styles không load
- Chạy `npm run dev` hoặc `npm run build`
- Kiểm tra Vite configuration
- Clear cache: `php artisan cache:clear`

### 3. JavaScript errors
- Kiểm tra browser console
- Đảm bảo Alpine.js được import đúng
- Kiểm tra syntax errors trong `app.js`

## 📚 Resources

- [Mosaic Lite Template](https://github.com/cruip/laravel-tailwindcss-admin-dashboard-template)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Tailwind CSS Documentation](https://tailwindcss.com/)
- [Laravel Documentation](https://laravel.com/docs)

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## 📄 License

This project is licensed under the MIT License.
