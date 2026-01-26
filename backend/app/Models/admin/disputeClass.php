<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class disputeClass extends Model
{
    protected $primaryKey = 'dispute_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_id',
        'reporter_id',
        'description',
        'status'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(projectClass::class, 'project_id', 'project_id');
    }
}
