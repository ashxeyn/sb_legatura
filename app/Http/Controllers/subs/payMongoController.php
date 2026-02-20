<?php

namespace App\Http\Controllers\subs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
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

        // Manual session check replacing Auth::user()
        if (!Session::has('user')) {
            Log::warning('createSubscriptionCheckout: Unauthorized (Session missing)');
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = Session::get('user');

        if (!$user) {
            Log::warning('createSubscriptionCheckout: Unauthorized (User object null)');
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get Contractor ID
        $contractor = DB::table('contractors')->where('user_id', $user->user_id)->first();
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

        // Ensure secret key is present
        if (!$secretKey) {
            Log::error('PayMongo Secret Key missing in .env');
            return response()->json(['success' => false, 'message' => 'Server configuration error'], 500);
        }

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'billing' => [
                            'name' => $user->username,
                            'email' => $user->email,
                        ],
                        'line_items' => [[
                                'currency' => 'PHP',
                                'amount' => $plan['amount'],
                                'description' => $plan['name'],
                                'name' => $plan['name'],
                                'quantity' => 1
                            ]],
                        'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
                        'success_url' => route('payment.callback', ['type' => 'subscription', 'plan' => $planTier]),
                        'cancel_url' => url(($user->user_type === 'property_owner' ? '/owner/homepage' : '/contractor/homepage') . '?subscription=cancelled'),
                        'metadata' => [
                            'user_id' => $user->user_id,
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
                        'is_approved' => 0, // Pending
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
        // Manual session check replacing Auth::user()
        if (!Session::has('user')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        $user = Session::get('user');

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get Property Owner ID
        $owner = DB::table('property_owners')->where('user_id', $user->user_id)->first();
        if (!$owner) {
            Log::error('createBoostCheckout: Owner profile not found for user ' . $user->user_id);
            return response()->json(['success' => false, 'message' => 'Owner profile not found'], 404);
        }

        $projectId = $request->input('project_id');
        if (!$projectId) {
            return response()->json(['success' => false, 'message' => 'Project ID required'], 400);
        }

        $secretKey = env('PAYMONGO_TEST_SECRET_KEY', env('PAYMONGO_SECRET_KEY'));

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->post('https://api.paymongo.com/v1/checkout_sessions', [
                'data' => [
                    'attributes' => [
                        'billing' => [
                            'name' => $user->username,
                            'email' => $user->email,
                        ],
                        'line_items' => [[
                                'currency' => 'PHP',
                                'amount' => 4900, // 49.00 PHP
                                'description' => 'Project Boost - 7 Days',
                                'name' => 'Project Boost',
                                'quantity' => 1
                            ]],
                        'payment_method_types' => ['card', 'gcash', 'paymaya', 'grab_pay'],
                        'success_url' => route('payment.callback', ['type' => 'boost', 'project_id' => $projectId]),
                        'cancel_url' => url('/owner/homepage?boost=cancelled'),
                        'metadata' => [
                            'user_id' => $user->user_id,
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
                    DB::table('platform_payments')->insert([
                        'owner_id' => $owner->owner_id,
                        'project_id' => $projectId,
                        'payment_for' => 'boosted_post',
                        'amount' => 49.00,
                        'transaction_number' => $checkoutSessionId,
                        'is_approved' => 0, // Pending
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

                            // Apply Boost logic if needed
                            if ($lockedPayment->payment_for === 'boosted_post' && $lockedPayment->project_id) {
                                DB::table('projects')
                                    ->where('project_id', $lockedPayment->project_id)
                                    ->update([
                                    'is_boosted' => 1,
                                    'boost_expires_at' => now()->addDays(7)
                                ]);
                            }
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

                        // If boosted post, update project
                        if ($lockedPayment->payment_for === 'boosted_post' && $lockedPayment->project_id) {
                            DB::table('projects')
                                ->where('project_id', $lockedPayment->project_id)
                                ->update([
                                'is_boosted' => 1,
                                'boost_expires_at' => now()->addDays(7)
                            ]);
                            Log::info('Webhook: Project ' . $lockedPayment->project_id . ' boosted.');
                        }
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
        $user = Session::get('user');
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
}
