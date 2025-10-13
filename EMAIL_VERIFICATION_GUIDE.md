# Hệ thống Xác thực Email - Hướng dẫn chi tiết

## 🎯 Tổng quan

Hệ thống xác thực email được thiết kế để đảm bảo tính bảo mật và xác thực danh tính người dùng. Khi tạo user mới, hệ thống sẽ tự động gửi email xác thực.

## 📧 Quy trình Xác thực Email

### 1. Tạo User mới

-   Khi Team Admin tạo thành viên mới, hệ thống tự động gửi email xác thực
-   User được tạo với trạng thái "Chưa xác thực"

### 2. Gửi Email Xác thực

-   Email chứa link xác thực có hiệu lực 60 phút
-   Template email đẹp mắt với thông tin chi tiết

### 3. Xác thực Email

-   User click vào link trong email
-   Hệ thống kiểm tra token và thời gian hết hạn
-   Cập nhật trạng thái thành "Đã xác thực"

### 4. Gửi lại Email (nếu cần)

-   Nếu link hết hạn, user có thể yêu cầu gửi lại
-   Có cooldown 5 phút để tránh spam

## 🔧 Cấu hình Email

### 1. Cấu hình SMTP trong .env

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### 2. Gmail App Password

-   Bật 2FA cho Gmail
-   Tạo App Password
-   Sử dụng App Password thay vì mật khẩu thường

## 📁 Cấu trúc Files

### Controllers

```
app/Http/Controllers/
└── EmailVerificationController.php    # Xử lý xác thực email
```

### Mail

```
app/Mail/
└── VerifyEmail.php                    # Mail class cho xác thực
```

### Views

```
resources/views/
├── emails/
│   └── verify-email.blade.php         # Template email
└── auth/
    ├── verify-email.blade.php         # Form gửi email xác thực
    └── resend-verification.blade.php  # Form gửi lại email
```

### Database

```sql
-- Thêm vào bảng users
ALTER TABLE users ADD COLUMN email_verification_token VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN email_verification_expires_at TIMESTAMP NULL;
```

## 🚀 Cách sử dụng

### 1. Chạy Migration

```bash
php artisan migrate
```

### 2. Cấu hình Email

-   Cập nhật file `.env` với thông tin SMTP
-   Test gửi email

### 3. Tạo User mới

-   Team Admin tạo thành viên mới
-   Hệ thống tự động gửi email xác thực

### 4. Xác thực Email

-   User nhận email và click link xác thực
-   Hoặc truy cập `/email/verify` để gửi lại

## 📧 Template Email

### Nội dung Email

-   **Header**: Logo HMTik và tiêu đề
-   **Nội dung**: Thông tin user và hướng dẫn
-   **Button**: Link xác thực nổi bật
-   **Cảnh báo**: Thời gian hết hạn
-   **Footer**: Thông tin hệ thống

### Tính năng

-   Responsive design
-   Dark theme phù hợp
-   Thông tin chi tiết và rõ ràng
-   Cảnh báo về thời gian hết hạn

## 🔒 Bảo mật

### Token Security

-   Token 64 ký tự ngẫu nhiên
-   Thời gian hết hạn 60 phút
-   Sử dụng signed URLs

### Rate Limiting

-   Cooldown 5 phút cho việc gửi lại
-   Tránh spam và tấn công

### Validation

-   Kiểm tra email tồn tại
-   Kiểm tra trạng thái đã xác thực
-   Kiểm tra token hợp lệ

## 🎨 Giao diện

### Form Gửi Email Xác thực

-   **URL**: `/email/verify`
-   **Design**: Dark theme với blue accent
-   **Features**: Validation, error handling

### Form Gửi lại Email

-   **URL**: `/email/resend`
-   **Design**: Dark theme với yellow accent
-   **Features**: Cooldown protection

### Trạng thái User

-   **Chưa xác thực**: Badge màu vàng
-   **Đã xác thực**: Badge màu xanh
-   **Hiển thị**: Trong danh sách team members

## 📊 Routes

```php
// Email Verification Routes
Route::get('/email/verify', [EmailVerificationController::class, 'showVerificationForm'])->name('verification.notice');
Route::post('/email/verify', [EmailVerificationController::class, 'sendVerificationEmail'])->name('verification.send');
Route::get('/email/verify/{id}/{token}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
Route::get('/email/resend', [EmailVerificationController::class, 'showResendForm'])->name('verification.resend.form');
Route::post('/email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');
```

## 🔧 Troubleshooting

### Email không gửi được

1. **Kiểm tra cấu hình SMTP**

    - MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD
    - MAIL_ENCRYPTION (tls/ssl)

2. **Gmail App Password**

    - Bật 2FA
    - Tạo App Password
    - Sử dụng App Password

3. **Kiểm tra logs**
    ```bash
    tail -f storage/logs/laravel.log
    ```

### Link xác thực không hoạt động

1. **Kiểm tra URL**

    - Đảm bảo APP_URL đúng trong .env
    - Kiểm tra signed URL

2. **Kiểm tra token**

    - Token có trong database không
    - Token có hết hạn không

3. **Kiểm tra route**
    - Route đã đăng ký chưa
    - Middleware có chặn không

### User không nhận được email

1. **Kiểm tra spam folder**
2. **Kiểm tra email address**
3. **Test gửi email**
    ```bash
    php artisan tinker
    Mail::raw('Test email', function($message) { $message->to('test@example.com')->subject('Test'); });
    ```

## 📝 Best Practices

### 1. Error Handling

```php
try {
    Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
} catch (\Exception $e) {
    Log::error('Failed to send verification email: ' . $e->getMessage());
    // Don't fail user creation
}
```

### 2. Rate Limiting

```php
if ($user->email_verification_expires_at && $user->email_verification_expires_at > now()->subMinutes(5)) {
    return back()->with('error', 'Vui lòng đợi 5 phút trước khi yêu cầu gửi lại.');
}
```

### 3. Token Security

```php
$token = Str::random(64);
$user->update([
    'email_verification_token' => $token,
    'email_verification_expires_at' => now()->addHours(1),
]);
```

### 4. User Experience

-   Clear error messages
-   Helpful instructions
-   Responsive design
-   Loading states

## 🎉 Kết luận

Hệ thống xác thực email cung cấp:

✅ **Bảo mật cao** với token và thời gian hết hạn  
✅ **UX tốt** với template email đẹp và giao diện thân thiện  
✅ **Tự động hóa** gửi email khi tạo user mới  
✅ **Flexible** có thể gửi lại email nếu cần  
✅ **Robust** với error handling và logging

Hệ thống đảm bảo tính bảo mật và trải nghiệm người dùng tốt! 🚀
