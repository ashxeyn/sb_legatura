<?php

namespace App\Models\admin;

use Illuminate\Database\Eloquent\Model;
use App\Models\subs\subscriptionPlan;

class PlatformPayment extends Model
{
    protected $primaryKey = 'platform_payment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $table = 'platform_payments';

    protected $fillable = [
        'subscriptionPlanId',
        'project_id',
        'contractor_id',
        'owner_id',
        'amount',
        'transaction_number',
        'transaction_date',
        'is_approved',
        'approved_by',
        'expiration_date',
        'payment_type'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2',
        'is_approved' => 'boolean'
    ];

    /**
     * Get the subscription plan associated with the payment.
     */
    public function subscriptionPlan()
    {
        return $this->belongsTo(subscriptionPlan::class, 'subscriptionPlanId');
    }
}
