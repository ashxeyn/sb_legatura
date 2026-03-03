<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\authService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class passwordController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new authService();
    }

    /**
     * Show the forgot password form.
     */
    public function showForgotForm()
    {
        return view('signUp_logIN.forgot_password');
    }

    /**
     * Send OTP for password reset.
     */
    public function sendResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $email = $request->input('email');

        // Check if user exists
        $user = DB::table('users')->where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with that email address.'
            ], 422);
        }

        // Check if user has been verified (has approved contractor or property owner record)
        $hasApprovedContractor = DB::table('contractors')
            ->where('user_id', $user->user_id)
            ->where('verification_status', 'approved')
            ->exists();

        $hasApprovedPropertyOwner = DB::table('property_owners')
            ->where('user_id', $user->user_id)
            ->where('verification_status', 'approved')
            ->exists();

        if (!$hasApprovedContractor && !$hasApprovedPropertyOwner) {
            return response()->json([
                'success' => false,
                'message' => 'This account has not been verified yet. Please contact an administrator.'
            ], 422);
        }

        // Generate 6-digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedOtp = Hash::make($otp);

        // Store OTP in cache (15 min expiry)
        Cache::put('password_reset_otp_' . $email, $hashedOtp, now()->addMinutes(15));

        // Send OTP email
        try {
            Mail::raw(
                "Your password reset code is: {$otp}\n\nThis code will expire in 15 minutes. If you did not request a password reset, please ignore this email.",
                function ($message) use ($email) {
                    $message->to($email)
                        ->subject('Legatura - Password Reset Code');
                }
            );
        } catch (\Exception $e) {
            Log::error('Failed to send password reset OTP', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset code. Please try again later.'
            ], 500);
        }

        Log::info("Password reset OTP sent to {$email}");

        return response()->json([
            'success' => true,
            'message' => 'Reset code sent to your email.'
        ]);
    }

    /**
     * Verify the password reset OTP.
     */
    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');

        $hashedOtp = Cache::get('password_reset_otp_' . $email);

        if (!$hashedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new one.'
            ], 422);
        }

        if (!Hash::check($otp, $hashedOtp)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP. Please try again.'
            ], 422);
        }

        // OTP verified — set a short-lived token allowing password reset
        $resetToken = bin2hex(random_bytes(32));
        Cache::put('password_reset_token_' . $email, $resetToken, now()->addMinutes(10));

        // Clear the OTP so it can't be reused
        Cache::forget('password_reset_otp_' . $email);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'reset_token' => $resetToken
        ]);
    }

    /**
     * Reset the password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'reset_token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'confirmed'
            ]
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.regex' => 'Password must contain at least one uppercase letter, one number, and one special character.',
            'password.confirmed' => 'Passwords do not match.'
        ]);

        $email = $request->input('email');
        $resetToken = $request->input('reset_token');

        // Validate the reset token
        $storedToken = Cache::get('password_reset_token_' . $email);

        if (!$storedToken || $storedToken !== $resetToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset session. Please start over.'
            ], 422);
        }

        // Update password
        $passwordHash = Hash::make($request->input('password'));

        $updated = DB::table('users')
            ->where('email', $email)
            ->update(['password_hash' => $passwordHash]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password. Please try again.'
            ], 500);
        }

        // Cleanup
        Cache::forget('password_reset_token_' . $email);

        Log::info("Password reset successful for {$email}");

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully!'
        ]);
    }

    /**
     * API: Force change password (for first-time member login)
     *
     * Password rules:
     * - At least 8 characters
     * - At least one uppercase letter
     * - At least one number
     * - At least one special character (!@#$%^&*(),.?":{}|<>)
     */
    public function apiForceChangePassword(Request $request)
    {
        try {
            $request->validate([
                'user_id' => 'required|integer',
                'new_password' => 'required|string|min:8',
                'new_password_confirmation' => 'required|string|same:new_password',
            ]);

            $userId = $request->user_id;

            // Validate password strength using authService rules
            $strengthCheck = $this->authService->validatePasswordStrength($request->new_password);
            if (!$strengthCheck['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $strengthCheck['message'],
                ], 422);
            }

            // Check user exists and still has the default password
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (!Hash::check('teammember123@!', $user->password_hash)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password change is not required for this account',
                ], 400);
            }

            // Update password (changing away from the default automatically
            // clears the "must change" state — no flag column needed).
            $user->password_hash = bcrypt($request->new_password);
            $user->updated_at = now();
            $user->save();

            Log::info('Force password change completed', [
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Force password change error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while changing password',
            ], 500);
        }
    }
}

