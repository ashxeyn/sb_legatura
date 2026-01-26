<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class platformPaymentClass extends Model
{
    protected $primaryKey = 'payment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $table = 'platform_payments';

    protected $fillable = [
        'payment_for',
        'amount',
        'transaction_date',
        'is_approved'
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:2'
    ];
}
