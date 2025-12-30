<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\PasswordUpdateRequest;
use App\Http\Requests\AvatarUpdateRequest;
use App\Services\EmailVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SystemSetting;

class ProfileController extends Controller
{
    private EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Refresh để luôn lấy trạng thái mới nhất (email_verified_at, avatar, ...).
        $user = $request->user()->fresh(['roles', 'team', 'tiktokMarkets']);
        $primaryMarket = $user->getPrimaryTikTokMarket();
        $userMarkets = $user->getTikTokMarkets();

        // Lấy thông tin cấu hình hệ thống
        $systemSettings = [
            'app_name' => SystemSetting::getValue('app_name', 'HMTIK'),
            'password_min_length' => SystemSetting::getValue('password_min_length', 8),
            'session_timeout' => SystemSetting::getValue('session_timeout', 120),
        ];

        return view('profile.edit', compact('user', 'systemSettings', 'primaryMarket', 'userMarkets'));
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

        return Redirect::route('profile.edit')->with('success', 'Profile information updated successfully.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(PasswordUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();

        // Kiểm tra mật khẩu hiện tại
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Cập nhật mật khẩu mới
        $user->password = Hash::make($request->password);
        $user->save();

        // Logout tất cả session khác (optional)
        Auth::logoutOtherDevices($request->password);

        return Redirect::route('profile.edit')->with('success', 'Password updated successfully.');
    }

    /**
     * Update the user's avatar.
     */
    public function updateAvatar(AvatarUpdateRequest $request): RedirectResponse
    {
        return Redirect::route('profile.edit')->with('error', 'Avatar update function is disabled. Avatar always uses default.');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        return Redirect::route('profile.edit')->with('error', 'Avatar update function is disabled. Avatar always uses default.');
    }

    /**
     * Delete avatar from S3 if it belongs to our bucket.
     */
    private function deleteAvatarFromS3IfPossible(?string $avatarPathOrUrl): void
    {
        if (!$avatarPathOrUrl) {
            return;
        }

        $disk = Storage::disk('s3');

        // Convert URL to relative path to delete
        $relativePath = $avatarPathOrUrl;
        if (str_starts_with($avatarPathOrUrl, 'http')) {
            $parsed = parse_url($avatarPathOrUrl);
            $relativePath = isset($parsed['path']) ? ltrim($parsed['path'], '/') : '';
        }

        if ($relativePath && $disk->exists($relativePath)) {
            $disk->delete($relativePath);
        }
    }

    /**
     * Build public S3 URL, supporting custom endpoint/AWS_URL.
     */
    private function makeS3PublicUrl(string $path): string
    {
        $customUrl = rtrim(
            config('filesystems.disks.s3.url')
                ?? config('filesystems.disks.s3.endpoint')
                ?? env('AWS_URL', ''),
            '/'
        );

        if (!empty($customUrl)) {
            return $customUrl . '/' . ltrim($path, '/');
        }

        $bucket = config('filesystems.disks.s3.bucket');
        $region = config('filesystems.disks.s3.region');

        if (!empty($bucket) && !empty($region)) {
            return "https://{$bucket}.s3.{$region}.amazonaws.com/" . ltrim($path, '/');
        }

        // Fallback to disk URL
        return Storage::disk('s3')->url($path);
    }

    /**
     * Display user's activity log.
     */
    public function activity(Request $request): View
    {
        $user = $request->user();

        // Get recent activities (can be implemented later)
        $activities = collect(); // Placeholder cho activity log

        return view('profile.activity', compact('user', 'activities'));
    }

    /**
     * Display user's security settings.
     */
    public function security(Request $request): View
    {
        $user = $request->user();

        // Get security information
        $securityInfo = [
            'last_login' => $user->last_login_at ?? 'Not logged in',
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

        $status = $user->two_factor_enabled ? 'enabled' : 'disabled';
        return Redirect::route('profile.security')->with('success', "Two-factor authentication has been {$status}.");
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
            return back()->withErrors(['password' => 'Password is incorrect.']);
        }

        // Delete avatar if it exists
        $this->deleteAvatarFromS3IfPossible($user->avatar);

        // Delete account
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

        // Get notifications (can be implemented later)
        $notifications = collect(); // Placeholder cho notifications

        return view('profile.notifications', compact('user', 'notifications'));
    }

    /**
     * Mark notification as read.
     */
    public function markNotificationAsRead(Request $request, $id): RedirectResponse
    {
        // Implement notification marking logic
        return Redirect::route('profile.notifications')->with('success', 'Notification marked as read.');
    }

    /**
     * Export user data.
     */
    public function exportData(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Implement data export logic
        // Can create job to export data

        return Redirect::route('profile.edit')->with('success', 'Data export request has been sent. You will receive an email when it is complete.');
    }

    /**
     * Send verification email for authenticated user
     */
    public function sendVerificationEmail(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Check if already verified
        if ($user->email_verified_at) {
            return Redirect::route('profile.edit')->with('info', 'Email has been verified       before.');
        }

        $sent = $this->emailVerificationService->sendVerificationEmail($user);

        if ($sent) {
            return Redirect::route('profile.edit')->with('success', 'Verification email has been sent. Please check your inbox.');
        } else {
            return Redirect::route('profile.edit')->with('error', 'Unable to send verification email. Please try again later.');
        }
    }
}
