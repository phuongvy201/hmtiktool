<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemSetting;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app_name',
                'value' => 'HMTIK - TikTok Shop Management',
                'type' => 'string',
                'description' => 'Tên hiển thị của ứng dụng',
            ],
            [
                'key' => 'app_description',
                'value' => 'Hệ thống quản lý TikTok Shop fulfillment',
                'type' => 'string',
                'description' => 'Mô tả ngắn về ứng dụng',
            ],
            [
                'key' => 'maintenance_mode',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Bật/tắt chế độ bảo trì hệ thống',
            ],

            // User Settings
            [
                'key' => 'user_registration_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Cho phép người dùng tự đăng ký tài khoản',
            ],
            [
                'key' => 'email_verification_required',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Yêu cầu xác thực email khi đăng ký',
            ],
            [
                'key' => 'password_min_length',
                'value' => '8',
                'type' => 'integer',
                'description' => 'Độ dài tối thiểu của mật khẩu',
            ],
            [
                'key' => 'session_timeout',
                'value' => '120',
                'type' => 'integer',
                'description' => 'Thời gian tự động đăng xuất (phút)',
            ],

            // Timezone Settings
            [
                'key' => 'default_timezone',
                'value' => 'Asia/Ho_Chi_Minh',
                'type' => 'string',
                'description' => 'Múi giờ mặc định của hệ thống',
            ],
            [
                'key' => 'date_format',
                'value' => 'd/m/Y',
                'type' => 'string',
                'description' => 'Định dạng hiển thị ngày tháng',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i:s',
                'type' => 'string',
                'description' => 'Định dạng hiển thị thời gian',
            ],

            // Email Settings
            [
                'key' => 'mail_from_address',
                'value' => 'noreply@hmtik.com',
                'type' => 'string',
                'description' => 'Địa chỉ email gửi từ',
            ],
            [
                'key' => 'mail_from_name',
                'value' => 'HMTIK System',
                'type' => 'string',
                'description' => 'Tên hiển thị khi gửi email',
            ],

            // TikTok Shop Settings
            [
                'key' => 'tiktok_api_timeout',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Thời gian timeout cho API TikTok (giây)',
            ],
            [
                'key' => 'tiktok_max_retries',
                'value' => '3',
                'type' => 'integer',
                'description' => 'Số lần thử lại tối đa cho API TikTok',
            ],
            [
                'key' => 'tiktok_rate_limit',
                'value' => '100',
                'type' => 'integer',
                'description' => 'Giới hạn rate limit cho API TikTok (requests/phút)',
            ],

            // Backup Settings
            [
                'key' => 'backup_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Bật/tắt tính năng backup tự động',
            ],
            [
                'key' => 'backup_frequency',
                'value' => 'daily',
                'type' => 'string',
                'description' => 'Tần suất backup (daily, weekly, monthly)',
            ],
            [
                'key' => 'backup_retention_days',
                'value' => '30',
                'type' => 'integer',
                'description' => 'Số ngày lưu trữ backup',
            ],

            // Security Settings
            [
                'key' => 'two_factor_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Bật/tắt xác thực 2 yếu tố',
            ],
            [
                'key' => 'login_attempts_limit',
                'value' => '5',
                'type' => 'integer',
                'description' => 'Số lần đăng nhập sai tối đa trước khi khóa',
            ],
            [
                'key' => 'lockout_duration',
                'value' => '15',
                'type' => 'integer',
                'description' => 'Thời gian khóa tài khoản (phút)',
            ],

            // Notification Settings
            [
                'key' => 'email_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Bật/tắt thông báo qua email',
            ],
            [
                'key' => 'sms_notifications_enabled',
                'value' => '0',
                'type' => 'boolean',
                'description' => 'Bật/tắt thông báo qua SMS',
            ],
            [
                'key' => 'push_notifications_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Bật/tắt thông báo push',
            ],

            // Performance Settings
            [
                'key' => 'cache_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Bật/tắt cache hệ thống',
            ],
            [
                'key' => 'cache_ttl',
                'value' => '3600',
                'type' => 'integer',
                'description' => 'Thời gian cache (giây)',
            ],
            [
                'key' => 'queue_enabled',
                'value' => '1',
                'type' => 'boolean',
                'description' => 'Bật/tắt queue system',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::create($setting);
        }
    }
}
