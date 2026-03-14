<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Services\SmsService;

class AuthService
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
        $otp = (string)$otp; // Ensure OTP is a string
        \Log::info("Sending OTP to {$email}", ['otp' => $otp, 'timestamp' => now()]);

        try {
            \Mail::raw("Your OTP code is: {$otp}\n\nThis code will expire in 15 minutes. Please do not share this code with anyone.", function($message) use ($email) {
                $message->to($email)
                        ->subject('Legatura - Your OTP Code');
            });

            \Log::info("OTP sent successfully to {$email}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send OTP email to {$email}", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    public function sendAccountPendingEmail($email, $firstName, $accountType = 'account')
    {
        \Log::info("Sending account pending approval email to {$email}", ['account_type' => $accountType, 'timestamp' => now()]);

        try {
            $subject = 'Legatura - Account Registration Received';
            $accountTypeText = $accountType === 'contractor' ? 'contractor' : ($accountType === 'owner' ? 'property owner' : 'account');

            $message = "Dear {$firstName},\n\n";
            $message .= "Thank you for registering your {$accountTypeText} account with Legatura!\n\n";
            $message .= "We have received your registration and are currently reviewing your application. This process typically takes 1-3 business days.\n\n";
            $message .= "What happens next:\n";
            $message .= "- Our admin team will verify your submitted documents\n";
            $message .= "- You will receive an email notification once your account is approved\n";
            $message .= "- After approval, you can log in and start using all platform features\n\n";
            $message .= "If you have any questions or need assistance, please don't hesitate to contact our support team.\n\n";
            $message .= "Thank you for choosing Legatura!\n\n";
            $message .= "Best regards,\n";
            $message .= "The Legatura Team";

            \Mail::raw($message, function($mailMessage) use ($email, $subject) {
                $mailMessage->to($email)
                           ->subject($subject);
            });

            \Log::info("Account pending approval email sent successfully to {$email}");
            return true;
        } catch (\Exception $e) {
            \Log::error("Failed to send account pending approval email to {$email}", [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Send change OTP to a destination (email or phone) and cache the hashed OTP.
     * Returns array with success, masked destination and otp_token.
     */
    public function sendChangeOtp($user, $purpose, $destination)
    {
        $destination = trim((string)$destination);
        $isEmail = strpos($destination, '@') !== false;

        $otp = $this->generateOtp();
        $otpHash = $this->hashOtp($otp);

        $sent = false;
        if ($isEmail) {
            $sent = $this->sendOtpEmail($destination, $otp);
        } else {
            // SMS fallback
            try {
                $sms = new SmsService();
                $sent = $sms->sendSms($destination, "Your OTP code is: {$otp}");
            } catch (\Throwable $e) {
                Log::warning('SMS send failed: ' . $e->getMessage());
                $sent = false;
            }
        }

        if (!$sent) {
            return ['success' => false, 'message' => 'Failed to send OTP'];
        }

        // Cache metadata for stateless clients
        $normalized = $isEmail ? strtolower($destination) : preg_replace('/[^0-9]/', '', $destination);
        $meta = ['hash' => $otpHash, 'issued_at' => now()->timestamp, 'purpose' => $purpose, 'destination' => $normalized, 'user_id' => $user->user_id ?? ($user->id ?? null)];
        $ttl = (int)config('otp.ttl_seconds', 900);

        try {
            Cache::put('change_otp_' . $normalized, $meta, now()->addSeconds($ttl));
            // IP -> destination mapping for fallback
            // Note: caller may set IP mapping separately if desired
            // Generate token for stateless clients
            $otpToken = bin2hex(random_bytes(8));
            Cache::put('change_otp_token_' . $otpToken, $meta, now()->addSeconds($ttl));
        } catch (\Throwable $e) {
            Log::warning('Failed to cache change OTP meta: ' . $e->getMessage());
            $otpToken = null;
        }

        return [
            'success' => true,
            'masked' => $this->maskDestination($destination),
            'otp_token' => $otpToken
        ];
    }

    public function maskDestination($destination)
    {
        if (strpos($destination, '@') !== false) {
            [$user, $domain] = explode('@', $destination, 2);
            $len = strlen($user);
            if ($len <= 2) return substr($user, 0, 1) . '***@' . $domain;
            return substr($user, 0, 1) . str_repeat('*', max(1, min(3, $len - 1))) . '@' . $domain;
        }
        // phone masking: keep last 3 digits
        $digits = preg_replace('/[^0-9]/', '', $destination);
        $len = strlen($digits);
        if ($len <= 4) return str_repeat('*', max(0, $len - 1)) . substr($digits, -1);
        return str_repeat('*', $len - 4) . substr($digits, -4);
    }

    public function attemptUserLogin($username, $password)
    {
        try {
            $user = DB::table('users')
                ->where(function ($query) use ($username) {
                    $query->whereRaw('BINARY username = ?', [$username])
                          ->orWhere('email', $username);
                })
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
            $contractorApproved = false;
            $propertyOwnerApproved = false;
            try {
                $contractorApproved = DB::table('contractors')
                    ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                    ->where('property_owners.user_id', $user->user_id)
                    ->where('contractors.verification_status', 'approved')
                    ->first();
            } catch (\Exception $e) {
                \Log::warning('contractors lookup failed: ' . $e->getMessage());
                $contractorApproved = false;
            }
            try {
                $propertyOwnerApproved = DB::table('property_owners')
                    ->where('user_id', $user->user_id)
                    ->where('verification_status', 'approved')
                    ->first();
            } catch (\Exception $e) {
                \Log::warning('property_owners lookup failed: ' . $e->getMessage());
                $propertyOwnerApproved = false;
            }

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
            } elseif ($user->user_type === 'owner_staff') {
                // Staff members (contractor team members) - verify via contractor_staff and parent contractor
                \Log::info('Staff user detected, checking contractor_staff table', ['user_id' => $user->user_id]);
                $staffOwnerId = DB::table('property_owners')->where('user_id', $user->user_id)->value('owner_id');
                $staffRecord = $staffOwnerId
                    ? DB::table('contractor_staff')
                        ->where('owner_id', $staffOwnerId)
                        ->whereNull('deletion_reason')
                        ->first()
                    : null;
                \Log::info('contractor_staff lookup result', ['found' => $staffRecord ? 'yes' : 'no']);

                if ($staffRecord && $staffRecord->is_active == 1) {
                    // Check if parent contractor is approved
                    $contractor = DB::table('contractors')->where('contractor_id', $staffRecord->contractor_id)->first();
                    \Log::info('Parent contractor lookup', [
                        'contractor_id' => $staffRecord->contractor_id,
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
                } elseif ($staffRecord && $staffRecord->is_active == 0) {
                    $rejectionReason = 'Your team member account has been deactivated by the contractor';
                    \Log::info('Staff user - contractor_staff record inactive');
                } elseif ($staffRecord && $staffRecord->deletion_reason) {
                    $rejectionReason = 'Your team member account has been removed';
                    \Log::info('Staff user - contractor_staff record deleted');
                } else {
                    \Log::warning('Staff user has NO contractor_staff record', ['user_id' => $user->user_id]);
                }
            } elseif ($user->user_type === 'contractor') {
                $contractor = null;
                try {
                    $cOwnerId = DB::table('property_owners')->where('user_id', $user->user_id)->value('owner_id');
                    $contractor = $cOwnerId ? DB::table('contractors')->where('owner_id', $cOwnerId)->first() : null;
                } catch (\Exception $e) {
                    \Log::warning('contractors lookup failed: ' . $e->getMessage());
                    $contractor = null;
                }

                if ($contractor && $contractor->verification_status === 'approved') {
                    $isVerified = true;
                } elseif ($contractor && $contractor->verification_status === 'rejected') {
                    $rejectionReason = $contractor->rejection_reason;
                }
            } elseif ($user->user_type === 'property_owner') {
                $owner = null;
                try {
                    $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
                } catch (\Exception $e) {
                    \Log::warning('property_owners lookup failed: ' . $e->getMessage());
                    $owner = null;
                }
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
                // Prefer the primary contractor record for verification status
                $cApproved = false;
                $oApproved = false;
                try {
                    $cApproved = DB::table('contractors')
                        ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                        ->where('property_owners.user_id', $user->user_id)
                        ->where('contractors.verification_status', 'approved')
                        ->exists();
                } catch (\Exception $e) {
                    \Log::warning('contractors exists lookup failed: ' . $e->getMessage());
                    $cApproved = false;
                }
                try {
                    $oApproved = DB::table('property_owners')
                        ->where('user_id', $user->user_id)
                        ->where('verification_status', 'approved')
                        ->exists();
                } catch (\Exception $e) {
                    \Log::warning('property_owners exists lookup failed: ' . $e->getMessage());
                    $oApproved = false;
                }

                // If either is approved, allow login and set determinedRole
                if ($cApproved && !$oApproved) {
                    $isVerified = true;
                    $determinedRole = 'contractor';
                } elseif ($oApproved && !$cApproved) {
                    $isVerified = true;
                    $determinedRole = 'property_owner';
                } elseif ($cApproved && $oApproved) {
                    // Both approved, choose by earliest created_at timestamp
                    $contractorCreated = null;
                    $ownerCreated = null;
                    try {
                        $contractorOwnerId = DB::table('property_owners')->where('user_id', $user->user_id)->value('owner_id');
                        $contractorCreated = $contractorOwnerId ? DB::table('contractors')->where('owner_id', $contractorOwnerId)->value('created_at') : null;
                    } catch (\Exception $e) {
                        \Log::warning('contractors value(created_at) lookup failed: ' . $e->getMessage());
                        $contractorCreated = null;
                    }
                    try {
                        $ownerCreated = DB::table('property_owners')->where('user_id', $user->user_id)->value('created_at');
                    } catch (\Exception $e) {
                        \Log::warning('property_owners value(created_at) lookup failed: ' . $e->getMessage());
                        $ownerCreated = null;
                    }
                    $isVerified = true;
                    if ($contractorCreated && $ownerCreated && $contractorCreated < $ownerCreated) {
                        $determinedRole = 'contractor';
                    } else {
                        $determinedRole = 'property_owner';
                    }
                } else {
                    // Neither role is approved. Build rejection/pending info from latest records.
                    $cRejected = false;
                    $oRejected = false;
                    try {
                        $cRejected = DB::table('contractors')
                            ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                            ->where('property_owners.user_id', $user->user_id)
                            ->where('contractors.verification_status', 'rejected')
                            ->exists();
                    } catch (\Exception $e) {
                        \Log::warning('contractors rejected exists lookup failed: ' . $e->getMessage());
                        $cRejected = false;
                    }
                    try {
                        $oRejected = DB::table('property_owners')
                            ->where('user_id', $user->user_id)
                            ->where('verification_status', 'rejected')
                            ->exists();
                    } catch (\Exception $e) {
                        \Log::warning('property_owners rejected exists lookup failed: ' . $e->getMessage());
                        $oRejected = false;
                    }

                    $rejectionReason = null;
                    if ($cRejected) {
                        try {
                            $reason = DB::table('contractors')
                                ->join('property_owners', 'contractors.owner_id', '=', 'property_owners.owner_id')
                                ->where('property_owners.user_id', $user->user_id)
                                ->whereNotNull('contractors.rejection_reason')
                                ->orderBy('contractors.updated_at', 'desc')
                                ->value('contractors.rejection_reason');
                        } catch (\Exception $e) {
                            \Log::warning('contractors rejection reason lookup failed: ' . $e->getMessage());
                            $reason = null;
                        }
                        $rejectionReason = "Contractor: " . ($reason ?: 'rejected');
                    }
                    if ($oRejected) {
                        try {
                            $reason = DB::table('property_owners')
                                ->where('user_id', $user->user_id)
                                ->whereNotNull('rejection_reason')
                                ->orderBy('updated_at', 'desc')
                                ->value('rejection_reason');
                        } catch (\Exception $e) {
                            \Log::warning('property_owners rejection reason lookup failed: ' . $e->getMessage());
                            $reason = null;
                        }
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
                ->where(function ($query) use ($username) {
                    $query->whereRaw('BINARY username = ?', [$username])
                          ->orWhere('email', $username);
                })
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

    public function login($username, $password, $adminOnly = false)
    {
        // If admin-only login, skip user table check
        if ($adminOnly) {
            $adminLogin = $this->attemptAdminLogin($username, $password);

            // If admin login failed, check if this username exists in users table
            if (!$adminLogin['success']) {
                try {
                    $user = DB::table('users')
                        ->where(function ($query) use ($username) {
                            $query->whereRaw('BINARY username = ?', [$username])
                                  ->orWhere('email', $username);
                        })
                        ->first();

                    // If user exists in users table, return admin-only error
                    if ($user) {
                        return [
                            'success' => false,
                            'errors' => [
                                'username' => 'Access Restricted: Admin Access Only'
                            ]
                        ];
                    }
                } catch (\Exception $e) {
                    \Log::warning('Admin-only login user check failed: ' . $e->getMessage());
                }
            }

            return $adminLogin;
        }

        // Regular login flow (check users first, SKIP admin check for mobile)
        $userLogin = $this->attemptUserLogin($username, $password);
        if ($userLogin['success']) {
            return $userLogin;
        }

        try {
            $user = DB::table('users')
                ->where(function ($query) use ($username) {
                    $query->whereRaw('BINARY username = ?', [$username])
                          ->orWhere('email', $username);
                })
                ->first();

            if ($user) {
                // User exists but login failed - return the user login error
                return $userLogin;
            }
        } catch (\Exception $e) {
            \Log::warning('login user existence lookup failed: ' . $e->getMessage());
        }

        // For non-admin-only login (mobile), do NOT check admin table
        // Return generic error instead
        return [
            'success' => false,
            'errors' => [
                'username' => 'Invalid username or password'
            ]
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
