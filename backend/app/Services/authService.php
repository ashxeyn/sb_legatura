<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class authService
{
    // Generate a 6-digit OTP
    public function generateOtp()
    {
        return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    public function hashOtp($otp)
    {
        return Hash::make($otp);
    }

    public function verifyOtp($inputOtp, $hashedOtp)
    {
        return Hash::check($inputOtp, $hashedOtp);
    }

    public function hashPassword($password)
    {
        return Hash::make($password);
    }

    public function verifyPassword($inputPassword, $hashedPassword)
    {
        return Hash::check($inputPassword, $hashedPassword);
    }

    public function sendOtpEmail($email, $otp)
    {
        // \Log::info("OTP for {$email}: {$otp}");

        try {
            \Mail::raw("Your OTP code is: {$otp}\n\nThis code will expire soon. Please do not share this code with anyone.", function($message) use ($email) {
                $message->to($email)
                        ->subject('Legatura - Your OTP Code');
            });
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP email to {$email}: " . $e->getMessage());
            return false;
        }
    }

    public function attemptUserLogin($username, $password)
    {
        $user = DB::table('users')
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user && $this->verifyPassword($password, $user->password_hash)) {

            // Check verification status based on user type
            $isVerified = false;
            $rejectionReason = null;

            if ($user->user_type === 'admin') {
                $isVerified = true;
            } elseif ($user->user_type === 'contractor') {
                $contractor = DB::table('contractors')->where('user_id', $user->user_id)->first();
                $contractorUser = DB::table('contractor_users')->where('user_id', $user->user_id)->first();

                if ($contractor && $contractor->verification_status === 'approved' &&
                    $contractorUser && $contractorUser->is_active == 1) {
                    $isVerified = true;
                } elseif ($contractor && $contractor->verification_status === 'rejected') {
                    $rejectionReason = $contractor->rejection_reason;
                }
            } elseif ($user->user_type === 'property_owner') {
                $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
                if ($owner && $owner->verification_status === 'approved' && $owner->is_active == 1) {
                    $isVerified = true;
                } elseif ($owner && $owner->verification_status === 'rejected') {
                    $rejectionReason = $owner->rejection_reason;
                }
            } elseif ($user->user_type === 'both') {
                $contractor = DB::table('contractors')->where('user_id', $user->user_id)->first();
                $contractorUser = DB::table('contractor_users')->where('user_id', $user->user_id)->first();
                $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();

                $isContractorValid = $contractor &&
                                     $contractor->verification_status === 'approved' &&
                                     $contractorUser &&
                                     $contractorUser->is_active == 1 &&
                                     $contractorUser->role === 'owner';

                $isOwnerValid = $owner &&
                                $owner->verification_status === 'approved' &&
                                $owner->is_active == 1;

                if ($isContractorValid && !$isOwnerValid) {
                    $isVerified = true;
                    $determinedRole = 'contractor';
                } elseif (!$isContractorValid && $isOwnerValid) {
                    $isVerified = true;
                    $determinedRole = 'property_owner';
                } elseif ($isContractorValid && $isOwnerValid) {
                    $isVerified = true;
                    // Compare created_at (string comparison works for standard timestamps)
                    if ($contractor->created_at < $owner->created_at) {
                        $determinedRole = 'contractor';
                    } else {
                        $determinedRole = 'property_owner';
                    }
                } else {
                    // Both invalid. Check for rejection.
                    if ($contractor && $contractor->verification_status === 'rejected') {
                         $rejectionReason = "Contractor Account: " . $contractor->rejection_reason;
                    }
                    if ($owner && $owner->verification_status === 'rejected') {
                        $ownerReason = "Property Owner Account: " . $owner->rejection_reason;
                        $rejectionReason = $rejectionReason ? $rejectionReason . " | " . $ownerReason : $ownerReason;
                    }
                }
            }

            // Allow login if verified
            if (!$isVerified) {
                $message = 'Your account is waiting for verification or is inactive. Please contact support.';
                if ($rejectionReason) {
                    $message = "Your account has been rejected due to the reason: {$rejectionReason}. Please contact support.";
                }

                return [
                    'success' => false,
                    'message' => $message
                ];
            }

            return [
                'success' => true,
                'user' => $user,
                'userType' => 'user',
                'determinedRole' => $determinedRole ?? null
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials'
        ];
    }

    public function attemptAdminLogin($username, $password)
    {
        $admin = DB::table('admin_users')
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($admin && $this->verifyPassword($password, $admin->password_hash)) {
            if ($admin->is_active) {
                return [
                    'success' => true,
                    'user' => $admin,
                    'userType' => 'admin'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Admin account is inactive'
                ];
            }
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials'
        ];
    }

    public function login($username, $password)
    {
        $userLogin = $this->attemptUserLogin($username, $password);
        if ($userLogin['success']) {
            return $userLogin;
        }

        $user = DB::table('users')
            ->where('username', $username)
            ->orWhere('email', $username)
            ->first();

        if ($user) {
            return $userLogin;
        }

        $adminLogin = $this->attemptAdminLogin($username, $password);
        if ($adminLogin['success']) {
            return $adminLogin;
        }

        return [
            'success' => false,
            'message' => 'Invalid username or password'
        ];
    }

    public function validatePasswordStrength($password)
    {
        if (strlen($password) < 8) {
            return [
                'valid' => false,
                'message' => 'Password must be at least 8 characters'
            ];
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must contain at least one uppercase letter'
            ];
        }

        if (!preg_match('/[0-9]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must contain at least one number'
            ];
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            return [
                'valid' => false,
                'message' => 'Password must contain at least one special character'
            ];
        }

        return ['valid' => true];
    }

    public function calculateAge($dateOfBirth)
    {
        $dob = new \DateTime($dateOfBirth);
        $now = new \DateTime();
        $age = $now->diff($dob)->y;
        return $age;
    }
}
