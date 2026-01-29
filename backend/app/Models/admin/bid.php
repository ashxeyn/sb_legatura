<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class bid extends Model
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
        return $this->belongsTo(project::class, 'project_id', 'project_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(contractor::class, 'contractor_id', 'contractor_id');
    }
}
