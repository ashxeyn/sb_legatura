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
        try {
            $num = random_int(0, 999999);
        } catch (\Throwable $e) {
            // Fallback to less secure generator if random_int is unavailable
            $num = mt_rand(0, 999999);
        }
        return str_pad((string)$num, 6, '0', STR_PAD_LEFT);
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
        try {
            $user = DB::table('users')
                ->where('username', $username)
                ->orWhere('email', $username)
                ->first();
        } catch (\Exception $e) {
            \Log::warning('attemptUserLogin DB lookup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

        if ($user && $this->verifyPassword($password, $user->password_hash)) {
            // Check verification status based on user type
            $isVerified = false;
            $rejectionReason = null;
            $determinedRole = null;

            // Check contractors and property_owners for verification_status = 'approved'
            $contractorApproved = DB::table('contractors')
                ->where('user_id', $user->user_id)
                ->where('verification_status', 'approved')
                ->first();
            $propertyOwnerApproved = DB::table('property_owners')
                ->where('user_id', $user->user_id)
                ->where('verification_status', 'approved')
                ->first();

            if ($contractorApproved || $propertyOwnerApproved) {
                $isVerified = true;
            }

            \Log::info('User login attempt', [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'user_type' => $user->user_type,
            ]);

            if ($user->user_type === 'admin') {
                $isVerified = true;
            } elseif ($user->user_type === 'staff') {
                // Staff members (contractor team members) - verify via contractor_users and parent contractor
                \Log::info('Staff user detected, checking contractor_users table', ['user_id' => $user->user_id]);
                $contractorUser = DB::table('contractor_users')->where('user_id', $user->user_id)->first();
                \Log::info('contractor_users lookup result', ['found' => $contractorUser ? 'yes' : 'no']);
                \Log::info('contractor_users lookup result', ['found' => $contractorUser ? 'yes' : 'no']);

                if ($contractorUser && $contractorUser->is_active == 1 && $contractorUser->is_deleted == 0) {
                    // Check if parent contractor is approved
                    $contractor = DB::table('contractors')->where('contractor_id', $contractorUser->contractor_id)->first();
                    \Log::info('Parent contractor lookup', [
                        'contractor_id' => $contractorUser->contractor_id,
                        'found' => $contractor ? 'yes' : 'no',
                        'verification_status' => $contractor->verification_status ?? 'N/A'
                    ]);

                    if ($contractor && $contractor->verification_status === 'approved') {
                        $isVerified = true;
                        $determinedRole = 'contractor'; // Staff members operate under contractor context
                        \Log::info('Staff user verified, determinedRole set to contractor');
                    } elseif ($contractor && $contractor->verification_status === 'rejected') {
                        $rejectionReason = 'The contractor account has been rejected: ' . $contractor->rejection_reason;
                        \Log::info('Staff user - contractor rejected', ['reason' => $rejectionReason]);
                    } else {
                        $rejectionReason = 'The contractor account is pending verification';
                        \Log::info('Staff user - contractor pending verification');
                    }
                } elseif ($contractorUser && $contractorUser->is_active == 0) {
                    $rejectionReason = 'Your team member account has been deactivated by the contractor';
                    \Log::info('Staff user - contractor_users record inactive');
                } elseif ($contractorUser && $contractorUser->is_deleted == 1) {
                    $rejectionReason = 'Your team member account has been removed';
                    \Log::info('Staff user - contractor_users record deleted');
                } else {
                    \Log::warning('Staff user has NO contractor_users record', ['user_id' => $user->user_id]);
                }
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
                // Robust status and rejection reason logic for BOTH
                // Use 'exists' checks to find any APPROVED records. This prevents a newly-created
                // PENDING record from masking an earlier APPROVED record (e.g., when adding a
                // new role creates a pending row while an approved row still exists).
                // Check both contractor_users (member records) and contractors (primary contractor account)
                // Prefer the primary contractor record for verification status
                $cApproved = DB::table('contractors')
                    ->where('user_id', $user->user_id)
                    ->where('verification_status', 'approved')
                    ->exists();
                $oApproved = DB::table('property_owners')
                    ->where('user_id', $user->user_id)
                    ->where('verification_status', 'approved')
                    ->exists();

                // If either is approved, allow login and set determinedRole
                if ($cApproved && !$oApproved) {
                    $isVerified = true;
                    $determinedRole = 'contractor';
                } elseif ($oApproved && !$cApproved) {
                    $isVerified = true;
                    $determinedRole = 'property_owner';
                } elseif ($cApproved && $oApproved) {
                    // Both approved, choose by earliest created_at timestamp
                    $contractorCreated = DB::table('contractors')->where('user_id', $user->user_id)->value('created_at');
                    $ownerCreated = DB::table('property_owners')->where('user_id', $user->user_id)->value('created_at');
                    $isVerified = true;
                    if ($contractorCreated && $ownerCreated && $contractorCreated < $ownerCreated) {
                        $determinedRole = 'contractor';
                    } else {
                        $determinedRole = 'property_owner';
                    }
                } else {
                    // Neither role is approved. Build rejection/pending info from latest records.
                    $cRejected = DB::table('contractors')
                        ->where('user_id', $user->user_id)
                        ->where('verification_status', 'rejected')
                        ->exists();
                    $oRejected = DB::table('property_owners')
                        ->where('user_id', $user->user_id)
                        ->where('verification_status', 'rejected')
                        ->exists();

                    $rejectionReason = null;
                    if ($cRejected) {
                        $reason = DB::table('contractors')
                            ->where('user_id', $user->user_id)
                            ->whereNotNull('rejection_reason')
                            ->orderBy('updated_at', 'desc')
                            ->value('rejection_reason');
                        $rejectionReason = "Contractor: " . ($reason ?: 'rejected');
                    }
                    if ($oRejected) {
                        $reason = DB::table('property_owners')
                            ->where('user_id', $user->user_id)
                            ->whereNotNull('rejection_reason')
                            ->orderBy('updated_at', 'desc')
                            ->value('rejection_reason');
                        $rejectionReason = $rejectionReason ? $rejectionReason . " | Owner: " . ($reason ?: 'rejected') : "Owner: " . ($reason ?: 'rejected');
                    }

                    return [
                        'success' => false,
                        'message' => $rejectionReason ? "Rejected: $rejectionReason" : "Your account is pending verification",
                        'user' => $user,
                        'contractor_status' => $cApproved ? 'approved' : ($cRejected ? 'rejected' : 'pending'),
                        'owner_status' => $oApproved ? 'approved' : ($oRejected ? 'rejected' : 'pending')
                    ];
                }
            }

            // Allow login if verified
            if (!$isVerified) {
                $message = 'Your account is waiting for verification or is inactive. Please contact support.';
                if ($rejectionReason) {
                    $message = "Your account has been rejected due to the reason: {$rejectionReason}. Please contact support.";
                }

                \Log::info('Login failed - not verified', [
                    'user_id' => $user->user_id,
                    'rejectionReason' => $rejectionReason
                ]);

                // If owner is verified, allow login and attach user object even if contractor role is pending
                $owner = isset($owner) ? $owner : DB::table('property_owners')->where('user_id', $user->user_id)->first();
                if ($owner && $owner->verification_status === 'approved' && $owner->is_active == 1) {

                    // Prefer returning success so owners can still sign in when their contractor application is pending
                    return [
                        'success' => true,
                        'user' => $user,
                        'userType' => 'user',
                        'determinedRole' => 'property_owner',
                        'owner_status' => 'approved',
                        'contractor_status' => ($contractor && $contractor->verification_status) ? $contractor->verification_status : null,
                        'contractor_rejection_reason' => $contractor && $contractor->verification_status === 'rejected' ? $contractor->rejection_reason : null,
                        'owner_rejection_reason' => $owner && $owner->verification_status === 'rejected' ? $owner->rejection_reason : null
                    ];
                }
                return [
                    'success' => false,
                    'message' => $message
                ];
            }

            \Log::info('Login successful', [
                'user_id' => $user->user_id,
                'user_type' => $user->user_type,
                'determinedRole' => $determinedRole ?? 'null',
                'isVerified' => $isVerified
            ]);

            return [
                'success' => true,
                'user' => $user,
                'userType' => 'user',
                'determinedRole' => $determinedRole ?? null
            ];
        }

        // Return specific error based on whether user exists
        if ($user) {
            return [
                'success' => false,
                'errors' => [
                    'password' => 'Invalid password'
                ]
            ];
        }

        return [
            'success' => false,
            'errors' => [
                'username' => 'Invalid username'
            ]
        ];
    }

    public function attemptAdminLogin($username, $password)
    {
        try {
            $admin = DB::table('admin_users')
                ->where('username', $username)
                ->orWhere('email', $username)
                ->first();
        } catch (\Exception $e) {
            \Log::warning('attemptAdminLogin DB lookup failed: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Invalid credentials'
            ];
        }

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

        // Return specific error based on whether admin exists
        if ($admin) {
            return [
                'success' => false,
                'errors' => [
                    'password' => 'Invalid password'
                ]
            ];
        }

        return [
            'success' => false,
            'errors' => [
                'username' => 'Invalid username'
            ]
        ];

    }

    public function login($username, $password)
    {
        $userLogin = $this->attemptUserLogin($username, $password);
        if ($userLogin['success']) {
            return $userLogin;
        }

        try {
            $user = DB::table('users')
                ->where('username', $username)
                ->orWhere('email', $username)
                ->first();

            if ($user) {
                return $userLogin;
            }
        } catch (\Exception $e) {
            \Log::warning('login user existence lookup failed: ' . $e->getMessage());
            // proceed to admin lookup; treat as non-existent user on DB error
        }

        $adminLogin = $this->attemptAdminLogin($username, $password);
        if ($adminLogin['success']) {
            return $adminLogin;
        }

        return $adminLogin;
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
