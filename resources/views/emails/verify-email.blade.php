<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your email</title>
    <style>
        :root {
            color-scheme: light;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 24px;
            background: #0f172a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #e2e8f0;
        }
        .wrapper {
            max-width: 640px;
            margin: 0 auto;
        }
        .card {
            background: linear-gradient(135deg, #0b1224 0%, #0f172a 40%, #111827 100%);
            border: 1px solid #1f2937;
            border-radius: 18px;
            padding: 28px 28px 24px;
            box-shadow: 0 12px 50px rgba(0,0,0,0.35), 0 1px 0 rgba(255,255,255,0.03);
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 12px;
            background: rgba(59,130,246,0.12);
            color: #bfdbfe;
            font-weight: 700;
            letter-spacing: 0.3px;
            font-size: 15px;
        }
        .title {
            margin: 18px 0 8px;
            font-size: 24px;
            font-weight: 700;
            color: #f8fafc;
        }
        .subtitle {
            color: #cbd5e1;
            font-size: 14px;
            margin: 0;
        }
        .content {
            background: rgba(255,255,255,0.02);
            border: 1px solid #1f2937;
            border-radius: 14px;
            padding: 18px 18px 16px;
            margin-top: 20px;
        }
        .content p {
            margin: 0 0 12px;
            color: #e2e8f0;
            font-size: 14px;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #3b82f6, #6366f1);
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            letter-spacing: 0.2px;
            box-shadow: 0 10px 30px rgba(79,70,229,0.35);
            margin: 10px 0 14px;
        }
        .button:hover {
            background: linear-gradient(135deg, #2563eb, #4f46e5);
        }
        .pill {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 999px;
            background: rgba(59,130,246,0.15);
            color: #bfdbfe;
            font-size: 12px;
            letter-spacing: 0.3px;
            margin-top: 6px;
        }
        .link-box {
            margin-top: 12px;
            background: #0b1220;
            border: 1px solid #1f2937;
            border-radius: 10px;
            padding: 12px;
            font-size: 12px;
            color: #93c5fd;
            word-break: break-all;
        }
        .warning {
            margin-top: 14px;
            padding: 12px;
            border-radius: 10px;
            background: rgba(251,191,36,0.12);
            border: 1px solid rgba(251,191,36,0.35);
            color: #fcd34d;
            font-size: 13px;
        }
        .list {
            margin: 14px 0;
            padding-left: 18px;
            color: #cbd5e1;
            font-size: 13px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #94a3b8;
            font-size: 12px;
        }
        .divider {
            height: 1px;
            background: #1f2937;
            border: 0;
            margin: 18px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                <div class="brand">HMTik • Secure Access</div>
                <div class="title">Verify your email</div>
                <p class="subtitle">Just one click to activate your account</p>
            </div>

            <div class="content">
                <p>Hello <strong>{{ $user->name }}</strong>,</p>
                <p>Thanks for joining HMTik! Please verify your email to activate your account and keep your access secure.</p>

                <div style="text-align:center;">
                    <a href="{{ $verificationUrl }}" class="button">Verify Email</a>
                    <div class="pill">Valid for 60 minutes</div>
                </div>

                <p>If the button does not work, copy and paste this link:</p>
                <div class="link-box">{{ $verificationUrl }}</div>

                <div class="warning">
                    <strong>Heads up:</strong> The link expires in 60 minutes. If it expires, request a new verification email.
                </div>

                <p>After verification you can:</p>
                <ul class="list">
                    <li>Access all HMTik features</li>
                    <li>Receive important notifications</li>
                    <li>Keep your account secure</li>
                </ul>

                <hr class="divider">
                <p style="font-size:12px; color:#94a3b8;">If you did not sign up for HMTik, you can safely ignore this email.</p>
            </div>

            <div class="footer">
                <p>This email was sent automatically by HMTik.</p>
                <p>Please do not reply to this email.</p>
                <p>© {{ date('Y') }} HMTik. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
