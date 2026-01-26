<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class milestoneClass extends Model
{
    protected $primaryKey = 'milestone_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'amount',
        'due_date',
        'status'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'amount' => 'decimal:2'
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(projectClass::class, 'project_id', 'project_id');
    }
}
