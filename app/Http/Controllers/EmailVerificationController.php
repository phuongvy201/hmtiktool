<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class EmailVerificationController extends Controller
{
    /**
     * Send verification email
     */
    public function sendVerificationEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Email không tồn tại trong hệ thống.');
        }

        if ($user->email_verified_at) {
            return back()->with('info', 'Email đã được xác thực trước đó.');
        }

        // Generate verification token
        $token = Str::random(64);
        $user->update([
            'email_verification_token' => $token,
            'email_verification_expires_at' => now()->addHours(1), // 1 hour expiry
        ]);

        // Generate verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'token' => $token]
        );

        // Send email
        try {
            Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
            return back()->with('success', 'Email xác thực đã được gửi. Vui lòng kiểm tra hộp thư của bạn.');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể gửi email xác thực. Vui lòng thử lại sau.');
        }
    }

    /**
     * Verify email
     */
    public function verify(Request $request, $id, $token)
    {
        $user = User::findOrFail($id);

        // Check if already verified
        if ($user->email_verified_at) {
            return redirect()->route('dashboard')->with('info', 'Email đã được xác thực trước đó.');
        }

        // Check if token matches and not expired
        if ($user->email_verification_token !== $token) {
            return redirect()->route('dashboard')->with('error', 'Link xác thực không hợp lệ.');
        }

        if ($user->email_verification_expires_at && $user->email_verification_expires_at < now()) {
            return redirect()->route('dashboard')->with('error', 'Link xác thực đã hết hạn. Vui lòng yêu cầu gửi lại email.');
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
        ]);

        return redirect()->route('dashboard')->with('success', 'Email đã được xác thực thành công!');
    }

    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('error', 'Email không tồn tại trong hệ thống.');
        }

        if ($user->email_verified_at) {
            return back()->with('info', 'Email đã được xác thực trước đó.');
        }

        // Check if we can resend (prevent spam)
        if ($user->email_verification_expires_at && $user->email_verification_expires_at > now()->subMinutes(5)) {
            return back()->with('error', 'Vui lòng đợi 5 phút trước khi yêu cầu gửi lại email xác thực.');
        }

        // Generate new verification token
        $token = Str::random(64);
        $user->update([
            'email_verification_token' => $token,
            'email_verification_expires_at' => now()->addHours(1),
        ]);

        // Generate verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'token' => $token]
        );

        // Send email
        try {
            Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
            return back()->with('success', 'Email xác thực đã được gửi lại. Vui lòng kiểm tra hộp thư của bạn.');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể gửi email xác thực. Vui lòng thử lại sau.');
        }
    }

    /**
     * Show verification form
     */
    public function showVerificationForm()
    {
        return view('auth.verify-email');
    }

    /**
     * Show resend form
     */
    public function showResendForm()
    {
        return view('auth.resend-verification');
    }
}
