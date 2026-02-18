<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\authService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class passwordController extends Controller
{
    protected $authService;

    public function __construct()
    {
        $this->authService = new authService();
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
