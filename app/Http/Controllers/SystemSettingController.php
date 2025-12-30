<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:system-admin');
    }

    /**
     * Display the system settings page
     */
    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'general');

        $groups = [
            'general' => 'Cấu hình chung',
            'user' => 'Cấu hình người dùng',
            'timezone' => 'Múi giờ',
            'api' => 'Cấu hình API',
            'smtp' => 'Cấu hình SMTP',
            'log' => 'Cấu hình Log',
        ];

        $settings = SystemSetting::getByGroup($activeTab);

        return view('system-settings.index', compact('settings', 'groups', 'activeTab'));
    }

    /**
     * Update system settings
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable',
            'settings.*.type' => 'required|in:string,boolean,integer,json,array',
        ]);

        try {
            $maintenanceModeChanged = false;
            $oldMaintenanceMode = SystemSetting::getValue('maintenance_mode', false);

            foreach ($request->settings as $settingData) {
                $setting = SystemSetting::where('key', $settingData['key'])->first();

                if ($setting) {
                    // Check if maintenance_mode is being changed
                    if ($setting->key === 'maintenance_mode') {
                        $newMaintenanceMode = filter_var($settingData['value'] ?? '0', FILTER_VALIDATE_BOOLEAN);
                        if ($oldMaintenanceMode != $newMaintenanceMode) {
                            $maintenanceModeChanged = true;
                        }
                    }

                    $setting->typed_value = $settingData['value'] ?? null;
                    $setting->save();
                }
            }

            // Handle maintenance mode change
            if ($maintenanceModeChanged) {
                $newMaintenanceMode = SystemSetting::getValue('maintenance_mode', false);
                try {
                    if ($newMaintenanceMode) {
                        // Enable maintenance mode
                        Artisan::call('down', [
                            '--retry' => 60, // Retry after 60 seconds
                            '--secret' => env('MAINTENANCE_SECRET', 'maintenance-secret-key'),
                        ]);
                        Log::info('Maintenance mode enabled by user: ' . Auth::user()->id);
                    } else {
                        // Disable maintenance mode
                        Artisan::call('up');
                        Log::info('Maintenance mode disabled by user: ' . Auth::user()->id);
                    }
                } catch (\Exception $e) {
                    Log::error('Error toggling maintenance mode: ' . $e->getMessage());
                    // Continue with the update even if maintenance mode toggle fails
                }
            }

            // Clear cache
            Cache::forget('system_settings');

            Log::info('System settings updated by user: ' . Auth::user()->id);

            $message = 'Cấu hình hệ thống đã được cập nhật thành công.';
            if ($maintenanceModeChanged) {
                $newMaintenanceMode = SystemSetting::getValue('maintenance_mode', false);
                $message .= $newMaintenanceMode
                    ? ' Chế độ bảo trì đã được bật. Hệ thống hiện đang trong chế độ bảo trì.'
                    : ' Chế độ bảo trì đã được tắt. Hệ thống đã hoạt động bình thường.';
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error updating system settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật cấu hình hệ thống.');
        }
    }

    /**
     * Reset settings to default
     */
    public function reset(Request $request)
    {
        $group = $request->get('group', 'general');

        try {
            // Delete existing settings for the group
            SystemSetting::where('group', $group)->delete();

            // Re-seed default settings for the group
            $this->seedDefaultSettings($group);

            // Clear cache
            Cache::forget('system_settings');

            Log::info("System settings reset for group: {$group} by user: " . Auth::user()->id);

            return redirect()->back()->with('success', "Cấu hình {$group} đã được đặt lại về mặc định.");
        } catch (\Exception $e) {
            Log::error('Error resetting system settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi đặt lại cấu hình.');
        }
    }

    /**
     * Export settings
     */
    public function export()
    {
        $settings = SystemSetting::all();

        $data = $settings->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'group' => $setting->group,
                'label' => $setting->label,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
            ];
        });

        return response()->json($data);
    }

    /**
     * Import settings
     */
    public function import(Request $request)
    {
        $request->validate([
            'settings_file' => 'required|file|mimes:json',
        ]);

        try {
            $content = file_get_contents($request->file('settings_file')->getRealPath());
            $settings = json_decode($content, true);

            foreach ($settings as $settingData) {
                SystemSetting::updateOrCreate(
                    ['key' => $settingData['key']],
                    [
                        'value' => $settingData['value'] ?? null,
                        'type' => $settingData['type'] ?? 'string',
                        'group' => $settingData['group'] ?? 'general',
                        'label' => $settingData['label'] ?? '',
                        'description' => $settingData['description'] ?? null,
                        'is_public' => $settingData['is_public'] ?? false,
                    ]
                );
            }

            // Clear cache
            Cache::forget('system_settings');

            Log::info('System settings imported by user: ' . Auth::user()->id);

            return redirect()->back()->with('success', 'Cấu hình hệ thống đã được import thành công.');
        } catch (\Exception $e) {
            Log::error('Error importing system settings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi import cấu hình.');
        }
    }

    /**
     * Get system information
     */
    public function systemInfo()
    {
        $info = [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug_mode' => config('app.debug'),
            'maintenance_mode' => app()->isDownForMaintenance(),
            'storage_path' => storage_path(),
            'public_path' => public_path(),
            'base_path' => base_path(),
        ];

        return response()->json($info);
    }

    /**
     * Seed default settings for a group
     */
    private function seedDefaultSettings(string $group): void
    {
        $defaults = $this->getDefaultSettings($group);

        foreach ($defaults as $setting) {
            SystemSetting::create($setting);
        }
    }

    /**
     * Get default settings by group
     */
    private function getDefaultSettings(string $group): array
    {
        $defaults = [
            'general' => [
                [
                    'key' => 'app_name',
                    'value' => 'HMTIK - TikTok Shop Management',
                    'type' => 'string',
                    'group' => 'general',
                    'label' => 'Tên ứng dụng',
                    'description' => 'Tên hiển thị của ứng dụng',
                    'is_public' => true,
                ],
                [
                    'key' => 'app_description',
                    'value' => 'Hệ thống quản lý TikTok Shop fulfillment',
                    'type' => 'string',
                    'group' => 'general',
                    'label' => 'Mô tả ứng dụng',
                    'description' => 'Mô tả ngắn về ứng dụng',
                    'is_public' => true,
                ],
                [
                    'key' => 'maintenance_mode',
                    'value' => '0',
                    'type' => 'boolean',
                    'group' => 'general',
                    'label' => 'Chế độ bảo trì',
                    'description' => 'Bật/tắt chế độ bảo trì hệ thống',
                    'is_public' => false,
                ],
            ],
            'user' => [
                [
                    'key' => 'user_registration_enabled',
                    'value' => '0',
                    'type' => 'boolean',
                    'group' => 'user',
                    'label' => 'Cho phép đăng ký',
                    'description' => 'Cho phép người dùng tự đăng ký tài khoản',
                    'is_public' => false,
                ],
                [
                    'key' => 'email_verification_required',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'user',
                    'label' => 'Yêu cầu xác thực email',
                    'description' => 'Yêu cầu xác thực email khi đăng ký',
                    'is_public' => false,
                ],
                [
                    'key' => 'password_min_length',
                    'value' => '8',
                    'type' => 'integer',
                    'group' => 'user',
                    'label' => 'Độ dài mật khẩu tối thiểu',
                    'description' => 'Độ dài tối thiểu của mật khẩu',
                    'is_public' => false,
                ],
                [
                    'key' => 'session_timeout',
                    'value' => '120',
                    'type' => 'integer',
                    'group' => 'user',
                    'label' => 'Thời gian timeout session (phút)',
                    'description' => 'Thời gian tự động đăng xuất (phút)',
                    'is_public' => false,
                ],
            ],
            'timezone' => [
                [
                    'key' => 'default_timezone',
                    'value' => 'Asia/Ho_Chi_Minh',
                    'type' => 'string',
                    'group' => 'timezone',
                    'label' => 'Múi giờ mặc định',
                    'description' => 'Múi giờ mặc định của hệ thống',
                    'is_public' => true,
                ],
                [
                    'key' => 'date_format',
                    'value' => 'd/m/Y',
                    'type' => 'string',
                    'group' => 'timezone',
                    'label' => 'Định dạng ngày',
                    'description' => 'Định dạng hiển thị ngày tháng',
                    'is_public' => true,
                ],
                [
                    'key' => 'time_format',
                    'value' => 'H:i:s',
                    'type' => 'string',
                    'group' => 'timezone',
                    'label' => 'Định dạng giờ',
                    'description' => 'Định dạng hiển thị giờ phút giây',
                    'is_public' => true,
                ],
            ],
            'api' => [
                [
                    'key' => 'api_rate_limit',
                    'value' => '60',
                    'type' => 'integer',
                    'group' => 'api',
                    'label' => 'Giới hạn API rate (phút)',
                    'description' => 'Số lượng request tối đa trong 1 phút',
                    'is_public' => false,
                ],
                [
                    'key' => 'api_timeout',
                    'value' => '30',
                    'type' => 'integer',
                    'group' => 'api',
                    'label' => 'Timeout API (giây)',
                    'description' => 'Thời gian timeout cho API calls',
                    'is_public' => false,
                ],
                [
                    'key' => 'api_documentation_enabled',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'api',
                    'label' => 'Bật tài liệu API',
                    'description' => 'Cho phép truy cập tài liệu API',
                    'is_public' => false,
                ],
            ],
            'smtp' => [
                [
                    'key' => 'smtp_host',
                    'value' => 'smtp.gmail.com',
                    'type' => 'string',
                    'group' => 'smtp',
                    'label' => 'SMTP Host',
                    'description' => 'Địa chỉ SMTP server',
                    'is_public' => false,
                ],
                [
                    'key' => 'smtp_port',
                    'value' => '587',
                    'type' => 'integer',
                    'group' => 'smtp',
                    'label' => 'SMTP Port',
                    'description' => 'Port của SMTP server',
                    'is_public' => false,
                ],
                [
                    'key' => 'smtp_encryption',
                    'value' => 'tls',
                    'type' => 'string',
                    'group' => 'smtp',
                    'label' => 'Mã hóa SMTP',
                    'description' => 'Loại mã hóa (tls, ssl, null)',
                    'is_public' => false,
                ],
                [
                    'key' => 'smtp_username',
                    'value' => '',
                    'type' => 'string',
                    'group' => 'smtp',
                    'label' => 'SMTP Username',
                    'description' => 'Tên đăng nhập SMTP',
                    'is_public' => false,
                ],
                [
                    'key' => 'smtp_password',
                    'value' => '',
                    'type' => 'string',
                    'group' => 'smtp',
                    'label' => 'SMTP Password',
                    'description' => 'Mật khẩu SMTP',
                    'is_public' => false,
                ],
            ],
            'log' => [
                [
                    'key' => 'log_level',
                    'value' => 'info',
                    'type' => 'string',
                    'group' => 'log',
                    'label' => 'Mức độ log',
                    'description' => 'Mức độ log của hệ thống (debug, info, warning, error)',
                    'is_public' => false,
                ],
                [
                    'key' => 'log_retention_days',
                    'value' => '30',
                    'type' => 'integer',
                    'group' => 'log',
                    'label' => 'Thời gian lưu log (ngày)',
                    'description' => 'Số ngày lưu trữ log files',
                    'is_public' => false,
                ],
                [
                    'key' => 'log_user_activities',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'log',
                    'label' => 'Log hoạt động người dùng',
                    'description' => 'Ghi log các hoạt động của người dùng',
                    'is_public' => false,
                ],
                [
                    'key' => 'log_api_calls',
                    'value' => '1',
                    'type' => 'boolean',
                    'group' => 'log',
                    'label' => 'Log API calls',
                    'description' => 'Ghi log các API calls',
                    'is_public' => false,
                ],
            ],
        ];

        return $defaults[$group] ?? [];
    }
}
