<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispute extends Model
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
        return $this->belongsTo(Project::class, 'project_id', 'project_id');
    }
}
