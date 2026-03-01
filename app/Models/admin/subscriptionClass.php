<?php

namespace App\Models\admin;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class subscriptionClass
{
    /**
     * Get all active subscription plans.
     */
    public static function getPlans()
    {
        return DB::table('subscription_plans')
            ->where('is_active', 1)
            ->where('is_deleted', 0)
            ->get();
    }

    /**
     * Get subscription statistics for the dashboard.
     */
    public static function getStats()
    {
        $now = Carbon::now();
        $sevenDaysLater = Carbon::now()->addDays(7);

        // Total Subscriptions (Active and Approved)
        $totalSub = DB::table('platform_payments')
            ->whereNotNull('subscriptionPlanId')
            ->where('is_approved', 1)
            ->where(function ($query) use ($now) {
                $query->whereNull('expiration_date')
                    ->orWhere('expiration_date', '>', $now);
            })
            ->count();

        // Total Revenue (All approved payments)
        $totalRevenue = DB::table('platform_payments')
            ->where('is_approved', 1)
            ->sum('amount');

        // Expiring Soon (Next 7 days)
        $expiringSoon = DB::table('platform_payments')
            ->where('is_approved', 1)
            ->whereNotNull('expiration_date')
            ->whereBetween('expiration_date', [$now, $sevenDaysLater])
            ->count();

        // Expired
        $expired = DB::table('platform_payments')
            ->where('is_approved', 1)
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<', $now)
            ->count();

        return [
            'total' => $totalSub,
            'revenue' => number_format($totalRevenue, 2),
            'expiring_soon' => $expiringSoon,
            'expired' => $expired,
            'limit' => 300 // Placeholder for goal UI
        ];
    }

    /**
     * Get subscriptions by status.
     */
    public static function getSubscriptions($status = 'active')
    {
        $now = Carbon::now();

        $query = DB::table('platform_payments')
            ->leftJoin('subscription_plans', 'platform_payments.subscriptionPlanId', '=', 'subscription_plans.id')
            ->leftJoin('contractors', 'platform_payments.contractor_id', '=', 'contractors.contractor_id')
            ->leftJoin('projects', 'platform_payments.project_id', '=', 'projects.project_id')
            ->leftJoin('property_owners', 'platform_payments.owner_id', '=', 'property_owners.owner_id')
            ->select(
                'platform_payments.*',
                'subscription_plans.name as plan_name',
                'subscription_plans.plan_key',
                'subscription_plans.billing_cycle',
                'subscription_plans.duration_days',
                'contractors.company_name',
                'projects.project_title',
                'property_owners.first_name',
                'property_owners.last_name'
            );

        if ($status === 'active') {
            $query->where('platform_payments.is_approved', 1)
                ->where(function ($q) use ($now) {
                    $q->whereNull('platform_payments.expiration_date')
                        ->orWhere('platform_payments.expiration_date', '>', $now);
                });
        } elseif ($status === 'expired') {
            $query->where('platform_payments.is_approved', 1)
                ->where('platform_payments.expiration_date', '<=', $now);
        } elseif ($status === 'cancelled') {
            $query->where('platform_payments.is_approved', 0)
                ->where('platform_payments.is_cancelled', 1)
                ->where('platform_payments.expiration_date', '>', $now);
        }

        return $query->orderByDesc('platform_payments.transaction_date')->get();

    }
    /**
     * Add a subscription plan.
     */
    public static function addPlan($data)
    {
        return DB::table('subscription_plans')->insert([
            'name' => $data['name'],
            'plan_key' => $data['plan_key'],
            'for_contractor' => $data['for_contractor'] ?? 0,
            'amount' => (int) ($data['price'] * 100), // Store as cents
            'billing_cycle' => $data['billing_cycle'],
            'duration_days' => $data['duration_days'] ?? null,
            'benefits' => json_encode($data['benefits']),
            'is_active' => 1,
            'is_deleted' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }

    /**
     * Update a subscription plan.
     */
    public static function updatePlan($id, $data)
    {
        return DB::table('subscription_plans')
            ->where('id', $id)
            ->update([
                'name' => $data['name'],
                'amount' => (int) ($data['price'] * 100), // Store as cents
                'billing_cycle' => $data['billing_cycle'],
                'duration_days' => $data['duration_days'] ?? null,
                'benefits' => json_encode($data['benefits']),
                'updated_at' => Carbon::now(),
            ]);
    }

    /**
     * Delete a subscription plan (soft delete with reason).
     */
    public static function deletePlan($id, $reason)
    {
        return DB::table('subscription_plans')
            ->where('id', $id)
            ->update([
                'is_deleted' => 1,
                'is_active' => 0,
                'deletion_reason' => $reason,
                'updated_at' => Carbon::now(),
            ]);
    }

    /**
     * Deactivate a platform payment (set is_approved = 0).
     */
    public static function deactivate($id, $reason)
    {
        return DB::table('platform_payments')
            ->where('platform_payment_id', $id)
            ->update([
                'is_approved' => 0,
                'is_cancelled' => 1,
                'deactivation_reason' => $reason,
            ]);
    }

    /**
     * Reactivate a platform payment (set is_approved = 1).
     */
    public static function reactivate($id)
    {
        return DB::table('platform_payments')
            ->where('platform_payment_id', $id)
            ->update([
                'is_approved' => 1,
                'is_cancelled' => 0,
                'deactivation_reason' => null,
            ]);
    }
}
