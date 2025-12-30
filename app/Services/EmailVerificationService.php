<?php

namespace App\Services;

use App\Models\User;
use App\Mail\VerifyEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EmailVerificationService
{
    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(User $user): bool
    {
        try {
            // Check if already verified
            if ($user->email_verified_at) {
                Log::info('User email already verified', ['user_id' => $user->id]);
                return false;
            }

            // Generate verification token
            $token = Str::random(64);
            $user->update([
                'email_verification_token' => $token,
                'email_verification_expires_at' => now()->addHours(1), // 1 hour expiry
            ]);

            // Generate verification URL
            $verificationUrl = URL::temporarySignedRoute(
                'email.verification.verify',
                now()->addHour(),
                ['id' => $user->id, 'token' => $token]
            );

            // Send email
            Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

            Log::info('Verification email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Verify email with token
     */
    public function verifyEmail(User $user, string $token): array
    {
        // Check if already verified
        if ($user->email_verified_at) {
            return [
                'success' => false,
                'message' => 'Email đã được xác thực trước đó.'
            ];
        }

        // Check if token matches
        if ($user->email_verification_token !== $token) {
            return [
                'success' => false,
                'message' => 'Link xác thực không hợp lệ.'
            ];
        }

        // Check if token expired
        if ($user->email_verification_expires_at && $user->email_verification_expires_at < now()) {
            return [
                'success' => false,
                'message' => 'Link xác thực đã hết hạn. Vui lòng yêu cầu gửi lại email.'
            ];
        }

        // Mark email as verified
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
            'email_verification_expires_at' => null,
        ]);

        Log::info('Email verified successfully', ['user_id' => $user->id]);

        return [
            'success' => true,
            'message' => 'Email đã được xác thực thành công!'
        ];
    }

    /**
     * Resend verification email (with rate limiting)
     */
    public function resendVerificationEmail(User $user): array
    {
        // Check if already verified
        if ($user->email_verified_at) {
            return [
                'success' => false,
                'message' => 'Email đã được xác thực trước đó.'
            ];
        }

        // Check rate limiting (prevent spam - wait 5 minutes between requests)
        if ($user->email_verification_expires_at && $user->email_verification_expires_at > now()->subMinutes(5)) {
            return [
                'success' => false,
                'message' => 'Vui lòng đợi 5 phút trước khi yêu cầu gửi lại email xác thực.'
            ];
        }

        // Send verification email
        $sent = $this->sendVerificationEmail($user);

        if ($sent) {
            return [
                'success' => true,
                'message' => 'Email xác thực đã được gửi lại. Vui lòng kiểm tra hộp thư của bạn.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể gửi email xác thực. Vui lòng thử lại sau.'
            ];
        }
    }
}
