<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\AvatarUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use App\Models\User;
use App\Models\SystemSetting;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $user->load('roles', 'team');

        // Lấy thông tin cấu hình hệ thống
        $systemSettings = [
            'app_name' => SystemSetting::getValue('app_name', 'HMTIK'),
            'password_min_length' => SystemSetting::getValue('password_min_length', 8),
            'session_timeout' => SystemSetting::getValue('session_timeout', 120),
        ];

        return view('profile.edit', compact('user', 'systemSettings'));
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Cập nhật thông tin cơ bản
        $user->fill($request->validated());

        // Kiểm tra nếu email thay đổi
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
            // Có thể gửi email xác thực lại ở đây
        }

        $user->save();

        return Redirect::route('profile.edit')->with('success', 'Thông tin cá nhân đã được cập nhật thành công.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không đúng.']);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        // Logout tất cả session khác (optional)
        Auth::logoutOtherDevices($request->password);

        return Redirect::route('profile.edit')->with('success', 'Mật khẩu đã được cập nhật thành công.');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(AvatarUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        if ($request->hasFile('avatar')) {
            // Xóa avatar cũ nếu có
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Lưu avatar mới
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            return Redirect::route('profile.edit')->with('success', 'Ảnh đại diện đã được cập nhật thành công.');
        }

        return Redirect::route('profile.edit')->with('error', 'Không có file ảnh được chọn.');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
            $user->avatar = null;
            $user->save();

            return Redirect::route('profile.edit')->with('success', 'Ảnh đại diện đã được xóa.');
        }

        return Redirect::route('profile.edit')->with('error', 'Không có ảnh đại diện để xóa.');
    }

    /**
     * Display user's activity log.
     */
    public function activity(Request $request): View
    {
        $user = $request->user();

        // Lấy hoạt động gần đây (có thể implement sau)
        $activities = collect(); // Placeholder cho activity log

        return view('profile.activity', compact('user', 'activities'));
    }

    /**
     * Display user's security settings.
     */
    public function security(Request $request): View
    {
        $user = $request->user();

        // Lấy thông tin bảo mật
        $securityInfo = [
            'last_login' => $user->last_login_at ?? 'Chưa đăng nhập',
            'login_count' => $user->login_count ?? 0,
            'two_factor_enabled' => $user->two_factor_enabled ?? false,
            'email_verified' => !is_null($user->email_verified_at),
        ];

        return view('profile.security', compact('user', 'securityInfo'));
    }

    /**
     * Enable/disable two-factor authentication.
     */
    public function toggleTwoFactor(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->two_factor_enabled = !$user->two_factor_enabled;
        $user->save();

        $status = $user->two_factor_enabled ? 'bật' : 'tắt';
        return Redirect::route('profile.security')->with('success', "Xác thực 2 yếu tố đã được {$status}.");
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Mật khẩu không đúng.']);
        }

        // Xóa avatar nếu có
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Xóa tài khoản
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Show user's notifications.
     */
    public function notifications(Request $request): View
    {
        $user = $request->user();

        // Lấy thông báo (có thể implement sau)
        $notifications = collect(); // Placeholder cho notifications

        return view('profile.notifications', compact('user', 'notifications'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationAsRead(Request $request, $id): RedirectResponse
    {
        // Implement notification marking logic
        return Redirect::route('profile.notifications')->with('success', 'Đã đánh dấu thông báo là đã đọc.');
    }

    /**
     * Export user data.
     */
    public function exportData(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Implement data export logic
        // Có thể tạo job để export data

        return Redirect::route('profile.edit')->with('success', 'Yêu cầu xuất dữ liệu đã được gửi. Bạn sẽ nhận được email khi hoàn thành.');
    }
}
