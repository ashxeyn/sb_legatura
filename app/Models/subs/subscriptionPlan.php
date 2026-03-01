<?php

namespace App\Models\subs;

use Illuminate\Database\Eloquent\Model;
use App\Models\admin\PlatformPayment;

class subscriptionPlan extends Model
{
    protected $table = 'subscription_plans';

    protected $fillable = [
        'plan_key',
        'name',
        'amount',
        'currency',
        'billing_cycle',
        'description',
        'benefits',
        'is_active'
    ];

    protected $casts = [
        'benefits' => 'array',
        'is_active' => 'boolean',
        'amount' => 'integer' // Stored in cents/centavos
    ];

    /**
     * Get the payments associated with this plan.
     */
    public function payments()
    {
        return $this->hasMany(PlatformPayment::class, 'subscriptionPlanId');
    }
}
