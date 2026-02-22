<?php

namespace App\Http\Controllers\subs;

    use App\Http\Controllers\Controller;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Session;

    class payMongoController extends Controller
    {
        /**
         * Create Subscription Checkout Session
         */
        public function createSubscriptionCheckout(Request $request)
        {
            Log::info('createSubscriptionCheckout called');

            // Try to identify user via Sanctum first
            $user = $request->user();

            // Fallback: web session user
            if (!$user && Session::has('user')) {
                $user = Session::get('user');
                if ($user) {
                    Log::info('createSubscriptionCheckout: Using session user fallback: ' . ($user->user_id ?? 'unknown'));
                }
            }

            // Fallback: allow mobile clients to pass X-User-Id header or user_id in body
            if (!$user) {
                $fallbackUserId = $request->header('X-User-Id') ?? $request->input('user_id');
                if ($fallbackUserId) {
                    $user = DB::table('users')->where('user_id', $fallbackUserId)->first();
                    if ($user) {
                        Log::info('createSubscriptionCheckout: Using fallback user from header/body: ' . $fallbackUserId);
                    }
                }
            }

            if (!$user) {
                Log::warning('createSubscriptionCheckout: Unauthorized (User not found)');
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            // Get Contractor ID
            $contractor = DB::table('contractors')->where('user_id', is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? $user['id'] ?? null))->first();
            if (!$contractor) {
                Log::error('createSubscriptionCheckout: Contractor profile not found for user ' . $user->user_id);
                return response()->json(['success' => false, 'message' => 'Contractor profile not found'], 404);
            }

            $planTier = $request->input('plan_tier');
            Log::info('createSubscriptionCheckout: Tier ' . $planTier);
            $plans = [
                'gold' => ['amount' => 199900, 'name' => 'Gold Tier Subscription'],
                'silver' => ['amount' => 149900, 'name' => 'Silver Tier Subscription'],
                'bronze' => ['amount' => 99900, 'name' => 'Bronze Tier Subscription']
            ];

            if (!array_key_exists($planTier, $plans)) {
                return response()->json(['success' => false, 'message' => 'Invalid plan tier'], 400);
            }

            $plan = $plans[$planTier];
            $secretKey = env('PAYMONGO_TEST_SECRET_KEY', env('PAYMONGO_SECRET_KEY'));

            // Prepare billing info from user record (supports both Eloquent user and DB row)
            $username = is_object($user) ? ($user->username ?? $user->name ?? '') : ($user['username'] ?? $user['name'] ?? '');
            $email = is_object($user) ? ($user->email ?? '') : ($user['email'] ?? '');

            // Ensure secret key is present
            if (!$secretKey) {
                Log::error('PayMongo Secret Key missing in .env');
                return response()->json(['success' => false, 'message' => 'Server configuration error'], 500);
            }

            try {
                // Allow mobile clients to provide a deep-link return URL (e.g. exp://... or myapp://...)
                $returnUrl = $request->input('return_url');

                $successUrl = $returnUrl ? ($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=success') : route('payment.callback', ['type' => 'subscription', 'plan' => $planTier]);
                $cancelUrl = $returnUrl ? ($returnUrl . (strpos($returnUrl, '?') !== false ? '&' : '?') . 'status=cancel') : url(($user->user_type === 'property_owner' ? '/owner/homepage' : '/contractor/homepage') . '?subscription=cancelled');

                $response = Http::withBasicAuth($secretKey, '')
                    ->post('https://api.paymongo.com/v1/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'billing' => [
                                'name' => $username,
                                'email' => $email,
                            ],
                            'line_items' => [[
                                    'currency' => 'PHP',
                                    'amount' => $plan['amount'],
                                    'description' => $plan['name'],
                                    'name' => $plan['name'],
                                    'quantity' => 1
                                ]],
                            'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
                            'success_url' => $successUrl,
                            'cancel_url' => $cancelUrl,
                            'metadata' => [
                                'user_id' => is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? $user['id'] ?? null),
                                'plan_tier' => $planTier,
                                'type' => 'subscription'
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $checkoutData = $response->json();
                    $checkoutSessionId = $checkoutData['data']['id'];
                    $checkoutUrl = $checkoutData['data']['attributes']['checkout_url'];

                    // Insert into platform_payments
                    try {
                        DB::table('platform_payments')->insert([
                            'contractor_id' => $contractor->contractor_id,
                            'payment_for' => 'subscription',
                            'subscription_tier' => $planTier,
                            'amount' => $plan['amount'] / 100,
                            'transaction_number' => $checkoutSessionId,
                            'is_approved' => config('app.env') === 'local' ? 1 : 0,
                            //'is_approved' => 0,
                            'transaction_date' => now(),
                            'expiration_date' => now()->addMonth(),
                            'payment_type' => 'PayMongo'
                        ]);
                    }
                    catch (\Exception $dbEx) {
                        Log::error('Database Insert Failed (Subscription): ' . $dbEx->getMessage());
                    // Proceed without failing request
                    }

                    return response()->json([
                        'success' => true,
                        'checkout_url' => $checkoutUrl
                    ]);
                }
                else {
                    Log::error('PayMongo Error: ' . $response->body());
                    return response()->json(['success' => false, 'message' => 'Unable to create checkout session.'], 500);
                }
            }
            catch (\Exception $e) {
                Log::error('PayMongo Exception: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Server Error'], 500);
            }
        }

        /**
         * Create Boost Checkout Session
         */
    public function createBoostCheckout(Request $request)
    {

        // Try to get authenticated user via Sanctum first
        $user = $request->user();

        // Fallback: web session user
        if (!$user && Session::has('user')) {
            $user = Session::get('user');
            if ($user) {
                Log::info('createBoostCheckout: Using session user fallback: ' . ($user->user_id ?? 'unknown'));
            }
        }

        // Fallback: allow mobile clients to pass X-User-Id header or user_id in body
        if (!$user) {
            $fallbackUserId = $request->header('X-User-Id') ?? $request->input('user_id');
            if ($fallbackUserId) {
                $user = DB::table('users')->where('user_id', $fallbackUserId)->first();
                if ($user) {
                    Log::info('createBoostCheckout: Using fallback user from header/body: ' . $fallbackUserId);
                }
            }
        }

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Normalize user id for lookups and metadata
        $userId = is_object($user) ? ($user->user_id ?? $user->id ?? null) : ($user['user_id'] ?? $user['id'] ?? null);

        // Prepare billing info from user record (supports both Eloquent user and DB row)
        $username = is_object($user) ? ($user->username ?? $user->name ?? '') : ($user['username'] ?? $user['name'] ?? '');
        $email = is_object($user) ? ($user->email ?? '') : ($user['email'] ?? '');

        // Get Property Owner record
        $owner = DB::table('property_owners')->where('user_id', $userId)->first();

        if (!$owner) {
            Log::error('createBoostCheckout: Owner profile not found for user ' . $userId);
            return response()->json(['success' => false, 'message' => 'Owner profile not found'], 404);
        }

            $projectId = $request->input('project_id');
            if (!$projectId) {
                return response()->json(['success' => false, 'message' => 'Project ID required'], 400);
            }

            // Verify project exists and belongs to this owner (ownership validation)
            $project = DB::table('projects')->where('project_id', $projectId)->first();
            if (!$project) {
                Log::warning('createBoostCheckout: Project not found: ' . $projectId);
                return response()->json(['success' => false, 'message' => 'Project not found'], 404);
            }

            // Expecting projects.owner_id to reference property_owners.owner_id
            if (isset($project->owner_id) && $project->owner_id != $owner->owner_id) {
                Log::warning('createBoostCheckout: Ownership mismatch. User owner_id: ' . $owner->owner_id . ' project owner_id: ' . ($project->owner_id ?? 'null'));
                return response()->json(['success' => false, 'message' => 'Forbidden: you do not own this project'], 403);
            }

            $secretKey = env('PAYMONGO_TEST_SECRET_KEY', env('PAYMONGO_SECRET_KEY'));

            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->post('https://api.paymongo.com/v1/checkout_sessions', [
                    'data' => [
                        'attributes' => [
                            'billing' => [
                                'name' => $username,
                                'email' => $email,
                            ],
                            'line_items' => [[
                                    'currency' => 'PHP',
                                    'amount' => 4900, // 49.00 PHP
                                    'description' => 'Project Boost - 7 Days',
                                    'name' => 'Project Boost',
                                    'quantity' => 1
                                ]],
                            'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
                            // Allow mobile clients to provide a deep-link return URL (e.g. exp://... or myapp://...)
                            // If provided, craft distinct success/cancel URLs by appending a status param
                            // so the app can distinguish actual success vs user-cancel/back actions.
                            'success_url' => (function() use ($request, $projectId) {
                                $base = $request->input('return_url');
                                if ($base) {
                                    return $base . (strpos($base, '?') !== false ? '&' : '?') . 'status=success';
                                }
                                return route('payment.callback', ['type' => 'boost', 'project_id' => $projectId]);
                            })(),
                            'cancel_url' => (function() use ($request) {
                                $base = $request->input('return_url');
                                if ($base) {
                                    return $base . (strpos($base, '?') !== false ? '&' : '?') . 'status=cancel';
                                }
                                return url('/owner/homepage?boost=cancelled');
                            })(),
                            'metadata' => [
                                'user_id' => $userId,
                                'project_id' => $projectId,
                                'type' => 'boost'
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $checkoutData = $response->json();
                    $checkoutSessionId = $checkoutData['data']['id'];
                    $checkoutUrl = $checkoutData['data']['attributes']['checkout_url'];

                    // Insert into platform_payments
                    try {
                        // Always mark new boost checkouts as not approved initially.
                        // Previously this used env-local auto-approve which caused the app
                        // to show boosts as active immediately during local testing.
                        DB::table('platform_payments')->insert([
                            'owner_id' => $owner->owner_id,
                            'project_id' => $projectId,
                            'payment_for' => 'boosted_post',
                            'amount' => 49.00,
                            'transaction_number' => $checkoutSessionId,
                            'is_approved' => 0,
                            'transaction_date' => now(),
                            'expiration_date' => now()->addDays(7),
                            'payment_type' => 'PayMongo'
                        ]);
                    }
                    catch (\Exception $dbEx) {
                        Log::error('Database Insert Failed (Boost): ' . $dbEx->getMessage());
                    // Proceed without failing request
                    }

                    return response()->json([
                        'success' => true,
                        'checkout_url' => $checkoutUrl
                    ]);
                }
                else {
                    Log::error('PayMongo Boost Error: ' . $response->body());
                    return response()->json(['success' => false, 'message' => 'Unable to create boost session.'], 500);
                }
            }
            catch (\Exception $e) {
                Log::error('PayMongo Boost Exception: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Server Error'], 500);
            }
        }

        /**
         * Get Projects Eligible for Boosting
         */

        /**
         * Handle Payment Success Callback (Localhost Friendly)
         */
        public function handlePaymentSuccess(Request $request)
        {
            if (!Session::has('user')) {
                return redirect('/login');
            }
            $user = Session::get('user');

            // Find latest pending payment
            // Find latest pending payment
            // We check for pending PayMongo payments for this user (checking both contractor and owner tables)
            $userId = $user->user_id;

            $payment = DB::table('platform_payments')
                ->where('is_approved', 0)
                ->where(function ($q) use ($userId) {
                $q->whereIn('contractor_id', function ($sq) use ($userId) {
                        $sq->select('contractor_id')->from('contractors')->where('user_id', $userId);
                    }
                    )
                        ->orWhereIn('owner_id', function ($sq) use ($userId) {
                    $sq->select('owner_id')->from('property_owners')->where('user_id', $userId);
                }
                );
            })
                ->orderByDesc('platform_payment_id')
                ->first();

            if (!$payment) {
                // No pending payment found
                return redirect(($user->user_type === 'property_owner' ? '/owner/homepage' : '/contractor/homepage') . '?error=no_pending');
            }

            // Verify with PayMongo
            $secretKey = env('PAYMONGO_TEST_SECRET_KEY', env('PAYMONGO_SECRET_KEY'));
            if (!$secretKey)
                return redirect('/?error=config');

            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->get('https://api.paymongo.com/v1/checkout_sessions/' . $payment->transaction_number);

                if ($response->successful()) {
                    $data = $response->json()['data'];
                    // Check payment status
                    // Accessing data.attributes.payment_intent.attributes.status or data.attributes.payments
                    // Simplified check: if payments array is not empty and status is paid
                    $payments = $data['attributes']['payments'] ?? [];
                    $isPaid = false;

                    foreach ($payments as $p) {
                        if ($p['attributes']['status'] === 'paid') {
                            $isPaid = true;
                            break;
                        }
                    }

                    if ($isPaid) {
                        // Row Locking & Idempotency
                        DB::transaction(function () use ($payment) {
                            $lockedPayment = DB::table('platform_payments')
                                ->where('platform_payment_id', $payment->platform_payment_id)
                                ->lockForUpdate()
                                ->first();

                            if ($lockedPayment && $lockedPayment->is_approved == 0) {
                                DB::table('platform_payments')
                                    ->where('platform_payment_id', $lockedPayment->platform_payment_id)
                                    ->update(['is_approved' => 1]);
                            }
                        });

                        return redirect(($user->user_type === 'property_owner' ? '/owner/homepage' : '/contractor/homepage') . '?subscription=success');
                    }
                }
            }
            catch (\Exception $e) {
                Log::error('Payment Callback Error: ' . $e->getMessage());
            }

            return redirect(($user->user_type === 'property_owner' ? '/owner/homepage' : '/contractor/homepage') . '?payment=pending_or_failed');
        }

        /**
         * Handle PayMongo Webhook
         */
        public function handleWebhook(Request $request)
        {
            Log::info('PayMongo Webhook Received', $request->all());

            $type = $request->input('data.attributes.type');

            if ($type === 'checkout_session.payment.paid') {
                $checkoutSessionId = $request->input('data.attributes.data.id');
                Log::info('Webhook: Payment Paid for Session: ' . $checkoutSessionId);

                // Find payment record
                $payment = DB::table('platform_payments')
                    ->where('transaction_number', $checkoutSessionId)
                    ->first();

                if ($payment) {
                    // Idempotency & Row Locking
                    DB::transaction(function () use ($payment) {
                        $lockedPayment = DB::table('platform_payments')
                            ->where('platform_payment_id', $payment->platform_payment_id)
                            ->lockForUpdate()
                            ->first();

                        if ($lockedPayment && $lockedPayment->is_approved == 0) {
                            DB::table('platform_payments')
                                ->where('platform_payment_id', $payment->platform_payment_id)
                                ->update(['is_approved' => 1]);

                            Log::info('Webhook: Payment marked as approved for ID: ' . $payment->platform_payment_id);
                        }
                        else {
                            Log::info('Webhook: Payment already approved (Idempotent check).');
                        }
                    });
                }
                else {
                    Log::warning('Webhook: Payment record not found for session: ' . $checkoutSessionId);
                }
            }

            return response()->json(['status' => 'received']);
        }

        /**
         * Cancel Subscription
         */
        public function cancelSubscription(Request $request)
        {
            // Try to identify user via Sanctum first
            $user = $request->user();

            // Fallback: web session user
            if (!$user && Session::has('user')) {
                $user = Session::get('user');
                if ($user) {
                    Log::info('cancelSubscription: Using session user fallback: ' . ($user->user_id ?? 'unknown'));
                }
            }

            // Fallback: allow mobile clients to pass X-User-Id header or user_id in body
            if (!$user) {
                $fallbackUserId = $request->header('X-User-Id') ?? $request->input('user_id');
                if ($fallbackUserId) {
                    $user = DB::table('users')->where('user_id', $fallbackUserId)->first();
                    if ($user) {
                        Log::info('cancelSubscription: Using fallback user from header/body: ' . $fallbackUserId);
                    }
                }
            }

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
            }

            // Normalize user ID access
            $userId = is_object($user) ? ($user->user_id ?? $user->id) : ($user['user_id'] ?? $user['id'] ?? null);

            try {
                $contractor = DB::table('contractors')->where('user_id', $userId)->first();

                if (!$contractor) {
                    return response()->json(['success' => false, 'message' => 'Contractor profile not found'], 404);
                }

                // Update latest active subscription to be unapproved (effectively cancelled in our logic)
                // Ideally we should have a 'status' column, but per user request, set is_approved = 0
                $affected = DB::table('platform_payments')
                    ->where('contractor_id', $contractor->contractor_id)
                    ->where('payment_for', 'subscription')
                    ->where('is_approved', 1)
                    ->where(function ($q) {
                    $q->whereNull('expiration_date')
                        ->orWhere('expiration_date', '>', now());
                })
                    ->update(['is_approved' => 0]);

                if ($affected) {
                    return response()->json(['success' => true, 'message' => 'Subscription cancelled successfully.']);
                }
                else {
                    return response()->json(['success' => false, 'message' => 'No active subscription found to cancel.']);
                }

            }
            catch (\Exception $e) {
                Log::error('Cancel Subscription Error: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Internal server error'], 500);
            }

        }

        /**
         * Verify a boost payment by querying PayMongo for the checkout session status.
         * Accepts either project_id (preferred) or transaction_number in body.
         */
        public function verifyBoostPayment(Request $request)
        {
            // Try to identify user via Sanctum, session, or X-User-Id fallback
            $user = $request->user();
            if (!$user && Session::has('user')) {
                $user = Session::get('user');
            }
            if (!$user) {
                $fallbackUserId = $request->header('X-User-Id') ?? $request->input('user_id');
                if ($fallbackUserId) {
                    $user = DB::table('users')->where('user_id', $fallbackUserId)->first();
                }
            }

            if (!$user) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            $projectId = $request->input('project_id');
            $transactionNumber = $request->input('transaction_number');

            if (!$transactionNumber) {
                if (!$projectId) {
                    return response()->json(['success' => false, 'message' => 'project_id or transaction_number required'], 400);
                }

                // Find latest pending platform_payment for this project
                $payment = DB::table('platform_payments')
                    ->where('project_id', $projectId)
                    ->where('payment_for', 'boosted_post')
                    ->where('is_approved', 0)
                    ->orderByDesc('platform_payment_id')
                    ->first();

                if (!$payment) {
                    return response()->json(['success' => false, 'message' => 'No pending payment found'], 404);
                }

                $transactionNumber = $payment->transaction_number;
            }

            $secretKey = env('PAYMONGO_TEST_SECRET_KEY', env('PAYMONGO_SECRET_KEY'));
            if (!$secretKey) return response()->json(['success' => false, 'message' => 'Server configuration error'], 500);

            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->get('https://api.paymongo.com/v1/checkout_sessions/' . $transactionNumber);

                Log::info('verifyBoostPayment: PayMongo query', ['transaction' => $transactionNumber, 'status' => $response->status(), 'body' => $response->body()]);

                if (!$response->successful()) {
                    return response()->json(['success' => false, 'message' => 'PayMongo query failed', 'status' => $response->status()], 502);
                }

                $data = $response->json()['data'] ?? null;
                $payments = $data['attributes']['payments'] ?? [];
                $isPaid = false;

                foreach ($payments as $p) {
                    Log::info('verifyBoostPayment: payment entry', ['payment' => $p]);
                    if (($p['attributes']['status'] ?? '') === 'paid') {
                        $isPaid = true;
                        break;
                    }
                }

                if ($isPaid) {
                    // Mark payment approved (idempotent)
                    $updated = false;
                    DB::transaction(function () use ($transactionNumber, &$updated) {
                        $paymentRow = DB::table('platform_payments')->where('transaction_number', $transactionNumber)->lockForUpdate()->first();
                        if ($paymentRow) {
                            if ($paymentRow->is_approved == 0) {
                                $affected = DB::table('platform_payments')->where('platform_payment_id', $paymentRow->platform_payment_id)->update(['is_approved' => 1]);
                                $updated = $affected > 0;
                                Log::info('verifyBoostPayment: updated platform_payments', ['platform_payment_id' => $paymentRow->platform_payment_id, 'affected' => $affected]);
                            } else {
                                Log::info('verifyBoostPayment: payment already approved', ['platform_payment_id' => $paymentRow->platform_payment_id]);
                            }
                        } else {
                            Log::warning('verifyBoostPayment: paymentRow not found for transaction', ['transaction' => $transactionNumber]);
                        }
                    });

                    return response()->json(['success' => true, 'approved' => true, 'updated' => $updated]);
                }

                return response()->json(['success' => true, 'approved' => false]);
            }
            catch (\Exception $e) {
                Log::error('verifyBoostPayment Error: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Server error'], 500);
            }
        }
    }
