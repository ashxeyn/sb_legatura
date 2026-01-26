<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class bidClass extends Model
{
    protected $primaryKey = 'bid_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_id',
        'contractor_id',
        'proposed_cost',
        'submitted_at',
        'bid_status'
    ];

    protected $casts = [
        'submitted_at' => 'datetime'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(projectClass::class, 'project_id', 'project_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(contractorClass::class, 'contractor_id', 'contractor_id');
    }
}
