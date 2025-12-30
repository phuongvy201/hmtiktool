<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmailVerificationController extends Controller
{
    private EmailVerificationService $emailVerificationService;

    public function __construct(EmailVerificationService $emailVerificationService)
    {
        $this->emailVerificationService = $emailVerificationService;
    }

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
            return back()->with('error', 'Email does not exist in the system.');
        }

        $sent = $this->emailVerificationService->sendVerificationEmail($user);

        if ($sent) {
            return back()->with('success', 'Email verification has been sent. Please check your email inbox.');
        } else {
            if ($user->email_verified_at) {
                return back()->with('info', 'Email has been verified before.');
            }
            return back()->with('error', 'Unable to send verification email. Please try again later.');
        }
    }

    /**
     * Verify email
     */
    public function verify(Request $request, $id, $token)
    {
        $user = User::findOrFail($id);
        $result = $this->emailVerificationService->verifyEmail($user, $token);

        if ($result['success']) {
            // Refresh & login to ensure session reflects verified state
            $user = $user->fresh();
            Auth::login($user);

            // If user is logged in, redirect to dashboard
            // If user is not logged in, redirect to login with message
            if (Auth::check() && Auth::id() == $user->id) {
                return redirect()->route('dashboard')->with('success', $result['message']);
            } else {
                return redirect()->route('login')->with('success', $result['message'] . ' Please login to continue.');
            }
        } else {
            // If user is logged in, redirect to dashboard with error
            // If user is not logged in, redirect to login with error
            if (Auth::check() && Auth::id() == $user->id) {
                return redirect()->route('dashboard')->with('error', $result['message']);
            } else {
                return redirect()->route('login')->with('error', $result['message']);
            }
        }
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
            return back()->with('error', 'Email does not exist in the system.');
        }

        $result = $this->emailVerificationService->resendVerificationEmail($user);

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->with('error', $result['message']);
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
