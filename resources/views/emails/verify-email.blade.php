<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác thực Email - HMTik</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }
        .title {
            font-size: 20px;
            color: #1f2937;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: #ffffff;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #1d4ed8;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #6b7280;
            font-size: 14px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">HMTik</div>
            <div class="title">Xác thực Email</div>
        </div>

        <div class="content">
            <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
            
            <p>Cảm ơn bạn đã đăng ký tài khoản tại HMTik. Để hoàn tất quá trình đăng ký và kích hoạt tài khoản, vui lòng xác thực email của bạn bằng cách nhấn vào nút bên dưới:</p>

            <div style="text-align: center;">
                <a href="{{ $verificationUrl }}" class="button">Xác thực Email</a>
            </div>

            <p>Hoặc bạn có thể copy và paste link sau vào trình duyệt:</p>
            <p style="word-break: break-all; color: #2563eb;">{{ $verificationUrl }}</p>

            <div class="warning">
                <strong>Lưu ý:</strong> Link xác thực này sẽ hết hạn sau 60 phút. Nếu link đã hết hạn, vui lòng yêu cầu gửi lại email xác thực.
            </div>

            <p>Sau khi xác thực thành công, bạn sẽ có thể:</p>
            <ul>
                <li>Truy cập đầy đủ các tính năng của hệ thống</li>
                <li>Nhận thông báo quan trọng qua email</li>
                <li>Đảm bảo tính bảo mật cho tài khoản</li>
            </ul>

            <p>Nếu bạn không thực hiện đăng ký tài khoản này, vui lòng bỏ qua email này.</p>
        </div>

        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống HMTik</p>
            <p>Vui lòng không trả lời email này</p>
            <p>© {{ date('Y') }} HMTik. Tất cả quyền được bảo lưu.</p>
        </div>
    </div>
</body>
</html>
