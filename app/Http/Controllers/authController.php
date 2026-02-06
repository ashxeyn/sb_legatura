<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\accounts\accountRequest;
use App\Services\authService;
use App\Services\psgcApiService;
use App\Models\accounts\accountClass;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class authController extends Controller
{
    protected $authService;
    protected $accountClass;
    protected $psgcService;

    public function __construct()
    {
        $this->authService = new authService();
        $this->accountClass = new accountClass();
        $this->psgcService = new psgcApiService();
    }



    public function showLoginForm()
    {
        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Login form data',
                'form_config' => [
                    'action' => '/accounts/login',
                    'method' => 'POST',
                    'fields' => [
                        'username' => [
                            'type' => 'text',
                            'required' => true,
                            'label' => 'Username or Email'
                        ],
                        'password' => [
                            'type' => 'password',
                            'required' => true,
                            'label' => 'Password'
                        ]
                    ]
                ]
            ], 200);
        }

        return view('accounts.login');
    }

    // Web Login wrapper (delegates to API for JSON requests)
    public function login(Request $request)
    {
        // If client expects JSON, reuse API login
        if ($request->expectsJson()) {
            return $this->apiLogin($request);
        }

        // Validate basic fields
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $result = $this->authService->login($request->username, $request->password);

            if (!empty($result['success'])) {
                // Persist session user for web flows
                Session::put('user', $result['user'] ?? null);

                // Also store the high-level user type (e.g., 'user' or 'admin')
                if (isset($result['userType'])) {
                    Session::put('userType', $result['userType']);
                }

                // Default current role to user_type if present, else fall back to userType
                if (!empty($result['user']) && isset($result['user']->user_type)) {
                    Session::put('current_role', $result['user']->user_type);
                } elseif (!empty($result['userType'])) {
                    Session::put('current_role', $result['userType']);
                }

                // Route admins to the Admin dashboard, owners/contractors to their homepages, users to main dashboard
                if (!empty($result['userType']) && $result['userType'] === 'admin') {
                    return redirect('/admin/dashboard')->with('success', 'Logged in successfully');
                }
                if (!empty($result['user']) && isset($result['user']->user_type) && $result['user']->user_type === 'property_owner') {
                    return redirect('/owner/homepage')->with('success', 'Logged in successfully');
                }
                if (!empty($result['user']) && isset($result['user']->user_type) && $result['user']->user_type === 'contractor') {
                    return redirect('/contractor/homepage')->with('success', 'Logged in successfully');
                }
                return redirect('/dashboard')->with('success', 'Logged in successfully');
            }

            if (!empty($result['errors'])) {
                $hasUsernameError = array_key_exists('username', $result['errors']);
                $hasPasswordError = array_key_exists('password', $result['errors']);
                if ($hasUsernameError && !$hasPasswordError) {
                    $request->session()->forget('_old_input');
                    return back()->withErrors($result['errors']);
                }
                return back()->withErrors($result['errors'])->withInput();
            }

            return back()->with('error', $result['message'] ?? 'Invalid credentials')->withInput();
        } catch (\Exception $e) {
            \Log::error('Web login error: ' . $e->getMessage());
            return back()->with('error', 'An error occurred during login')->withInput();
        }
    }

    // Show signup form
    public function showSignupForm()
    {
        // Values ng dropdowns
        $contractorTypes = $this->accountClass->getContractorTypes();
        $occupations = $this->accountClass->getOccupations();
        $validIds = $this->accountClass->getValidIds();
        $provinces = $this->psgcService->getProvinces();
        $picabCategories = $this->accountClass->getPicabCategories();

        if (request()->expectsJson()) {
            // Ensure valid_ids is properly formatted as an array
            $validIdsArray = $validIds->map(function ($item) {
                return [
                    'id' => $item->id ?? null,
                    'name' => $item->name ?? $item->valid_id_name ?? null
                ];
            })->values()->toArray();

            // Determine requested user_type to scope the returned form data
            $userType = strtolower(trim(request()->query('user_type') ?? Session::get('signup_user_type') ?? ''));

            if ($userType === 'property_owner' || $userType === 'owner') {
                $responseData = [
                    'occupations' => $occupations,
                    'valid_ids' => $validIdsArray,
                    'provinces' => $provinces
                ];
            } elseif ($userType === 'contractor') {
                $responseData = [
                    'contractor_types' => $contractorTypes,
                    'picab_categories' => $picabCategories,
                    'provinces' => $provinces,
                    'valid_ids' => $validIdsArray
                ];
            } else {
                // Backwards-compatible default: return all fields
                $responseData = [
                    'contractor_types' => $contractorTypes,
                    'occupations' => $occupations,
                    'valid_ids' => $validIdsArray,
                    'provinces' => $provinces,
                    'picab_categories' => $picabCategories
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Signup form data',
                'data' => $responseData
            ], 200);
        }

        return view('accounts.signup', compact('contractorTypes', 'occupations', 'validIds', 'provinces', 'picabCategories'));
    }

    // Handle role selection
    public function selectRole(accountRequest $request)
    {
        Session::put('signup_user_type', $request->user_type);
        Session::put('signup_step', 1);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Role selected successfully',
                'user_type' => $request->user_type,
                'next_step' => 'step1'
            ], 200);
        } else {
            return response()->json(['success' => true, 'user_type' => $request->user_type]);
        }
    }

    // Handle Contractor Step 1
    public function contractorStep1(accountRequest $request)
    {
        $businessAddress = $request->business_address_street . ', ' .
                          $request->business_address_barangay . ', ' .
                          $request->business_address_city . ', ' .
                          $request->business_address_province . ' ' .
                          $request->business_address_postal;

        // Calculate years of experience from founded_date
        $foundedDate = $request->founded_date ?? null;
        $yearsOfExperience = null;
        if ($foundedDate) {
            try {
                $yearsOfExperience = $this->authService->calculateAge($foundedDate);
            } catch (\Exception $e) {
                \Log::warning('Failed to calculate years_of_experience from founded_date: ' . $e->getMessage());
                $yearsOfExperience = 0;
            }
        }

        $step1Data = [
            'company_name' => $request->company_name,
            'company_phone' => $request->company_phone,
            'years_of_experience' => $yearsOfExperience,
            'type_id' => $request->contractor_type_id,
            'contractor_type_other' => $request->contractor_type_other_text,
            'services_offered' => $request->services_offered,
            'business_address' => $businessAddress,
            'company_website' => $request->company_website,
            'company_social_media' => $request->company_social_media
        ];

        // Para istore lang to yung sa step 1 inputs
        // Only treat as switch mode if user is logged in na and verified signup na
        $user = Session::get('user');
        if ($user) {
            Session::put('switch_contractor_step1', $step1Data);
        }

        Session::put('contractor_step1', $step1Data);

        Session::put('signup_step', 2);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Contractor step 1 completed',
                'step' => 2,
                'next_step' => 'contractor_step2'
            ], 200);
        }

        return response()->json(['success' => true, 'step' => 2]);
    }

    // Handle Contractor Step 2
    public function contractorStep2(accountRequest $request)
    {

        // Rate-limit OTP sends per email/IP
        $normalizedEmail = !empty($request->company_email) ? strtolower(trim($request->company_email)) : null;
        $clientIp = $request->ip();
        $sendKeyBase = $normalizedEmail ? 'otp_send_email_' . $normalizedEmail : 'otp_send_ip_' . $clientIp;
        $sendLimit = (int)config('otp.send_limit_per_hour', 5);
        try {
            $hourKey = $sendKeyBase . '_' . date('YmdH');
            $current = Cache::get($hourKey, 0);
            if ($current >= $sendLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP send limit reached. Please try again later.'
                ], 429);
            }
        } catch (\Throwable $e) {
            \Log::warning('OTP send rate-limit check failed: ' . $e->getMessage());
        }

        // Generate and send OTP
        $otp = $this->authService->generateOtp();
        $otpHash = $this->authService->hashOtp($otp);
        $this->authService->sendOtpEmail($request->company_email, $otp);

        // Store in session (include issued timestamp for reliable verification)
        Session::put('contractor_step2', [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'company_email' => $request->company_email,
            'password' => $request->password,
            'otp_hash' => $otpHash,
            'otp_issued_at' => now()->timestamp
        ]);

        Session::put('signup_step', 3);

        // Also cache OTP hash for mobile clients that cannot maintain session cookies.
        try {
            if (!empty($request->company_email)) {
                $normalizedEmail = strtolower(trim($request->company_email));
                // Store OTP metadata (hash + issued_at) so verification can validate TTL reliably
                $meta = [
                    'hash' => $otpHash,
                    'issued_at' => now()->timestamp
                ];
                $ttl = (int)config('otp.ttl_seconds', 900);
                Cache::put('signup_otp_' . $normalizedEmail, $meta, now()->addSeconds($ttl));
                \Log::info('Cached signup OTP meta for ' . $normalizedEmail);
                // Also store an IP->email mapping for short-term fallback
                try {
                    $clientIp = $request->ip();
                    if ($clientIp) {
                        Cache::put('signup_otp_ip_' . $clientIp, $normalizedEmail, now()->addSeconds($ttl));
                        \Log::info('Cached signup OTP IP mapping for ' . $clientIp);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to cache signup OTP IP mapping: ' . $e->getMessage());
                }

                // Also generate a short-lived token to lookup OTP (for stateless clients)
                try {
                    $otpToken = bin2hex(random_bytes(8));
                    Cache::put('signup_otp_token_' . $otpToken, $meta, now()->addSeconds($ttl));
                    \Log::info('Generated signup OTP token for ' . $normalizedEmail);
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate OTP token: ' . $e->getMessage());
                    $otpToken = null;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to cache signup OTP: ' . $e->getMessage());
        }

        $expiresAt = now()->addSeconds($ttl ?? (int)config('otp.ttl_seconds', 900))->toISOString();
        $masked = null;
        if (!empty($request->company_email)) {
            $parts = explode('@', $request->company_email);
            if (count($parts) === 2) {
                $user = $parts[0];
                $domain = $parts[1];
                $masked = substr($user, 0, 1) . str_repeat('*', max(0, min(3, strlen($user) - 1))) . '@' . $domain;
            } else {
                $masked = $request->company_email;
            }
        }

        if ($request->expectsJson()) {

            // Increment send counter (hourly)
            try {
                $hourKeyToInc = ($sendKeyBase ?? ('otp_send_ip_' . $clientIp)) . '_' . date('YmdH');
                Cache::increment($hourKeyToInc);
                Cache::put($hourKeyToInc, Cache::get($hourKeyToInc), now()->addHour());
            } catch (\Throwable $e) {
                \Log::warning('Failed to increment OTP send counter: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to email',
                'step' => 3,
                'next_step' => 'verify_otp',
                'otp_token' => $otpToken ?? null,
                'expires_at' => $expiresAt,
                'masked_destination' => $masked
            ], 200);
        } else {

            return response()->json(['success' => true, 'step' => 3, 'message' => 'OTP sent to email']);
        }
    }

    // Contractor Step 3
    public function contractorVerifyOtp(accountRequest $request)
    {
        $step2Data = Session::get('contractor_step2');

        // If the signup step already moved past OTP verification,
        // return a friendly idempotent success so clients that retry
        // don't get a confusing "missing identifier" error.
        $signupStep = Session::get('signup_step');
        if (!empty($signupStep) && (int)$signupStep >= 4) {
            \Log::info('contractorVerifyOtp called but signup_step already >=4; returning already verified');
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP already verified',
                    'step' => (int)$signupStep,
                    'next_step' => 'contractor_step4'
                ], 200);
            }
            return response()->json(['success' => true, 'step' => (int)$signupStep], 200);
        }

        // Input OTP
        $inputOtp = $request->input('otp') ?? $request->otp ?? null;
        if (empty($inputOtp)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP is required',
                    'errors' => ['otp' => ['OTP is required']]
                ], 422);
            }
            return response()->json(['success' => false, 'errors' => ['otp' => ['OTP is required']]], 422);
        }

        $otpMeta = null;
        $normalizedEmail = null;
        $otpToken = $request->input('otp_token') ?? null;
        // Require either: (1) session-stored OTP (web flow), (2) company email in request/header, or (3) otp_token (stateless client)
        $hasSessionOtp = is_array($step2Data) && isset($step2Data['otp_hash']);
        $hasEmailInRequest = false;
        $clientIp = $request->ip();
        $possibleKeys = ['company_email', 'companyEmail', 'email', 'identifier'];
        foreach ($possibleKeys as $key) {
            $val = $request->input($key);
            if (!empty($val)) {
                $hasEmailInRequest = true;
                break;
            }
        }
        if (!$hasEmailInRequest) {
            $hdr = $request->header('X-Company-Email') ?: $request->header('X-User-Email');
            if ($hdr) {
                $hasEmailInRequest = true;
            }
        }

        // If no explicit email/token/session, try IP->email mapping as an identifier
        if (!$hasSessionOtp && empty($otpToken) && !$hasEmailInRequest && $clientIp) {
            try {
                $ipMapped = Cache::get('signup_otp_ip_' . $clientIp);
                if (!empty($ipMapped)) {
                    \Log::info('Using IP-mapped email for OTP identification: ' . $clientIp . ' -> ' . $ipMapped);
                    $hasEmailInRequest = true;
                    // keep mapped email for later lookup
                    $mappedEmailForIp = $ipMapped;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to read IP-mapped OTP email: ' . $e->getMessage());
            }
        }

        if (!$hasSessionOtp && empty($otpToken) && !$hasEmailInRequest) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing identifier: provide company_email (or X-Company-Email header) or otp_token for stateless clients.',
                    'errors' => ['identifier' => ['company_email or otp_token required']]
                ], 400);
            }
            return response()->json(['success' => false, 'errors' => ['identifier' => ['company_email or otp_token required']]], 400);
        }
        $clientIp = $request->ip();

        // 1) Prefer session-stored OTP (web flow)
        if (is_array($step2Data) && isset($step2Data['otp_hash'])) {
            $otpMeta = [
                'hash' => $step2Data['otp_hash'],
                'issued_at' => $step2Data['otp_issued_at'] ?? null
            ];
            $normalizedEmail = isset($step2Data['company_email']) ? strtolower(trim($step2Data['company_email'])) : null;
            \Log::info('Found OTP meta in session for contractor_step2');
        }

        // 2) Check token-based lookup (stateless/mobile)
        if (!$otpMeta && !empty($otpToken)) {
            $meta = Cache::get('signup_otp_token_' . $otpToken);
            if ($meta) {
                $otpMeta = $meta;
                \Log::info('Lookup OTP meta by token ' . $otpToken . ' -> HIT');
            } else {
                \Log::info('Lookup OTP meta by token ' . $otpToken . ' -> MISS');
            }
        }

        // 3) Check email-based lookup
        if (!$otpMeta) {
            $possibleKeys = ['company_email', 'companyEmail', 'email', 'identifier'];
            $companyEmail = null;
            foreach ($possibleKeys as $key) {
                $val = $request->input($key);
                if (!empty($val)) {
                    // If the key is "identifier" decide whether it's an email or a token
                    if ($key === 'identifier') {
                        if (strpos($val, '@') !== false) {
                            $companyEmail = $val;
                            \Log::info('Found company email in request identifier');
                            break;
                        } else {
                            // treat as otp token for stateless clients
                            $otpToken = $val;
                            \Log::info('Found otp_token in request identifier');
                            break;
                        }
                    }
                    $companyEmail = $val;
                    \Log::info('Found company email in request key: ' . $key);
                    break;
                }
            }
            if (!$companyEmail) {
                $hdr = $request->header('X-Company-Email') ?: $request->header('X-User-Email');
                if ($hdr) {
                    $companyEmail = $hdr;
                }
                // If we earlier mapped IP to an email, prefer that
                if (empty($companyEmail) && isset($mappedEmailForIp) && !empty($mappedEmailForIp)) {
                    $companyEmail = $mappedEmailForIp;
                    \Log::info('Using mapped IP email for lookup: ' . $companyEmail);
                }
            }

            if ($companyEmail) {
                $normalizedEmail = strtolower(trim($companyEmail));
                $meta = Cache::get('signup_otp_' . $normalizedEmail);
                if ($meta) {
                    $otpMeta = $meta;
                    \Log::info('Lookup OTP meta by email ' . $normalizedEmail . ' -> HIT');
                } else {
                    \Log::info('Lookup OTP meta by email ' . $normalizedEmail . ' -> MISS');
                }
            }
        }

        // 4) IP fallback mapping
        if (!$otpMeta && $clientIp) {
            try {
                $mapped = Cache::get('signup_otp_ip_' . $clientIp);
                if ($mapped) {
                    $normalizedEmail = $mapped;
                    $meta = Cache::get('signup_otp_' . $mapped);
                    if ($meta) {
                        $otpMeta = $meta;
                        \Log::info('IP fallback lookup for ' . $clientIp . ' -> ' . ($mapped ?: 'NONE') . ' ; OTP meta HIT');
                    } else {
                        \Log::info('IP fallback lookup for ' . $clientIp . ' -> ' . ($mapped ?: 'NONE') . ' ; OTP meta MISS');
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed IP fallback lookup: ' . $e->getMessage());
            }
        }

        if (!$otpMeta || !isset($otpMeta['hash'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP not found (expired or missing). Please request a new code.',
                    'errors' => ['otp' => ['OTP not found or expired']]
                ], 422);
            }
            return response()->json(['success' => false, 'errors' => ['otp' => ['OTP not found or expired']]], 422);
        }

        // Validate TTL using issued_at if available to avoid premature invalidation
        $otpTtlSeconds = 15 * 60; // 15 minutes
        $graceSeconds = 30; // small grace window for clock skew
        if (!empty($otpMeta['issued_at'])) {
            $issued = (int) $otpMeta['issued_at'];
            if (now()->timestamp > ($issued + $otpTtlSeconds + $graceSeconds)) {
                // OTP expired
                // Cleanup stale cache entries
                try {
                    if (!empty($normalizedEmail)) {
                        Cache::forget('signup_otp_' . $normalizedEmail);
                    }
                    if (!empty($otpToken)) {
                        Cache::forget('signup_otp_token_' . $otpToken);
                    }
                    if (!empty($clientIp)) {
                        Cache::forget('signup_otp_ip_' . $clientIp);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to cleanup expired OTP cache: ' . $e->getMessage());
                }

                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'OTP has expired. Please request a new code.',
                        'errors' => ['otp' => ['OTP expired']]
                    ], 422);
                }
                return response()->json(['success' => false, 'errors' => ['otp' => ['OTP expired']]], 422);
            }
        }

        // Verify attempts rate-limiting to prevent brute-force
        $attemptKeyBase = null;
        try {
            if (!empty($normalizedEmail)) {
                $attemptKeyBase = 'otp_verify_attempts_' . $normalizedEmail;
            } elseif (!empty($otpToken)) {
                $attemptKeyBase = 'otp_verify_attempts_token_' . $otpToken;
            } else {
                $attemptKeyBase = 'otp_verify_attempts_ip_' . $clientIp;
            }
            $attemptLimit = (int)config('otp.verify_attempts_limit', 5);
            $attempts = Cache::get($attemptKeyBase, 0);
            if ($attempts >= $attemptLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many failed OTP attempts. Please try again later.'
                ], 429);
            }
        } catch (\Throwable $e) {
            \Log::warning('OTP verify rate-limit check failed: ' . $e->getMessage());
        }

        // Attempt verification and prevent race/reuse by using cache lock when available
        $lockName = null;
        if (!empty($normalizedEmail)) {
            $lockName = 'signup_otp_lock_' . $normalizedEmail;
        } elseif (!empty($otpToken)) {
            $lockName = 'signup_otp_lock_token_' . $otpToken;
        }

        $blockSeconds = (int)config('otp.verify_block_seconds', 900);

        $verifyAndCleanup = function () use ($inputOtp, $otpMeta, $normalizedEmail, $otpToken, $clientIp, $request, $attemptKeyBase, $blockSeconds) {
            if (!$this->authService->verifyOtp($inputOtp, $otpMeta['hash'])) {
                // Increment failed attempt counter
                try {
                    if (!empty($attemptKeyBase)) {
                        Cache::increment($attemptKeyBase);
                        Cache::put($attemptKeyBase, Cache::get($attemptKeyBase), now()->addSeconds($blockSeconds));
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Failed to increment OTP verify attempts: ' . $e->getMessage());
                }
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid OTP',
                        'errors' => ['otp' => ['Invalid OTP']]
                    ], 422);
                }
                return response()->json(['success' => false, 'errors' => ['otp' => ['Invalid OTP']]], 422);
            }

            // On success, clear failed attempts counter
            try {
                if (!empty($attemptKeyBase)) {
                    Cache::forget($attemptKeyBase);
                }
            } catch (\Throwable $e) {
                \Log::warning('Failed to clear OTP verify attempts: ' . $e->getMessage());
            }

            // On success, remove any cached tokens and session OTP to prevent reuse
            try {
                if (!empty($normalizedEmail)) {
                    Cache::forget('signup_otp_' . $normalizedEmail);
                }
                if (!empty($otpToken)) {
                    Cache::forget('signup_otp_token_' . $otpToken);
                }
                if (!empty($clientIp)) {
                    Cache::forget('signup_otp_ip_' . $clientIp);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to cleanup OTP cache after verify: ' . $e->getMessage());
            }

            // Keep otp_hash in session until final registration to allow creating
            // the user record (DB requires OTP_hash). We already cleared cached
            // OTP tokens and IP mappings above to prevent reuse.
            try {
                $s = Session::get('contractor_step2', []);
                if (is_array($s)) {
                    // Mark that OTP was verified, but retain the stored hash for final step
                    $s['otp_verified'] = true;
                    Session::put('contractor_step2', $s);
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to update session after OTP verify: ' . $e->getMessage());
            }

            Session::put('signup_step', 4);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully',
                    'step' => 4,
                    'next_step' => 'contractor_step4'
                ], 200);
            }
            return response()->json(['success' => true, 'step' => 4]);
        };

        if ($lockName) {
            try {
                if (method_exists(Cache::store(), 'lock')) {
                    $lock = Cache::lock($lockName, 5);
                    return $lock->block(3, function () use ($verifyAndCleanup) {
                        return $verifyAndCleanup();
                    });
                }
            } catch (\Throwable $e) {
                \Log::warning('OTP verification lock failed or unsupported: ' . $e->getMessage());
            }
        }

        // Fallback: no lock available
        return $verifyAndCleanup();
    }

    // Contractor Step 4
    public function contractorStep4(accountRequest $request)
    {
        // Handle file upload
        $dtiSecPath = $request->file('dti_sec_registration_photo')->store('DTI_SEC', 'public');

        Session::put('contractor_step4', [
            'picab_number' => $request->picab_number,
            'picab_category' => $request->picab_category,
            'picab_expiration_date' => $request->picab_expiration_date,
            'business_permit_number' => $request->business_permit_number,
            'business_permit_city' => $request->business_permit_city,
            'business_permit_expiration' => $request->business_permit_expiration,
            'tin_business_reg_number' => $request->tin_business_reg_number,
            'dti_sec_registration_photo' => $dtiSecPath
        ]);

        Session::put('signup_step', 5);

        return response()->json(['success' => true, 'step' => 5]);
    }

    // Handle Contractor Final Step
    public function contractorFinalStep(accountRequest $request)
    {
        // Get all session data (web flow)
        $step1 = Session::get('contractor_step1');
        $step2 = Session::get('contractor_step2');
        $step4 = Session::get('contractor_step4');

        // If session is not available (mobile/stateless clients), allow passing
        // the step data directly in the request as JSON fields: step1_data, step2_data, step4_data
        try {
            if (!$step1 && $request->input('step1_data')) {
                $decoded = json_decode($request->input('step1_data'), true);
                if (is_array($decoded)) $step1 = $decoded;
            }
            if (!$step2 && $request->input('step2_data')) {
                $decoded = json_decode($request->input('step2_data'), true);
                if (is_array($decoded)) $step2 = $decoded;
            }
            if (!$step4 && $request->input('step4_data')) {
                $decoded = json_decode($request->input('step4_data'), true);
                if (is_array($decoded)) $step4 = $decoded;
            }
        } catch (\Throwable $e) {
            \Log::warning('Failed to decode step data from request: ' . $e->getMessage());
        }

        // Normalize step arrays and provide safe defaults to avoid undefined keys
        $step1 = is_array($step1) ? $step1 : (is_object($step1) ? (array)$step1 : []);
        $step2 = is_array($step2) ? $step2 : (is_object($step2) ? (array)$step2 : []);
        $step4 = is_array($step4) ? $step4 : (is_object($step4) ? (array)$step4 : []);

        // Ensure years_of_experience exists; compute from founded_date if present
        if (empty($step1['years_of_experience'])) {
            if (!empty($step1['founded_date'])) {
                try {
                    $step1['years_of_experience'] = $this->authService->calculateAge($step1['founded_date']);
                } catch (\Throwable $e) {
                    \Log::warning('Failed to calculate years_of_experience from founded_date in final step: ' . $e->getMessage());
                    $step1['years_of_experience'] = 0;
                }
            } else {
                $step1['years_of_experience'] = 0;
            }
        }

        // Provide other default values to avoid undefined array key notices
        $step1['company_name'] = $step1['company_name'] ?? '';
        $step1['company_phone'] = $step1['company_phone'] ?? '';
        $step1['type_id'] = $step1['type_id'] ?? null;
        $step1['contractor_type_other'] = $step1['contractor_type_other'] ?? null;
        $step1['services_offered'] = $step1['services_offered'] ?? '';
        $step1['business_address'] = $step1['business_address'] ?? '';
        $step1['company_website'] = $step1['company_website'] ?? null;
        $step1['company_social_media'] = $step1['company_social_media'] ?? null;

        // Ensure step2 defaults
        $step2['username'] = $step2['username'] ?? null;
        $step2['company_email'] = $step2['company_email'] ?? ($step2['email'] ?? null);
        $step2['password'] = $step2['password'] ?? null;
        $step2['otp_hash'] = $step2['otp_hash'] ?? null;

        // Ensure step4 defaults
        $step4['picab_number'] = $step4['picab_number'] ?? '';
        $step4['picab_category'] = $step4['picab_category'] ?? '';
        $step4['picab_expiration_date'] = $step4['picab_expiration_date'] ?? null;
        $step4['business_permit_number'] = $step4['business_permit_number'] ?? '';
        $step4['business_permit_city'] = $step4['business_permit_city'] ?? '';
        $step4['business_permit_expiration'] = $step4['business_permit_expiration'] ?? null;
        $step4['tin_business_reg_number'] = $step4['tin_business_reg_number'] ?? '';

        // Map common mobile keys to backend keys (mobile may send contractor_type_id)
        if (isset($step1['contractor_type_id']) && empty($step1['type_id'])) {
            $step1['type_id'] = $step1['contractor_type_id'];
        }

        // Check if all required session data exists
        if (!$step1 || !$step2 || !$step4) {
            $missing = [];
            if (!$step1) {
                $missing[] = 'Step 1 data';
            }
            if (!$step2) {
                $missing[] = 'Step 2 data';
            }
            if (!$step4) {
                $missing[] = 'Step 4 data';
            }

            return response()->json([
                'success' => false,
                'errors' => ['Session expired. Missing: ' . implode(', ', $missing) . '. Please start the registration process again.']
            ], 400);
        }

        $profilePicPath = null;
        if ($request->hasFile('profile_pic')) {
            $profilePicPath = $request->file('profile_pic')->store('profiles', 'public');
        }

        // If step4 was provided as part of the request and contains an uploaded file
        // (e.g., `dti_sec_registration_photo`), handle the uploaded file here and
        // set the path inside $step4 accordingly.
        if (is_array($step4) && $request->hasFile('dti_sec_registration_photo')) {
            try {
                $dtiPath = $request->file('dti_sec_registration_photo')->store('DTI_SEC', 'public');
                $step4['dti_sec_registration_photo'] = $dtiPath;
            } catch (\Throwable $e) {
                \Log::warning('Failed to store dti_sec_registration_photo from final request: ' . $e->getMessage());
            }
        }

        // Validate valid_id_id to avoid FK constraint errors
        $validIdCandidate = $step4['valid_id_id'] ?? null;
        if (!empty($validIdCandidate)) {
            $validIdExists = DB::table('valid_ids')->where('id', $validIdCandidate)->exists();
            if (!$validIdExists) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['valid_id_id' => ['Invalid valid ID selected']]
                    ], 422);
                } else {
                    return response()->json([
                        'success' => false,
                        'errors' => ['valid_id_id' => ['Invalid valid ID selected']]
                    ], 422);
                }
            }
        }

        // Create user
        // If OTP hash not present in session/request, try to lookup from cache using
        // company_email or otp_token (useful for stateless mobile flow).
        if ((empty($step2['otp_hash']) || !isset($step2['otp_hash'])) ) {
            $foundHash = null;
            try {
                $normalizedEmail = isset($step2['company_email']) ? strtolower(trim($step2['company_email'])) : (isset($step2['email']) ? strtolower(trim($step2['email'])) : null);
                $otpTokenFromRequest = $request->input('otp_token') ?? null;

                if (!empty($otpTokenFromRequest)) {
                    $meta = Cache::get('signup_otp_token_' . $otpTokenFromRequest);
                    if (!empty($meta) && isset($meta['hash'])) {
                        $foundHash = $meta['hash'];
                        \Log::info('Found OTP hash via otp_token lookup in final step');
                    }
                }

                if (!$foundHash && !empty($normalizedEmail)) {
                    $meta = Cache::get('signup_otp_' . $normalizedEmail);
                    if (!empty($meta) && isset($meta['hash'])) {
                        $foundHash = $meta['hash'];
                        \Log::info('Found OTP hash via email lookup in final step for ' . $normalizedEmail);
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('OTP hash lookup failed in final step: ' . $e->getMessage());
            }

            if ($foundHash) {
                $step2['otp_hash'] = $foundHash;
            }
        }

        // If user already exists (username or email), reuse it to avoid duplicate inserts
        $existingUser = null;
        try {
            if (!empty($step2['username']) || !empty($step2['company_email'])) {
                $q = DB::table('users');
                if (!empty($step2['username'])) {
                    $q->where('username', $step2['username']);
                }
                if (!empty($step2['company_email'])) {
                    $q->orWhere('email', $step2['company_email']);
                }
                $existingUser = $q->first();
            }
        } catch (\Throwable $e) {
            \Log::warning('Existing user lookup failed: ' . $e->getMessage());
        }

        if ($existingUser) {
            $userId = $existingUser->user_id ?? $existingUser->id ?? null;
            try { \Log::info('contractorFinalStep: reusing existing user id -> ' . var_export($userId, true)); } catch (\Throwable $e) {}

            // If contractor already exists for this user, return success rather than duplicate
            try {
                $existingContractor = DB::table('contractors')->where('user_id', $userId)->first();
                if ($existingContractor) {
                    return response()->json(['success' => true, 'message' => 'Account already exists', 'user_id' => $userId, 'contractor_id' => $existingContractor->contractor_id], 200);
                }
            } catch (\Throwable $e) {
                \Log::warning('Existing contractor lookup failed: ' . $e->getMessage());
            }
        } else {
            $userId = $this->accountClass->createUser([
                'profile_pic' => $profilePicPath,
                'username' => $step2['username'] ?? null,
                'email' => $step2['company_email'] ?? ($step2['email'] ?? null),
                'password_hash' => isset($step2['password']) ? $this->authService->hashPassword($step2['password']) : null,
                'OTP_hash' => $step2['otp_hash'] ?? null,
                'user_type' => 'contractor'
            ]);

            // Log creation result for debugging (temporary)
            try {
                \Log::info('contractorFinalStep: created user id -> ' . var_export($userId, true));
                \Log::info('contractorFinalStep: step2 keys -> ' . json_encode(array_keys((array)$step2)));
            } catch (\Throwable $e) {
                // ignore logging failure
            }
        }

        // Create contractor
        $contractorId = $this->accountClass->createContractor([
            'user_id' => $userId,
            'company_name' => $step1['company_name'],
            'years_of_experience' => $step1['years_of_experience'],
            'type_id' => $step1['type_id'],
            'contractor_type_other' => $step1['contractor_type_other'] ?? null,
            'services_offered' => $step1['services_offered'],
            'business_address' => $step1['business_address'],
            'company_email' => $step2['company_email'],
            'company_phone' => $step1['company_phone'],
            'company_website' => $step1['company_website'],
            'company_social_media' => $step1['company_social_media'],
            'picab_number' => $step4['picab_number'],
            'picab_category' => $step4['picab_category'],
            'picab_expiration_date' => $step4['picab_expiration_date'],
            'business_permit_number' => $step4['business_permit_number'],
            'business_permit_city' => $step4['business_permit_city'],
            'business_permit_expiration' => $step4['business_permit_expiration'],
            'tin_business_reg_number' => $step4['tin_business_reg_number'],
            'dti_sec_registration_photo' => $step4['dti_sec_registration_photo']
        ]);

        try {
            \Log::info('contractorFinalStep: created contractor id -> ' . var_export($contractorId, true));
        } catch (\Throwable $e) {
        }

        // Create contractor user
        $this->accountClass->createContractorUser([
            'contractor_id' => $contractorId,
            'user_id' => $userId,
            'first_name' => $step2['first_name'],
            'middle_name' => $step2['middle_name'],
            'last_name' => $step2['last_name'],
            'phone_number' => $step1['company_phone']
        ]);

        // Clear session
        Session::forget(['signup_user_type', 'signup_step', 'contractor_step1', 'contractor_step2', 'contractor_step4']);

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please wait for admin approval.',
                'user_id' => $userId,
                'contractor_id' => $contractorId,
                'redirect_url' => '/accounts/login'
            ], 201);
        } else {

            return response()->json([
                'success' => true,
                'message' => 'Registration successful! Please wait for admin approval.',
                'redirect' => '/accounts/login'
            ]);
        }
    }

    // Handle Property Owner Step 1
    public function propertyOwnerStep1(accountRequest $request)
    {
        // Age
        $age = $this->authService->calculateAge($request->date_of_birth);

        // Combine address
        $address = $request->owner_address_street . ', ' .
                   $request->owner_address_barangay . ', ' .
                   $request->owner_address_city . ', ' .
                   $request->owner_address_province . ', ' .
                   $request->owner_address_postal;

        $step1Data = [
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'occupation_id' => $request->occupation_id,
            'occupation_other' => $request->occupation_other_text,
            'date_of_birth' => $request->date_of_birth,
            'phone_number' => $request->phone_number,
            'age' => $age,
            'address' => $address
        ];

        // Only treat as switch mode if user is logged in and verified na ang signup (just like the contractor)
        $user = Session::get('user');
        if ($user) {
            Session::put('switch_owner_step1', $step1Data);
        }

        Session::put('owner_step1', $step1Data);

        Session::put('signup_step', 2);

        if ($request->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => 'Property owner step 1 completed',
                'step' => 2,
                'next_step' => 'property_owner_step2'
            ], 200);
        } else {

            return response()->json(['success' => true, 'step' => 2]);
        }
    }

    // Handle Property Owner Step 2
    public function propertyOwnerStep2(accountRequest $request)
    {

        // Generate and send OTP
        $otp = $this->authService->generateOtp();
        $otpHash = $this->authService->hashOtp($otp);
        $this->authService->sendOtpEmail($request->email, $otp);

        // Store in session (include issued timestamp for reliable verification)
        Session::put('owner_step2', [
            'username' => $request->username,
            'email' => $request->email,
            'password' => $request->password,
            'otp_hash' => $otpHash,
            'otp_issued_at' => now()->timestamp
        ]);

        Session::put('signup_step', 3);

        // Also cache OTP hash for mobile/stateless clients
        try {
            if (!empty($request->email)) {
                $normalizedEmail = strtolower(trim($request->email));
                $meta = [
                    'hash' => $otpHash,
                    'issued_at' => now()->timestamp
                ];
                $ttl = (int)config('otp.ttl_seconds', 900);
                Cache::put('signup_otp_owner_' . $normalizedEmail, $meta, now()->addSeconds($ttl));
                // IP mapping
                try {
                    $clientIp = $request->ip();
                    if ($clientIp) {
                        Cache::put('signup_otp_owner_ip_' . $clientIp, $normalizedEmail, now()->addSeconds($ttl));
                    }
                } catch (\Exception $e) {
                    \Log::warning('Failed to cache signup OTP IP mapping (owner): ' . $e->getMessage());
                }

                // Short-lived token for stateless lookup
                try {
                    $otpToken = bin2hex(random_bytes(8));
                    Cache::put('signup_otp_token_owner_' . $otpToken, $meta, now()->addSeconds($ttl));
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate OTP token (owner): ' . $e->getMessage());
                    $otpToken = null;
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to cache signup OTP (owner): ' . $e->getMessage());
        }

        $expiresAt = now()->addSeconds($ttl ?? (int)config('otp.ttl_seconds', 900))->toISOString();

        if ($request->expectsJson()) {
                return response()->json([
                'success' => true,
                'message' => 'OTP sent to email',
                'step' => 3,
                'next_step' => 'verify_otp',
                'otp_token' => $otpToken ?? null,
                'expires_at' => $expiresAt
            ], 200);
        } else {
            return response()->json(['success' => true, 'step' => 3, 'message' => 'OTP sent to email']);
        }
    }

    // Property Owner Step 3
    public function propertyOwnerVerifyOtp(accountRequest $request)
    {
        // Frontend integration notes:
        // - Fetch CSRF token first via GET /sanctum/csrf-cookie when using cookie-based auth.
        // - When calling this endpoint from the browser/fetch, include credentials so cookies are sent:
        //   fetch('/api/signup/property-owner/step3/verify-otp', { method: 'POST', credentials: 'include', ... })
        // - Mobile clients using stateless tokens should use the returned `otp_token` from step2.

        $step2Data = Session::get('owner_step2');

        // Log incoming request payload and identifying headers for debugging mobile clients
        try {
            \Log::info('propertyOwnerVerifyOtp called. request_input=' . json_encode($request->all()));
            \Log::info('propertyOwnerVerifyOtp headers: X-User-Email=' . $request->header('X-User-Email') . ' X-Email=' . $request->header('X-Email'));
        } catch (\Throwable $e) {
            // non-fatal logging failure
        }
        // Idempotent success if already past OTP
        $signupStep = Session::get('signup_step');
        if (!empty($signupStep) && (int)$signupStep >= 4) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'OTP already verified',
                    'step' => (int)$signupStep,
                    'next_step' => 'property_owner_step4'
                ], 200);
            }
            return response()->json(['success' => true, 'step' => (int)$signupStep], 200);
        }

        $inputOtp = $request->input('otp') ?? $request->otp ?? null;
        if (empty($inputOtp)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'OTP is required',
                    'errors' => ['otp' => ['OTP is required']]
                ], 422);
            }
            return response()->json(['success' => false, 'errors' => ['otp' => ['OTP is required']]], 422);
        }

        $otpMeta = null;
        $normalizedEmail = null;
        $otpToken = $request->input('otp_token') ?? null;

        $hasSessionOtp = is_array($step2Data) && isset($step2Data['otp_hash']);
        $hasEmailInRequest = false;
        $clientIp = $request->ip();
        $possibleKeys = ['email', 'identifier'];
        foreach ($possibleKeys as $key) {
            $val = $request->input($key);
            if (!empty($val)) { $hasEmailInRequest = true; break; }
        }
        if (!$hasEmailInRequest) {
            $hdr = $request->header('X-User-Email') ?: $request->header('X-Email');
            if ($hdr) $hasEmailInRequest = true;
        }

        if (!$hasSessionOtp && empty($otpToken) && !$hasEmailInRequest && $clientIp) {
            try {
                $ipMapped = Cache::get('signup_otp_owner_ip_' . $clientIp);
                if (!empty($ipMapped)) {
                    $hasEmailInRequest = true;
                    $mappedEmailForIp = $ipMapped;
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to read IP-mapped OTP email (owner): ' . $e->getMessage());
            }
        }

        if (!$hasSessionOtp && empty($otpToken) && !$hasEmailInRequest) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'otp_identifier_missing',
                    'message' => 'Missing identifier: provide email (or X-User-Email header) or otp_token for stateless clients.',
                    'errors' => ['identifier' => ['email or otp_token required']]
                ], 400);
            }
            return response()->json(['success' => false, 'errors' => ['identifier' => ['email or otp_token required']]], 400);
        }

        // 1) Session-stored OTP
        if (is_array($step2Data) && isset($step2Data['otp_hash'])) {
            $otpMeta = [ 'hash' => $step2Data['otp_hash'], 'issued_at' => $step2Data['otp_issued_at'] ?? null ];
            $normalizedEmail = isset($step2Data['email']) ? strtolower(trim($step2Data['email'])) : null;
        }

        // 2) Token lookup
        if (!$otpMeta && !empty($otpToken)) {
            $meta = Cache::get('signup_otp_token_owner_' . $otpToken);
            if ($meta) { $otpMeta = $meta; \Log::info('Owner OTP lookup by token HIT'); } else { \Log::info('Owner OTP lookup by token MISS'); }
        }

        // 3) Email lookup
        if (!$otpMeta) {
            $emailCandidate = null;
            foreach ($possibleKeys as $key) {
                $val = $request->input($key);
                if (!empty($val)) {
                    if ($key === 'identifier') {
                        if (strpos($val, '@') !== false) { $emailCandidate = $val; break; }
                        else { $otpToken = $val; break; }
                    }
                    $emailCandidate = $val; break;
                }
            }
            if (!$emailCandidate) {
                $hdr = $request->header('X-User-Email') ?: $request->header('X-Email');
                if ($hdr) $emailCandidate = $hdr;
                if (empty($emailCandidate) && isset($mappedEmailForIp) && !empty($mappedEmailForIp)) { $emailCandidate = $mappedEmailForIp; }
            }
            if ($emailCandidate) {
                $normalizedEmail = strtolower(trim($emailCandidate));
                $meta = Cache::get('signup_otp_owner_' . $normalizedEmail);
                if ($meta) { $otpMeta = $meta; \Log::info('Owner OTP lookup by email HIT for ' . $normalizedEmail); } else { \Log::info('Owner OTP lookup by email MISS for ' . $normalizedEmail); }
            }
        }

        // 4) IP fallback
        if (!$otpMeta && $clientIp) {
            try {
                $mapped = Cache::get('signup_otp_owner_ip_' . $clientIp);
                if ($mapped) {
                    $normalizedEmail = $mapped;
                    $meta = Cache::get('signup_otp_owner_' . $mapped);
                    if ($meta) { $otpMeta = $meta; \Log::info('Owner IP fallback OTP HIT for ' . $clientIp); }
                }
            } catch (\Exception $e) {
                \Log::warning('Failed IP fallback lookup (owner): ' . $e->getMessage());
            }
        }

        if (!$otpMeta || !isset($otpMeta['hash'])) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error_code' => 'otp_not_found',
                    'message' => 'OTP not found (expired or missing). Please request a new code.',
                    'errors' => ['otp' => ['OTP not found or expired']]
                ], 422);
            }
            return response()->json(['success' => false, 'errors' => ['otp' => ['OTP not found or expired']]], 422);
        }

        // TTL check
        $otpTtlSeconds = 15 * 60;
        $graceSeconds = 30;
        if (!empty($otpMeta['issued_at'])) {
            $issued = (int) $otpMeta['issued_at'];
            if (now()->timestamp > ($issued + $otpTtlSeconds + $graceSeconds)) {
                try {
                    if (!empty($normalizedEmail)) Cache::forget('signup_otp_owner_' . $normalizedEmail);
                    if (!empty($otpToken)) Cache::forget('signup_otp_token_owner_' . $otpToken);
                    if (!empty($clientIp)) Cache::forget('signup_otp_owner_ip_' . $clientIp);
                } catch (\Exception $e) { \Log::warning('Failed to cleanup expired owner OTP cache: ' . $e->getMessage()); }
                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'error_code' => 'otp_expired', 'message' => 'OTP has expired. Please request a new code.', 'errors' => ['otp' => ['OTP expired']]], 422);
                }
                return response()->json(['success' => false, 'error_code' => 'otp_expired', 'errors' => ['otp' => ['OTP expired']]], 422);
            }
        }

        // Attempt verify
        $attemptKeyBase = null;
        try {
            if (!empty($normalizedEmail)) $attemptKeyBase = 'otp_verify_attempts_' . $normalizedEmail;
            elseif (!empty($otpToken)) $attemptKeyBase = 'otp_verify_attempts_token_' . $otpToken;
            else $attemptKeyBase = 'otp_verify_attempts_ip_' . $clientIp;
            $attemptLimit = (int)config('otp.verify_attempts_limit', 5);
            $attempts = Cache::get($attemptKeyBase, 0);
            if ($attempts >= $attemptLimit) {
                return response()->json(['success' => false, 'message' => 'Too many failed OTP attempts. Please try again later.'], 429);
            }
        } catch (\Throwable $e) { \Log::warning('OTP verify rate-limit check failed (owner): ' . $e->getMessage()); }

        $lockName = null;
        if (!empty($normalizedEmail)) $lockName = 'signup_otp_lock_' . $normalizedEmail;
        elseif (!empty($otpToken)) $lockName = 'signup_otp_lock_token_' . $otpToken;

        $blockSeconds = (int)config('otp.verify_block_seconds', 900);

        $verifyAndCleanup = function () use ($inputOtp, $otpMeta, $normalizedEmail, $otpToken, $clientIp, $request, $attemptKeyBase, $blockSeconds) {
            if (!$this->authService->verifyOtp($inputOtp, $otpMeta['hash'])) {
                try { if (!empty($attemptKeyBase)) { Cache::increment($attemptKeyBase); Cache::put($attemptKeyBase, Cache::get($attemptKeyBase), now()->addSeconds($blockSeconds)); } } catch (\Throwable $e) { \Log::warning('Failed to increment owner OTP verify attempts: ' . $e->getMessage()); }
                if ($request->expectsJson()) return response()->json(['success' => false, 'error_code' => 'invalid_otp', 'message' => 'Invalid OTP', 'errors' => ['otp' => ['Invalid OTP']]], 422);
                return response()->json(['success' => false, 'error_code' => 'invalid_otp', 'errors' => ['otp' => ['Invalid OTP']]], 422);
            }

            try { if (!empty($attemptKeyBase)) Cache::forget($attemptKeyBase); } catch (\Throwable $e) { \Log::warning('Failed to clear owner OTP attempts: ' . $e->getMessage()); }

            // clear cached entries but keep session hash until final
            try { if (!empty($normalizedEmail)) Cache::forget('signup_otp_owner_' . $normalizedEmail); if (!empty($otpToken)) Cache::forget('signup_otp_token_owner_' . $otpToken); if (!empty($clientIp)) Cache::forget('signup_otp_owner_ip_' . $clientIp); } catch (\Exception $e) { \Log::warning('Failed to cleanup owner OTP cache after verify: ' . $e->getMessage()); }

            try {
                $s = Session::get('owner_step2', []);
                if (is_array($s)) { $s['otp_verified'] = true; Session::put('owner_step2', $s); }
            } catch (\Exception $e) { \Log::warning('Failed to update owner session after OTP verify: ' . $e->getMessage()); }

            Session::put('signup_step', 4);

            if ($request->expectsJson()) return response()->json(['success' => true, 'message' => 'OTP verified successfully', 'step' => 4, 'next_step' => 'property_owner_step4'], 200);
            return response()->json(['success' => true, 'step' => 4]);
        };

        if ($lockName) {
            try {
                if (method_exists(Cache::store(), 'lock')) {
                    $lock = Cache::lock($lockName, 5);
                    return $lock->block(3, function () use ($verifyAndCleanup) { return $verifyAndCleanup(); });
                }
            } catch (\Throwable $e) { \Log::warning('Owner OTP verification lock failed or unsupported: ' . $e->getMessage()); }
        }

        return $verifyAndCleanup();
    }

    // Property Owner Step 4
    public function propertyOwnerStep4(accountRequest $request)
    {
        // Handle file uploads
        $validIdPath = null;
        $validIdBackPath = null;
        $policeClearancePath = null;

        if ($request->hasFile('valid_id_photo')) {
            $validIdPath = $request->file('valid_id_photo')->store('validID', 'public');
        }

        if ($request->hasFile('valid_id_back_photo')) {
            $validIdBackPath = $request->file('valid_id_back_photo')->store('validID', 'public');
        }

        if ($request->hasFile('police_clearance')) {
            $policeClearancePath = $request->file('police_clearance')->store('policeClearance', 'public');
        }

        Session::put('owner_step4', [
            'valid_id_id' => $request->valid_id_id,
            'valid_id_photo' => $validIdPath,
            'valid_id_back_photo' => $validIdBackPath,
            'police_clearance' => $policeClearancePath
        ]);

        Session::put('signup_step', 5);

        return response()->json(['success' => true, 'step' => 5]);
    }

    // Handle Property Owner Final Step
    public function propertyOwnerFinalStep(accountRequest $request)
    {
        // Get all session data (web flow)
        $step1 = Session::get('owner_step1');
        $step2 = Session::get('owner_step2');
        $step4 = Session::get('owner_step4');

        // Allow passing step data directly in request for stateless clients
        try {
            if (!$step1 && $request->input('step1_data')) { $decoded = json_decode($request->input('step1_data'), true); if (is_array($decoded)) $step1 = $decoded; }
            if (!$step2 && $request->input('step2_data')) { $decoded = json_decode($request->input('step2_data'), true); if (is_array($decoded)) $step2 = $decoded; }
            if (!$step4 && $request->input('step4_data')) { $decoded = json_decode($request->input('step4_data'), true); if (is_array($decoded)) $step4 = $decoded; }
        } catch (\Throwable $e) { \Log::warning('Failed to decode owner step data from request: ' . $e->getMessage()); }

        $step1 = is_array($step1) ? $step1 : (is_object($step1) ? (array)$step1 : []);
        $step2 = is_array($step2) ? $step2 : (is_object($step2) ? (array)$step2 : []);
        $step4 = is_array($step4) ? $step4 : (is_object($step4) ? (array)$step4 : []);

        // Provide defaults
        $step1['first_name'] = $step1['first_name'] ?? '';
        $step1['last_name'] = $step1['last_name'] ?? '';
        $step1['middle_name'] = $step1['middle_name'] ?? null;
        $step1['phone_number'] = $step1['phone_number'] ?? '';
        $step1['date_of_birth'] = $step1['date_of_birth'] ?? null;
        $step1['age'] = $step1['age'] ?? null;
        $step1['occupation_id'] = $step1['occupation_id'] ?? null;
        $step1['occupation_other'] = $step1['occupation_other'] ?? null;
        $step1['address'] = $step1['address'] ?? '';

        // Calculate age from date_of_birth if not provided (for mobile stateless flow)
        if (empty($step1['age']) && !empty($step1['date_of_birth'])) {
            try {
                $step1['age'] = $this->authService->calculateAge($step1['date_of_birth']);
                \Log::info('Calculated age from date_of_birth: ' . $step1['age']);
            } catch (\Throwable $e) {
                \Log::warning('Failed to calculate age from date_of_birth: ' . $e->getMessage());
                // Calculate manually as fallback
                try {
                    $birthDate = new \DateTime($step1['date_of_birth']);
                    $today = new \DateTime();
                    $step1['age'] = $today->diff($birthDate)->y;
                } catch (\Throwable $e2) {
                    \Log::warning('Manual age calculation also failed: ' . $e2->getMessage());
                }
            }
        }

        // Build address from components if address is empty but components exist
        if (empty($step1['address'])) {
            $addressParts = [];
            if (!empty($step1['owner_address_street'])) $addressParts[] = $step1['owner_address_street'];
            if (!empty($step1['owner_address_barangay'])) $addressParts[] = $step1['owner_address_barangay'];
            if (!empty($step1['owner_address_city'])) $addressParts[] = $step1['owner_address_city'];
            if (!empty($step1['owner_address_province'])) $addressParts[] = $step1['owner_address_province'];
            if (!empty($step1['owner_address_postal'])) $addressParts[] = $step1['owner_address_postal'];
            if (count($addressParts) > 0) {
                $step1['address'] = implode(', ', $addressParts);
            }
        }

        $step2['username'] = $step2['username'] ?? null;
        $step2['email'] = $step2['email'] ?? ($step2['owner_email'] ?? null);
        $step2['password'] = $step2['password'] ?? null;
        $step2['otp_hash'] = $step2['otp_hash'] ?? null;

        $step4['valid_id_id'] = $step4['valid_id_id'] ?? null;
        $step4['valid_id_photo'] = $step4['valid_id_photo'] ?? null;
        $step4['valid_id_back_photo'] = $step4['valid_id_back_photo'] ?? null;
        $step4['police_clearance'] = $step4['police_clearance'] ?? null;

        if (!$step1 || !$step2 || !$step4) {
            $missing = [];
            if (!$step1) $missing[] = 'Step 1 data';
            if (!$step2) $missing[] = 'Step 2 data';
            if (!$step4) $missing[] = 'Step 4 data';
            return response()->json(['success' => false, 'errors' => ['Session expired. Missing: ' . implode(', ', $missing) . '. Please start the registration process again.']], 400);
        }

        $profilePicPath = null;
        if ($request->hasFile('profile_pic')) {
            $profilePicPath = $request->file('profile_pic')->store('profiles', 'public');
        }

        // Handle file uploads from mobile clients (stateless flow)
        // Mobile clients send all images in the final step since they don't have persistent sessions
        if ($request->hasFile('valid_id_photo')) {
            try {
                $path = $request->file('valid_id_photo')->store('validID', 'public');
                $step4['valid_id_photo'] = $path;
                \Log::info('Stored valid_id_photo from final request: ' . $path);
            } catch (\Throwable $e) {
                \Log::warning('Failed to store valid_id_photo from final request (owner): ' . $e->getMessage());
            }
        }

        if ($request->hasFile('valid_id_back_photo')) {
            try {
                $path = $request->file('valid_id_back_photo')->store('validID', 'public');
                $step4['valid_id_back_photo'] = $path;
                \Log::info('Stored valid_id_back_photo from final request: ' . $path);
            } catch (\Throwable $e) {
                \Log::warning('Failed to store valid_id_back_photo from final request (owner): ' . $e->getMessage());
            }
        }

        if ($request->hasFile('police_clearance')) {
            try {
                $path = $request->file('police_clearance')->store('policeClearance', 'public');
                $step4['police_clearance'] = $path;
                \Log::info('Stored police_clearance from final request: ' . $path);
            } catch (\Throwable $e) {
                \Log::warning('Failed to store police_clearance from final request (owner): ' . $e->getMessage());
            }
        }

        // Validate valid_id_id
        $validIdCandidate = $step4['valid_id_id'] ?? null;
        if (!empty($validIdCandidate)) {
            $validIdExists = DB::table('valid_ids')->where('id', $validIdCandidate)->exists();
            if (!$validIdExists) {
                return response()->json(['success' => false, 'message' => 'Validation failed', 'errors' => ['valid_id_id' => ['Invalid valid ID selected']]], 422);
            }
        }

        // If OTP hash not present, try lookup from cache using token, email, or IP (mirror contractor logic)
        if (empty($step2['otp_hash']) || !isset($step2['otp_hash'])) {
            $foundHash = null;
            try {
                $normalizedEmail = isset($step2['email']) ? strtolower(trim($step2['email'])) : (isset($step2['owner_email']) ? strtolower(trim($step2['owner_email'])) : null);
                $otpTokenFromRequest = $request->input('otp_token') ?? $request->input('identifier') ?? null;
                $clientIp = $request->ip();

                // 1) Try token lookup
                if (!empty($otpTokenFromRequest)) {
                    $meta = Cache::get('signup_otp_token_owner_' . $otpTokenFromRequest);
                    if (!empty($meta) && isset($meta['hash'])) {
                        $foundHash = $meta['hash'];
                        \Log::info('Found owner OTP hash via otp_token lookup in final step');
                    }
                }

                // 2) Try email lookup
                if (!$foundHash && !empty($normalizedEmail)) {
                    $meta = Cache::get('signup_otp_owner_' . $normalizedEmail);
                    if (!empty($meta) && isset($meta['hash'])) {
                        $foundHash = $meta['hash'];
                        \Log::info('Found owner OTP hash via email lookup in final step for ' . $normalizedEmail);
                    }
                }

                // 3) IP fallback mapping
                if (!$foundHash && !empty($clientIp)) {
                    try {
                        $mapped = Cache::get('signup_otp_owner_ip_' . $clientIp);
                        if ($mapped) {
                            $meta = Cache::get('signup_otp_owner_' . $mapped);
                            if (!empty($meta) && isset($meta['hash'])) {
                                $foundHash = $meta['hash'];
                                \Log::info('Found owner OTP hash via IP fallback lookup for ' . $clientIp . ' -> ' . $mapped);
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::warning('Failed owner IP fallback OTP lookup: ' . $e->getMessage());
                    }
                }
            } catch (\Throwable $e) {
                \Log::warning('Owner OTP hash lookup failed in final step: ' . $e->getMessage());
            }

            if ($foundHash) {
                $step2['otp_hash'] = $foundHash;
            }
        }

        // Reuse existing user if present
        $existingUser = null;
        try {
            if (!empty($step2['username']) || !empty($step2['email'])) {
                $q = DB::table('users');
                if (!empty($step2['username'])) $q->where('username', $step2['username']);
                if (!empty($step2['email'])) $q->orWhere('email', $step2['email']);
                $existingUser = $q->first();
            }
        } catch (\Throwable $e) { \Log::warning('Existing owner user lookup failed: ' . $e->getMessage()); }

        if ($existingUser) {
            $userId = $existingUser->user_id ?? $existingUser->id ?? null;
            try { \Log::info('propertyOwnerFinalStep: reusing existing user id -> ' . var_export($userId, true)); } catch (\Throwable $e) {}

            try {
                $existingOwner = DB::table('property_owners')->where('user_id', $userId)->first();
                if ($existingOwner) {
                    return response()->json(['success' => true, 'message' => 'Account already exists', 'user_id' => $userId, 'owner_id' => $existingOwner->owner_id], 200);
                }
            } catch (\Throwable $e) { \Log::warning('Existing owner lookup failed: ' . $e->getMessage()); }
        } else {
            $userId = $this->accountClass->createUser([
                'profile_pic' => $profilePicPath,
                'username' => $step2['username'] ?? null,
                'email' => $step2['email'] ?? null,
                'password_hash' => isset($step2['password']) ? $this->authService->hashPassword($step2['password']) : null,
                'OTP_hash' => $step2['otp_hash'] ?? null,
                'user_type' => 'property_owner'
            ]);

            try { \Log::info('propertyOwnerFinalStep: created user id -> ' . var_export($userId, true)); } catch (\Throwable $e) {}
        }

        // Create property owner
        $ownerId = $this->accountClass->createPropertyOwner([
            'user_id' => $userId,
            'last_name' => $step1['last_name'],
            'middle_name' => $step1['middle_name'] ?? null,
            'first_name' => $step1['first_name'],
            'phone_number' => $step1['phone_number'],
            'valid_id_id' => $step4['valid_id_id'],
            'valid_id_photo' => $step4['valid_id_photo'],
            'valid_id_back_photo' => $step4['valid_id_back_photo'],
            'police_clearance' => $step4['police_clearance'],
            'date_of_birth' => $step1['date_of_birth'],
            'age' => $step1['age'],
            'occupation_id' => $step1['occupation_id'],
            'occupation_other' => $step1['occupation_other'] ?? null,
            'address' => $step1['address']
        ]);

        try { \Log::info('propertyOwnerFinalStep: created owner id -> ' . var_export($ownerId, true)); } catch (\Throwable $e) {}

        // Clear session
        Session::forget(['signup_user_type', 'signup_step', 'owner_step1', 'owner_step2', 'owner_step4']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Registration successful! You can now login.', 'user_id' => $userId, 'owner_id' => $ownerId, 'redirect_url' => '/accounts/login'], 201);
        }

        return response()->json(['success' => true, 'message' => 'Registration successful! Please wait for verification.', 'redirect' => '/accounts/login']);
    }

    // Temporary debug helper: lookup a user by email for local testing
    public function debugGetUserByEmail(Request $request)
    {
        $email = $request->query('email');
        if (empty($email)) {
            return response()->json(['success' => false, 'message' => 'email query parameter required'], 400);
        }

        try {
            $user = DB::table('users')->where('email', $email)->first();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 404);
            }
            return response()->json(['success' => true, 'user' => $user], 200);
        } catch (\Throwable $e) {
            \Log::error('debugGetUserByEmail error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error'], 500);
        }
    }


    //Handles Profile update
   public function updateProfile(Request $request)
{
    // 1. Check if user is authenticated
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'success' => false,
            'message' => 'User not authenticated. Please log in again.'
        ], 401);
    }

    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg|max:5120',
        'type' => 'required|in:profile,cover'
    ]);

    $type = $request->type;
    $folder = ($type === 'profile') ? 'profile_pics' : 'cover_photos';
    $column = ($type === 'profile') ? 'profile_pic' : 'cover_photo';

    // Delete old file if it exists
    if ($user->$column) {
        Storage::disk('public')->delete($user->$column);
    }

    $path = $request->file('image')->store($folder, 'public');
    $user->update([$column => $path]);

    return response()->json(['success' => true, 'path' => $path]);
}
    // Logout
    public function logout()
    {
        Session::flush();

        if (request()->expectsJson()) {

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ], 200);
        } else {

            return redirect('/accounts/login')->with('success', 'Logged out successfully');
        }
    }

    // ROLE SWITCHING METHODS

    // Show role switch form - Same logic as web version, but returns JSON for mobile
    public function showSwitchForm()
    {
        try {
            // Support both session and Sanctum token authentication
            $user = Session::get('user');

            // If no session user, try Sanctum
            if (!$user && request()->user()) {
                $user = request()->user();
            }

            // If still no user, try to get from token manually
            if (!$user && request()->bearerToken()) {
                try {
                    $token = \Laravel\Sanctum\PersonalAccessToken::findToken(request()->bearerToken());
                    if ($token && $token->tokenable) {
                        $user = $token->tokenable;
                    }
                } catch (\Exception $tokenError) {
                    // Silent fail, will return 401 below
                }
            }

            if (!$user) {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Authentication required',
                        'redirect_url' => '/accounts/login'
                    ], 401);
                } else {
                    return redirect('/accounts/login')->with('error', 'Please login first');
                }
            }

            // Get user_id and user_type safely
            $userId = is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? null);
            $userType = is_object($user) ? ($user->user_type ?? null) : ($user['user_type'] ?? null);

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user data'
                ], 400);
            }

            $currentRole = $userType;

            // Check if user already has both roles
            if ($userType === 'both') {
                if (request()->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You already have both roles',
                        'redirect_url' => '/dashboard'
                    ], 400);
                } else {
                    return redirect('/dashboard')->with('error', 'You already have both roles');
                }
            }

            // Get data para sa dropdowns (same as signup) - using EXACT same logic as showSignupForm
            $contractorTypes = $this->accountClass->getContractorTypes();
            $occupations = $this->accountClass->getOccupations();
            $validIds = $this->accountClass->getValidIds();
            $provinces = $this->psgcService->getProvinces();
            $picabCategories = $this->accountClass->getPicabCategories();

            // Get existing user data
            $existingData = $this->getExistingUserData($userId, $currentRole);

            if (request()->expectsJson()) {
                // Return JSON - Laravel will automatically convert collections to arrays (same as showSignupForm)
                return response()->json([
                    'success' => true,
                    'message' => 'Role switch form data',
                    'current_role' => $currentRole,
                    'existing_data' => $existingData,
                    'form_data' => [
                        'contractor_types' => $contractorTypes,
                        'occupations' => $occupations,
                        'valid_ids' => $validIds,
                        'picab_categories' => $picabCategories,
                        'provinces' => $provinces
                    ],
                    'is_switch_mode' => true
                ], 200);
            } else {
                return view('accounts.signup', compact(
                    'contractorTypes',
                    'occupations',
                    'validIds',
                    'picabCategories',
                    'provinces',
                    'currentRole',
                    'existingData'
                ))->with('isSwitchMode', true);
            }
        } catch (\Throwable $e) {
            // Always return a response, even on fatal errors
            try {
                Log::error('showSwitchForm error: ' . $e->getMessage() . ' | Line: ' . $e->getLine() . ' | File: ' . $e->getFile());
            } catch (\Exception $logError) {
                // If logging fails, at least return error
            }

            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    // Get existing user data
    private function getExistingUserData($userId, $currentRole)
    {
        $user = DB::table('users')->where('user_id', $userId)->first();
        $data = [
            'user' => $user ? (array) $user : null,
        ];

        if ($currentRole === 'contractor') {
            $contractor = DB::table('contractors')->where('user_id', $userId)->first();
            if ($contractor) {
                $contractorUser = DB::table('contractor_users')->where('user_id', $userId)->first();
                $data['contractor'] = (array) $contractor;
                $data['contractor_user'] = $contractorUser ? (array) $contractorUser : null;
            }
        } elseif ($currentRole === 'property_owner' || $currentRole === 'owner') {
            $owner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($owner) {
                $data['property_owner'] = (array) $owner;
            }
        }

        return $data;
    }

    // Switch to Contractor Step 1 - This handles company information (like regular contractorStep1)
    // For switch mode, we need to save company info to switch_contractor_step1
    public function switchContractorStep1(accountRequest $request)
    {
        // Support both session and Sanctum token authentication
        $user = Session::get('user') ?: $request->user();
        // Fallback: resolve user from Bearer token if middleware/user failed
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            } catch (\Throwable $e) {
                // ignore, handled below
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Authentication required. Please login again.']], 401);
        }

        // For switch mode, step1 can be either company info OR account info
        // Check what fields are being sent
        $step1Data = Session::get('switch_contractor_step1', []);

        // If request has company fields, it's the company info step
        if ($request->has('company_name')) {
            // This is company information step
            $companyData = $request->only([
                'company_name', 'company_phone', 'years_of_experience',
                'contractor_type_id', 'contractor_type_other_text', 'services_offered',
                'business_address_street', 'business_address_barangay', 'business_address_city',
                'business_address_province', 'business_address_postal', 'company_website', 'company_social_media'
            ]);

            // Build business address
            $businessAddress = trim(
                ($companyData['business_address_street'] ?? '') . ', ' .
                ($companyData['business_address_barangay'] ?? '') . ', ' .
                ($companyData['business_address_city'] ?? '') . ', ' .
                ($companyData['business_address_province'] ?? '') . ' ' .
                ($companyData['business_address_postal'] ?? '')
            );

            $step1Data = array_merge($step1Data, [
                'company_name' => $companyData['company_name'],
                'company_phone' => $companyData['company_phone'],
                'years_of_experience' => $companyData['years_of_experience'],
                'type_id' => $companyData['contractor_type_id'],
                'contractor_type_other' => $companyData['contractor_type_other_text'] ?? null,
                'services_offered' => $companyData['services_offered'],
                'business_address' => $businessAddress,
                'company_website' => $companyData['company_website'] ?? null,
                'company_social_media' => $companyData['company_social_media'] ?? null
            ]);
        } else {
            // This is account information step (step 2)
            $step1Data = array_merge($step1Data, $request->only(['first_name','middle_name','last_name','username','company_email']));
        }

        Session::put('switch_contractor_step1', $step1Data);

        return response()->json([
            'success' => true,
            'message' => 'Information saved',
            'step' => 2,
            'next_step' => 'documents'
        ]);
    }

    // Switch to Contractor Step 2
    public function switchContractorStep2(accountRequest $request)
    {
        // Support both session and Sanctum token authentication
        $user = Session::get('user') ?: $request->user();
        // Fallback: resolve user from Bearer token if middleware/user failed
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            } catch (\Throwable $e) {
                // ignore, handled below
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Authentication required. Please login again.']], 401);
        }

        $validated = $request->validated();

        if ($request->hasFile('dti_sec_registration_photo')) {
            $file = $request->file('dti_sec_registration_photo');
            $filename = time() . '_dti_sec_' . $file->getClientOriginalName();
            $path = $file->storeAs('contractor_documents', $filename, 'public');
            $validated['dti_sec_registration_photo'] = $path;
        }

        Session::put('switch_contractor_step2', $validated);
        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully',
            'saved' => [
                'picab_number' => $validated['picab_number'] ?? null,
                'picab_category' => $validated['picab_category'] ?? null,
                'picab_expiration_date' => $validated['picab_expiration_date'] ?? null,
                'business_permit_number' => $validated['business_permit_number'] ?? null,
                'business_permit_city' => $validated['business_permit_city'] ?? null,
                'business_permit_expiration' => $validated['business_permit_expiration'] ?? null,
                'tin_business_reg_number' => $validated['tin_business_reg_number'] ?? null,
                'dti_sec_registration_photo' => $validated['dti_sec_registration_photo'] ?? null,
            ]
        ]);
    }

    // Switch to Contractor Final
    public function switchContractorFinal(accountRequest $request)
    {
        // Support both session and Sanctum token authentication
        $user = Session::get('user') ?: $request->user();
        // Fallback: resolve user from Bearer token if middleware/user failed
        if (!$user && $request->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            } catch (\Throwable $e) {
                // ignore, handled below
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Authentication required. Please login again.']], 401);
        }

        // For mobile API, allow passing step data directly in request
        // For web, use session data
        $step1 = $request->input('step1_data') ?: Session::get('switch_contractor_step1');
        $step2 = $request->input('step2_data') ?: Session::get('switch_contractor_step2');

        // Normalize step arrays
        if (!is_array($step1)) { $step1 = is_object($step1) ? (array)$step1 : []; }
        if (!is_array($step2)) { $step2 = is_object($step2) ? (array)$step2 : []; }

        // Ensure DTI/SEC path is available for strict NOT NULL column
        if (empty($step2['dti_sec_registration_photo'])) {
            // Accept file upload in final as a fallback
            if ($request->hasFile('dti_sec_registration_photo')) {
                try {
                    $file = $request->file('dti_sec_registration_photo');
                    $filename = time() . '_dti_sec_' . $file->getClientOriginalName();
                    $path = $file->storeAs('contractor_documents', $filename, 'public');
                    $step2['dti_sec_registration_photo'] = $path;
                } catch (\Throwable $e) {
                    // ignore; handled by validation below
                }
            }
            // Or accept a plain string path provided by the client
            if (empty($step2['dti_sec_registration_photo'])) {
                $inlinePath = $request->input('dti_sec_registration_photo')
                    ?: $request->input('dti_sec_registration_photo_path');
                if (!empty($inlinePath) && is_string($inlinePath)) {
                    $step2['dti_sec_registration_photo'] = $inlinePath;
                }
            }
        }

        // If still missing, return a clear validation error instead of DB exception
        if (empty($step2['dti_sec_registration_photo'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => ['dti_sec_registration_photo' => ['DTI/SEC registration photo is required']]
            ], 422);
        }

        if (!$step1 || !$step2) {
            return response()->json(['success' => false, 'errors' => ['Previous steps not completed. Please complete all steps.']], 400);
        }

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_profile_' . $file->getClientOriginalName();
                $profilePicPath = $file->storeAs('profile_pictures', $filename, 'public');
                $userId = is_object($user) ? ($user->user_id ?? $user->id) : ($user['user_id'] ?? null);
                if ($userId) {
                    DB::table('users')->where('user_id', $userId)->update(['profile_pic' => $profilePicPath]);
                }
            }

            $businessAddress = $step1['business_address'] ?? '';

            $userId = is_object($user) ? ($user->user_id ?? $user->id) : ($user['user_id'] ?? null);
            $userEmail = is_object($user) ? ($user->email ?? null) : ($user['email'] ?? null);

            if (!$userId) {
                DB::rollBack();
                return response()->json(['success' => false, 'errors' => ['Invalid user data']], 400);
            }

            $ownerData = DB::table('property_owners')->where('user_id', $userId)->first();

            $contractorId = DB::table('contractors')->insertGetId([
                'user_id' => $userId,
                'company_name' => $step1['company_name'] ?? '',
                'years_of_experience' => $step1['years_of_experience'] ?? 0,
                'type_id' => $step1['type_id'] ?? null,
                'contractor_type_other' => $step1['contractor_type_other'] ?? null,
                'services_offered' => $step1['services_offered'] ?? '',
                'business_address' => $businessAddress,
                'company_email' => $userEmail,
                'company_phone' => $step1['company_phone'] ?? '',
                'company_website' => $step1['company_website'] ?? null,
                'company_social_media' => $step1['company_social_media'] ?? null,
                'picab_number' => $step2['picab_number'] ?? '',
                'picab_category' => $step2['picab_category'] ?? '',
                'picab_expiration_date' => $step2['picab_expiration_date'] ?? null,
                'business_permit_number' => $step2['business_permit_number'] ?? '',
                'business_permit_city' => $step2['business_permit_city'] ?? '',
                'business_permit_expiration' => $step2['business_permit_expiration'] ?? null,
                'tin_business_reg_number' => $step2['tin_business_reg_number'] ?? '',
                'dti_sec_registration_photo' => $step2['dti_sec_registration_photo'] ?? null,
                'verification_status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            if ($ownerData) {
                DB::table('contractor_users')->insert([
                    'contractor_id' => $contractorId,
                    'user_id' => $userId,
                    'authorized_rep_lname' => $ownerData->last_name ?? '',
                    'authorized_rep_mname' => $ownerData->middle_name ?? null,
                    'authorized_rep_fname' => $ownerData->first_name ?? '',
                    'phone_number' => $ownerData->phone_number ?? '',
                    'role' => 'owner',
                    'is_active' => 0,
                    'created_at' => now(),
                ]);
            }

            DB::table('users')->where('user_id', $userId)->update([
                'user_type' => 'both',
                'updated_at' => now(),
            ]);

            Session::forget(['switch_contractor_step1', 'switch_contractor_step2']);
            $updatedUser = DB::table('users')->where('user_id', $userId)->first();
            if (Session::has('user')) {
                Session::put('user', $updatedUser);
                Session::put('userType', 'both');
                Session::put('current_role', 'contractor');
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Role switch successful! You now have both roles.',
                'user_type' => 'both',
                'current_role' => 'contractor',
                'redirect_url' => '/dashboard'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]], 500);
        }
    }

    // DB calls are just laravel query builder

    // Switch to Owner Step 1 (Account Setup)
    public function switchOwnerStep1(accountRequest $request)
    {
        // Support session auth, Sanctum middleware, and explicit Bearer fallback
        $user = Session::get('user') ?: $request->user();
        if (!$user) {
            try {
                $bearer = $request->bearerToken();
                if ($bearer) {
                    $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                    if ($pat) {
                        $userModel = \App\Models\User::find($pat->tokenable_id);
                        if ($userModel) { $user = $userModel; }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('switchOwnerStep1 bearer fallback failed: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Authentication required. Please login again.']], 401);
        }

        $validated = $request->validated();

        // Store account info for owner switch
        $step1Data = [
            'username' => $validated['username'] ?? null,
            'email' => $validated['email'] ?? null
        ];

        Session::put('switch_owner_step1', $step1Data);

        return response()->json([
            'success' => true,
            'message' => 'Account information saved',
            'step' => 2,
            'next_step' => 'documents'
        ]);
    }

    // Switch to Owner Step 2
    public function switchOwnerStep2(accountRequest $request)
    {
        // Support session auth, Sanctum middleware, and explicit Bearer fallback
        $user = Session::get('user') ?: $request->user();
        if (!$user) {
            try {
                $bearer = $request->bearerToken();
                if ($bearer) {
                    $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                    if ($pat) {
                        $userModel = \App\Models\User::find($pat->tokenable_id);
                        if ($userModel) { $user = $userModel; }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('switchOwnerStep2 bearer fallback failed: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Authentication required. Please login again.']], 401);
        }

        $validated = $request->validated();

        if ($request->hasFile('valid_id_photo')) {
            $file = $request->file('valid_id_photo');
            $filename = time() . '_valid_id_front_' . $file->getClientOriginalName();
            $path = $file->storeAs('owner_documents', $filename, 'public');
            $validated['valid_id_photo'] = $path;
        }

        if ($request->hasFile('valid_id_back_photo')) {
            $file = $request->file('valid_id_back_photo');
            $filename = time() . '_valid_id_back_' . $file->getClientOriginalName();
            $path = $file->storeAs('owner_documents', $filename, 'public');
            $validated['valid_id_back_photo'] = $path;
        }

        if ($request->hasFile('police_clearance')) {
            $file = $request->file('police_clearance');
            $filename = time() . '_police_' . $file->getClientOriginalName();
            $path = $file->storeAs('owner_documents', $filename, 'public');
            $validated['police_clearance'] = $path;
        }

        Session::put('switch_owner_step2', $validated);

        return response()->json([
            'success' => true,
            'message' => 'Documents uploaded successfully',
            'step' => 3,
            'next_step' => 'final',
            'saved' => [
                'valid_id_id' => $validated['valid_id_id'] ?? null,
                'valid_id_photo' => $validated['valid_id_photo'] ?? null,
                'valid_id_back_photo' => $validated['valid_id_back_photo'] ?? null,
                'police_clearance' => $validated['police_clearance'] ?? null,
            ]
        ]);
    }

    // Switch to Owner Final
    public function switchOwnerFinal(accountRequest $request)
    {
        // Support session auth, Sanctum middleware, and explicit Bearer fallback
        $user = Session::get('user') ?: $request->user();
        if (!$user) {
            try {
                $bearer = $request->bearerToken();
                if ($bearer) {
                    $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($bearer);
                    if ($pat) {
                        $userModel = \App\Models\User::find($pat->tokenable_id);
                        if ($userModel) { $user = $userModel; }
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('switchOwnerFinal bearer fallback failed: ' . $e->getMessage());
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'errors' => ['Authentication required. Please login again.']], 401);
        }

        $validated = $request->validated();

        // For mobile API, allow passing step data directly in request; for web, use session data
        $ownerStep1Raw = $request->input('owner_step1_data') ?: Session::get('owner_step1');
        if (is_string($ownerStep1Raw)) {
            $decoded = json_decode($ownerStep1Raw, true);
            if (json_last_error() === JSON_ERROR_NONE) { $ownerStep1Raw = $decoded; }
        }
        $ownerStep1 = is_array($ownerStep1Raw) ? $ownerStep1Raw : [];

        $switchStep1Raw = $request->input('switch_step1_data') ?: Session::get('switch_owner_step1');
        if (is_string($switchStep1Raw)) {
            $decoded = json_decode($switchStep1Raw, true);
            if (json_last_error() === JSON_ERROR_NONE) { $switchStep1Raw = $decoded; }
        }
        $switchStep1 = is_array($switchStep1Raw) ? $switchStep1Raw : [];

        $switchStep2Raw = $request->input('switch_step2_data') ?: Session::get('switch_owner_step2');
        if (is_string($switchStep2Raw)) {
            $decoded = json_decode($switchStep2Raw, true);
            if (json_last_error() === JSON_ERROR_NONE) { $switchStep2Raw = $decoded; }
        }
        $switchStep2 = is_array($switchStep2Raw) ? $switchStep2Raw : [];

        // If client sent nested 'saved' doc map from Step 2, unwrap it
        $switchStep2Docs = (isset($switchStep2['saved']) && is_array($switchStep2['saved'])) ? $switchStep2['saved'] : $switchStep2;

        if (empty($ownerStep1)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => ['Personal information missing. Please start again.']], 400);
            }
        }

        // Normalize document fields from either nested step2 or flat top-level payload keys
        $docs = [
            'valid_id_id' => $switchStep2Docs['valid_id_id'] ?? $request->input('valid_id_id'),
            'valid_id_photo' => $switchStep2Docs['valid_id_photo'] ?? $request->input('valid_id_photo'),
            'valid_id_back_photo' => $switchStep2Docs['valid_id_back_photo'] ?? $request->input('valid_id_back_photo'),
            'police_clearance' => $switchStep2Docs['police_clearance'] ?? $request->input('police_clearance'),
        ];

        Log::info('switchOwnerFinal payload normalization', [
            'has_owner_step1' => !empty($ownerStep1),
            'has_switch_step2' => !empty($switchStep2),
            'doc_keys' => array_keys(array_filter($docs))
        ]);

        // Validate valid_id_id to avoid FK constraint failures when provided
        $switchValidId = $docs['valid_id_id'] ?? null;
        if (!empty($switchValidId)) {
            $exists = DB::table('valid_ids')->where('id', $switchValidId)->exists();
            if (!$exists) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => ['valid_id_id' => ['Invalid valid ID selected for owner switch']]
                    ], 422);
                } else {
                    return response()->json(['success' => false, 'errors' => ['valid_id_id' => ['Invalid valid ID selected for owner switch']]], 422);
                }
            }
        }

        // If no documents resolved, return a 422 rather than 500
        if (empty($docs['valid_id_photo']) && empty($docs['valid_id_back_photo']) && empty($docs['police_clearance'])) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => ['documents' => ['Owner documents missing. Please upload in step 2 or include saved paths in final.']]
            ], 422);
        }

        try {
            DB::beginTransaction();

            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');
                $filename = time() . '_profile_' . $file->getClientOriginalName();
                $profilePicPath = $file->storeAs('profile_pictures', $filename, 'public');
                DB::table('users')->where('user_id', $user->user_id)->update(['profile_pic' => $profilePicPath]);
            }

            // Get existing contractor user data (optional fallback)
            $contractorUser = DB::table('contractor_users')->where('user_id', $user->user_id)->first();

            // Create property owner record using validated top-level fields with fallbacks
            DB::table('property_owners')->insert([
                'user_id' => $user->user_id,
                'last_name' => $validated['last_name'] ?? ($contractorUser->authorized_rep_lname ?? null),
                'middle_name' => $validated['middle_name'] ?? ($contractorUser->authorized_rep_mname ?? null),
                'first_name' => $validated['first_name'] ?? ($contractorUser->authorized_rep_fname ?? null),
                'phone_number' => $validated['phone_number'] ?? ($contractorUser->phone_number ?? null),
                'valid_id_id' => $docs['valid_id_id'] ?? null,
                'valid_id_back_photo' => $docs['valid_id_back_photo'] ?? null,
                'valid_id_photo' => $docs['valid_id_photo'] ?? null,
                'police_clearance' => $docs['police_clearance'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? ($ownerStep1['date_of_birth'] ?? null),
                'age' => $ownerStep1['age'] ?? null,
                'occupation_id' => $validated['occupation_id'] ?? ($ownerStep1['occupation_id'] ?? null),
                'occupation_other' => $validated['occupation_other'] ?? ($ownerStep1['occupation_other'] ?? null),
                'address' => $validated['address'] ?? ($ownerStep1['address'] ?? null),
                'verification_status' => 'pending',
                'verification_date' => null,
                'created_at' => now(),
            ]);

            // Update user type to 'both'
            DB::table('users')->where('user_id', $user->user_id)->update([
                'user_type' => 'both',
                'updated_at' => now(),
            ]);

            // Update session with new user data
            $updatedUser = DB::table('users')->where('user_id', $user->user_id)->first();
            Session::put('user', $updatedUser);
            Session::put('userType', 'both');
            Session::put('current_role', 'owner'); // Default to owner since they just added owner role

            // Clear switch session data
            Session::forget(['switch_owner_step1', 'switch_owner_step2', 'owner_step1']);

            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Role switch successful! You now have both roles.',
                    'user_type' => 'both',
                    'current_role' => 'owner',
                    'redirect_url' => '/dashboard'
                ], 201);
            } else {
                return response()->json(['success' => true, 'message' => 'Role switch successful! You now have both roles.', 'redirect' => '/dashboard']);
            }

        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred during role switch',
                    'errors' => [$e->getMessage()]
                ], 500);
            } else {
                return response()->json(['success' => false, 'errors' => ['An error occurred: ' . $e->getMessage()]], 500);
            }
        }
    }

    // PSGC API Endpoints

    public function getProvinces()
    {
        $provinces = $this->psgcService->getProvinces();
        return response()->json($provinces);
    }

    public function getCitiesByProvince($provinceCode)
    {
        $cities = $this->psgcService->getCitiesByProvince($provinceCode);
        return response()->json($cities);
    }

    public function getBarangaysByCity($cityCode)
    {
        $barangays = $this->psgcService->getBarangaysByCity($cityCode);
        return response()->json($barangays);
    }

    // API Methods for Mobile Authentication

    // API Login
    public function apiLogin(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string', // Accept username (can be username or email)
                'password' => 'required|string'
            ]);

            $result = $this->authService->login($request->username, $request->password);

            if ($result['success']) {
                $userData = $result['user'];

                // Convert stdClass to Eloquent User model for Sanctum token creation
                $eloquentUser = \App\Models\User::find($userData->user_id ?? $userData->id ?? null);

                if (!$eloquentUser) {
                    return response()->json([
                        'success' => false,
                        'message' => 'User model not found'
                    ], 500);
                }

                // Create Sanctum token for mobile app
                $token = $eloquentUser->createToken('mobile-app')->plainTextToken;

                // Build response data
                $responseData = [
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $userData,
                    'userType' => $result['userType'],
                    'token' => $token,
                    'must_change_password' => (bool) ($eloquentUser->must_change_password ?? false),
                ];

                /**
                 * DASHBOARD ROUTING RULES:
                 * - user_type === 'staff' → contractor dashboard (staff are contractor team members)
                 * - user_type === 'contractor' → contractor dashboard
                 * - determinedRole === 'contractor' → contractor dashboard
                 * - user_type === 'property_owner' → property_owner dashboard
                 * - user_type === 'both' → property_owner dashboard (default)
                 */
                $isContractorUser = $userData->user_type === 'staff'
                    || $userData->user_type === 'contractor'
                    || ($result['determinedRole'] ?? null) === 'contractor';

                \Log::info('Login dashboard routing', [
                    'user_id' => $userData->user_id ?? $userData->id,
                    'username' => $userData->username,
                    'user_type' => $userData->user_type,
                    'determinedRole_from_authService' => $result['determinedRole'] ?? null,
                    'isContractorUser' => $isContractorUser,
                ]);

                // For contractor users (including staff), include member authorization context
                if ($isContractorUser) {
                    $contractorAuthService = app(\App\Services\ContractorAuthorizationService::class);
                    $userId = $userData->user_id ?? $userData->id;
                    
                    $memberContext = $contractorAuthService->getAuthorizationContext($userId);
                    
                    // Always set determinedRole for contractor/staff users
                    $responseData['determinedRole'] = 'contractor';
                    
                    if ($memberContext) {
                        $responseData['contractor_member'] = $memberContext;
                        
                        // Block login if member is inactive
                        if (!$memberContext['is_active']) {
                            // Delete the just-created token since they can't use it
                            $eloquentUser->tokens()->where('name', 'mobile-app')->delete();
                            
                            return response()->json([
                                'success' => false,
                                'message' => 'Your contractor member account is inactive. Please contact the contractor owner.',
                                'error_code' => 'MEMBER_INACTIVE'
                            ], 403);
                        }
                    } else {
                        // Staff user without contractor_users record - log warning but allow login
                        \Illuminate\Support\Facades\Log::warning('Staff user without contractor_users record', [
                            'user_id' => $userId,
                            'username' => $userData->username ?? null
                        ]);
                    }
                }

                return response()->json($responseData, 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 401);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('apiLogin exception: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login'
            ], 500);
        }
    }

    // API Register
    public function apiRegister(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6'
            ]);

            // Generate a simple OTP hash for now
            $otpHash = bcrypt('123456'); // You can implement proper OTP generation later

            // Create user using your database structure
            $user = \App\Models\User::create([
                'username' => $request->name,
                'email' => $request->email,
                'password_hash' => bcrypt($request->password),
                'OTP_hash' => $otpHash,
                'user_type' => 'property_owner' // Default to property_owner for mobile registration
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => [
                    'id' => $user->user_id,
                    'name' => $user->username,
                    'email' => $user->email,
                    'user_type' => $user->user_type
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            // Log the actual error for debugging
            \Illuminate\Support\Facades\Log::error('Registration error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during registration: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }

    // API Test Connection
    public function apiTest()
    {
        return response()->json([
            'success' => true,
            'message' => 'API connection successful',
            'timestamp' => now(),
            'server' => 'Laravel ' . app()->version()
        ], 200);
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

            // Check user exists and must_change_password is true
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found',
                ], 404);
            }

            if (!$user->must_change_password) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password change is not required for this account',
                ], 400);
            }

            // Update password and clear the flag
            $user->password_hash = bcrypt($request->new_password);
            $user->must_change_password = false;
            $user->updated_at = now();
            $user->save();

            \Illuminate\Support\Facades\Log::info('Force password change completed', [
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
            \Illuminate\Support\Facades\Log::error('Force password change error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while changing password',
            ], 500);
        }
    }
}
