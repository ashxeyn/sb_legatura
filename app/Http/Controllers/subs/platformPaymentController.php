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
        $role = Session::get('role');
        if (!$role && $user) {
            if (is_object($user)) {
                $role = $user->role ?? $user->user_type ?? null;
            } elseif (is_array($user)) {
                $role = $user['role'] ?? $user['user_type'] ?? null;
            }
        }

        // Default to owner if role not found, though ideally it should be explicitly set
        $isContractor = ($role === 'contractor') ? 1 : 0;

        $plansQuery = DB::table('subscription_plans')
            ->where('plan_key', '!=', 'boost')
            ->where('is_active', 1)
            ->where('is_deleted', 0);

        if ($isContractor) {
            $plansQuery->where('for_contractor', 1);
        }

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
