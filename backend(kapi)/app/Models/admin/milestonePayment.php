<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MilestonePayment extends Model
{
    protected $primaryKey = 'payment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $table = 'milestone_payments';

    protected $fillable = [
        'milestone_id',
        'project_id',
        'payer_id',
        'amount',
        'transaction_date',
        'receipt_photo',
        'payment_status'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class, 'milestone_id', 'milestone_id');
    }
}
