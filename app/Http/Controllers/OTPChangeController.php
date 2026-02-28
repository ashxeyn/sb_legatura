<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\authService;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class OTPChangeController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new authService();
    }

    public function sendOtp(Request $request)
    {
        $user = $request->user();
        // If Sanctum/session user not present, try resolving via Bearer token in personal_access_tokens
        if (!$user) {
            $bearer = $request->bearerToken();
            if ($bearer) {
                try {
                    // Prefer Sanctum model lookup which handles hashing
                    if (class_exists(PersonalAccessToken::class)) {
                        $pat = PersonalAccessToken::findToken($bearer);
                        if ($pat && $pat->tokenable) {
                            $user = $pat->tokenable;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('PersonalAccessToken lookup failed: ' . $e->getMessage());
                }

                if (!$user) {
                    // Fallback to direct DB lookup by hashed token
                    $tokenRecord = DB::table('personal_access_tokens')->where('token', hash('sha256', $bearer))->first();
                    if ($tokenRecord) {
                        $user = DB::table('users')->where('user_id', $tokenRecord->tokenable_id)->first();
                    }
                }
            }
        }

        if (!$user) return response()->json(['success' => false, 'message' => 'Authentication required'], 401);

        $purpose = $request->input('purpose');
        $newValue = $request->input('new_value');

        if (empty($purpose)) return response()->json(['success' => false, 'message' => 'Purpose required'], 422);

        // Determine destination. Allow explicit destination override (e.g. deliver OTP to email)
        $destinationOverride = $request->input('destination');
        if (!empty($destinationOverride)) {
            $destination = $destinationOverride;
        } else {
            if ($purpose === 'change_password') {
                $destination = $user->email ?? null;
            } else {
                if (empty($newValue)) return response()->json(['success' => false, 'message' => 'New value required'], 422);
                $destination = $newValue;
            }
        }

        if (empty($destination)) return response()->json(['success' => false, 'message' => 'Destination not available'], 422);

        // If changing email, require current password verification before sending OTP
        if ($purpose === 'change_email') {
            $currentPassword = $request->input('current_password');
            if (empty($currentPassword)) return response()->json(['success' => false, 'message' => 'Current password required'], 422);
            try {
                $hashed = $user->password_hash ?? ($user->password ?? null);
                if (empty($hashed) || !$this->authService->verifyPassword($currentPassword, $hashed)) {
                    return response()->json(['success' => false, 'message' => 'Invalid password'], 422);
                }
            } catch (\Throwable $e) {
                Log::warning('Password verification failed: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Password verification failed'], 500);
            }
        }

        // Rate-limit sends per destination per hour
        $normalized = strpos($destination, '@') !== false ? strtolower($destination) : preg_replace('/[^0-9]/', '', $destination);
        $hourKey = 'change_otp_send_' . $normalized . '_' . date('YmdH');
        $sendLimit = (int)config('otp.send_limit_per_hour', 5);
        try {
            $current = Cache::get($hourKey, 0);
            if ($current >= $sendLimit) {
                return response()->json(['success' => false, 'message' => 'OTP send limit reached. Please try later.'], 429);
            }
        } catch (\Throwable $e) { Log::warning('OTP send rate-limit check failed: ' . $e->getMessage()); }

        $result = $this->authService->sendChangeOtp($user, $purpose, $destination);
        if (!$result['success']) return response()->json(['success' => false, 'message' => $result['message'] ?? 'Failed to send OTP'], 500);

        // increment counter
        try { Cache::increment($hourKey); Cache::put($hourKey, Cache::get($hourKey), now()->addHour()); } catch (\Throwable $e) { Log::warning('Failed to increment change otp send counter: ' . $e->getMessage()); }

        return response()->json([
            'success' => true,
            'message' => 'OTP sent',
            'masked' => $result['masked'] ?? null,
            'otp_token' => $result['otp_token'] ?? null
        ], 200);
    }

    public function verifyOtp(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            $bearer = $request->bearerToken();
            if ($bearer) {
                try {
                    if (class_exists(PersonalAccessToken::class)) {
                        $pat = PersonalAccessToken::findToken($bearer);
                        if ($pat && $pat->tokenable) {
                            $user = $pat->tokenable;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('PersonalAccessToken lookup failed: ' . $e->getMessage());
                }

                if (!$user) {
                    $tokenRecord = DB::table('personal_access_tokens')->where('token', hash('sha256', $bearer))->first();
                    if ($tokenRecord) {
                        $user = DB::table('users')->where('user_id', $tokenRecord->tokenable_id)->first();
                    }
                }
            }
        }

        if (!$user) return response()->json(['success' => false, 'message' => 'Authentication required'], 401);

        $purpose = $request->input('purpose');
        $otp = $request->input('otp');
        $newValue = $request->input('new_value');
        $otpToken = $request->input('otp_token');
        $clientIp = $request->ip();

        if (empty($purpose) || empty($otp)) return response()->json(['success' => false, 'message' => 'Purpose and OTP are required'], 422);

        // Lookup meta: try token, then new_value (email/phone), then IP mapping
        $meta = null;
        $normalized = null;
        if (!empty($otpToken)) {
            $meta = Cache::get('change_otp_token_' . $otpToken);
        }

        if (!$meta && !empty($newValue)) {
            $normalized = strpos($newValue, '@') !== false ? strtolower($newValue) : preg_replace('/[^0-9]/', '', $newValue);
            $meta = Cache::get('change_otp_' . $normalized);
        }

        if (!$meta && $clientIp) {
            try {
                $mapped = Cache::get('change_otp_ip_' . $clientIp);
                if ($mapped) {
                    $normalized = $mapped;
                    $meta = Cache::get('change_otp_' . $mapped);
                }
            } catch (\Throwable $e) { Log::warning('IP fallback lookup failed: ' . $e->getMessage()); }
        }

        if (!$meta || empty($meta['hash'])) return response()->json(['success' => false, 'message' => 'OTP not found or expired'], 422);

        // TTL check
        $ttl = (int)config('otp.ttl_seconds', 900);
        $grace = (int)config('otp.grace_seconds', 30);
        if (!empty($meta['issued_at']) && now()->timestamp > ($meta['issued_at'] + $ttl + $grace)) {
            // cleanup
            if (!empty($normalized)) Cache::forget('change_otp_' . $normalized);
            if (!empty($otpToken)) Cache::forget('change_otp_token_' . $otpToken);
            return response()->json(['success' => false, 'message' => 'OTP expired. Please request a new code.'], 422);
        }

        // verify attempts limit
        $attemptKey = 'change_otp_attempts_' . ($normalized ?? ($otpToken ?? $clientIp));
        $attemptLimit = (int)config('otp.verify_attempts_limit', 5);
        $blockSeconds = (int)config('otp.verify_block_seconds', 900);
        $attempts = Cache::get($attemptKey, 0);
        if ($attempts >= $attemptLimit) {
            return response()->json(['success' => false, 'message' => 'Too many failed attempts. Please try again later.'], 429);
        }

        if (!$this->authService->verifyOtp($otp, $meta['hash'])) {
            // increment attempts and set block expiry
            $newAttempts = Cache::increment($attemptKey);
            Cache::put($attemptKey, $newAttempts, now()->addSeconds($blockSeconds));

            $attemptsLeft = max(0, $attemptLimit - $newAttempts);
            if ($newAttempts >= $attemptLimit) {
                $minutes = ceil($blockSeconds / 60);
                return response()->json([
                    'success' => false,
                    'message' => "Too many failed attempts. Verification blocked for {$minutes} minute(s).",
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP',
                'attempts_left' => $attemptsLeft,
            ], 422);
        }

        // success — perform update. For contact changes, update role-specific table based on active role.
        try {
            if ($purpose === 'change_email') {
                DB::table('users')->where('user_id', $user->user_id)->update(['email' => $newValue]);
            } elseif ($purpose === 'change_contact') {
                // Resolve active role: prefer preferred_role when user_type is 'both'
                $role = $user->user_type ?? null;
                if ($role === 'both') {
                    $pref = $user->preferred_role ?? DB::table('users')->where('user_id', $user->user_id)->value('preferred_role');
                    if (!empty($pref)) $role = $pref;
                }

                $roleLower = is_string($role) ? strtolower($role) : null;
                if ($roleLower === 'contractor' || $roleLower === 'contractor_user') {
                    DB::table('contractors')->where('user_id', $user->user_id)->update(['company_phone' => $newValue]);
                } elseif ($roleLower === 'property_owner' || $roleLower === 'owner') {
                    DB::table('property_owners')->where('user_id', $user->user_id)->update(['phone_number' => $newValue]);
                } else {
                    // Fallback to users table
                    DB::table('users')->where('user_id', $user->user_id)->update(['phone_number' => $newValue]);
                }
            } elseif ($purpose === 'change_password') {
                $hashed = $this->authService->hashPassword($newValue);
                DB::table('users')->where('user_id', $user->user_id)->update(['password_hash' => $hashed]);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to update user after OTP verify: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update account'], 500);
        }

        // cleanup cache entries
        try {
            if (!empty($normalized)) Cache::forget('change_otp_' . $normalized);
            if (!empty($otpToken)) Cache::forget('change_otp_token_' . $otpToken);
        } catch (\Throwable $e) { Log::warning('Failed to cleanup change otp cache: ' . $e->getMessage()); }

        return response()->json(['success' => true, 'message' => 'Updated successfully'], 200);
    }
}
