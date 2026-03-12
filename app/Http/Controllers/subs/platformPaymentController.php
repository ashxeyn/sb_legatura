<?php

namespace App\Http\Controllers\subs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use App\Models\subs\platformPaymentClass;

class platformPaymentController extends Controller
{
    /**
     * Return an array of data for the modal partials (safe, non-throwing)
     */
    public static function shareModalData(): array
    {
        $user = Session::get('user');

        if (!$user) {
            $user = auth()->user();
        }

        if (!$user && request()->bearerToken()) {
            try {
                $token = \Laravel\Sanctum\PersonalAccessToken::findToken(request()->bearerToken());
                if ($token && $token->tokenable) {
                    $user = $token->tokenable;
                }
            } catch (\Throwable $e) {
                $user = null;
            }
        }

        // Fallback: check X-User-Id header (mobile app sends this)
        if (!$user) {
            $headerUserId = request()->header('X-User-Id');
            if ($headerUserId) {
                $user = DB::table('users')->where('user_id', $headerUserId)->first();
            }
        }

        // Normalize user id (support both `user_id` and `id` shapes, focusing on `user_id` first)
        $userId = null;
        if ($user) {
            if (is_object($user)) {
                $userId = $user->user_id ?? $user->id ?? null;
            } elseif (is_array($user)) {
                $userId = $user['user_id'] ?? $user['id'] ?? null;
            }
        }

        $subscription = platformPaymentClass::getSubscriptionForUser($userId);
        $boostAnalytics = platformPaymentClass::getBoostAnalytics($userId);
        $boostedPosts = platformPaymentClass::getBoostedPosts($userId);
        $boostableProjects = platformPaymentClass::getBoostableProjects($userId);

        // Get user role to filter plans
        $role = Session::get('role') ?? Session::get('current_role');
        if (!$role && $user) {
            if (is_object($user)) {
                $role = $user->role ?? $user->user_type ?? null;
            } elseif (is_array($user)) {
                $role = $user['role'] ?? $user['user_type'] ?? null;
            }
        }

        // Determine whether subscription plans should be loaded in contractor context.
        // Representatives/staff should see contractor plans even if they do not own the contractor record.
        $isContractor = false;
        $ownerId = null;
        if ($userId) {
            try {
                $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
            } catch (\Throwable $e) {
                $ownerId = null;
            }
        }

        if ($role === 'contractor') {
            $isContractor = true;
        } elseif ($role === 'both' && $userId) {
            // Check current_role session or default to contractor for 'both' users
            $currentRole = Session::get('current_role');
            $isContractor = ($currentRole === 'contractor' || !$currentRole);
        } else {
            // For property_owner users or unclear role, treat as contractor when:
            // 1) they own a contractor company, OR
            // 2) they are an active contractor staff member (e.g., representative).
            if ($ownerId) {
                try {
                    $ownsContractor = DB::table('contractors')->where('owner_id', $ownerId)->exists();
                    $activeStaffMembership = DB::table('contractor_staff')
                        ->where('owner_id', $ownerId)
                        ->whereNull('deletion_reason')
                        ->where(function ($q) {
                            $q->where('is_active', 1)
                              ->orWhere('is_active', true)
                              ->orWhere('is_active', '1');
                        })
                        ->where(function ($q) {
                            $q->whereNull('is_suspended')
                              ->orWhere('is_suspended', 0)
                              ->orWhere('is_suspended', false)
                              ->orWhere('is_suspended', '0');
                        })
                        ->exists();

                    $isContractor = $ownsContractor || $activeStaffMembership;
                } catch (\Throwable $e) {
                    $isContractor = false;
                }
            }
        }

        $plansQuery = DB::table('subscription_plans')
            ->where('plan_key', '!=', 'boost')
            ->where('is_active', 1)
            ->where('is_deleted', 0);

        // Always filter by role to avoid mixing owner and contractor plans
        $plansQuery->where('for_contractor', $isContractor ? 1 : 0);

        $plans = $plansQuery->get()
            ->map(function ($plan) {
                if (!empty($plan->benefits)) {
                    $plan->benefits = is_string($plan->benefits) ? json_decode($plan->benefits, true) : $plan->benefits;
                }
                return $plan;
            });

        $ownerPlans = DB::table('subscription_plans')
            ->where('for_contractor', 0)
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get()
            ->map(function ($plan) {
                if (!empty($plan->benefits)) {
                    $plan->benefits = is_string($plan->benefits) ? json_decode($plan->benefits, true) : $plan->benefits;
                }
                return $plan;
            });

        $boostPlan = DB::table('subscription_plans')
            ->where('plan_key', 'boost')
            ->first();
        if ($boostPlan && !empty($boostPlan->benefits)) {
            $boostPlan->benefits = is_string($boostPlan->benefits) ? json_decode($boostPlan->benefits, true) : $boostPlan->benefits;
        }

        return [
            'subscription' => $subscription,
            'boostAnalytics' => $boostAnalytics,
            'boostedPosts' => $boostedPosts,
            'boostableProjects' => $boostableProjects,
            'plans' => $plans,
            'ownerPlans' => $ownerPlans,
            'boostPlan' => $boostPlan,
        ];
    }

    /**
     * Optional JSON endpoint for frontend fetch if needed
     */
    public function modalData(Request $request)
    {
        return response()->json(self::shareModalData());
    }
}
