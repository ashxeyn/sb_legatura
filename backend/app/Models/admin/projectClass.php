<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class projectClass extends Model
{
    protected $primaryKey = 'project_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'relationship_id',
        'project_title',
        'project_description',
        'project_location',
        'budget_range_min',
        'budget_range_max',
        'lot_size',
        'property_type',
        'type_id',
        'to_finish',
        'project_status',
        'selected_contractor_id',
        'bidding_deadline'
    ];

    protected $casts = [
        'bidding_deadline' => 'datetime',
        'budget_range_min' => 'decimal:2',
        'budget_range_max' => 'decimal:2',
    ];

    public function relationship(): BelongsTo
    {
        return $this->belongsTo(projectRelationshipClass::class, 'relationship_id', 'rel_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(bidClass::class, 'project_id', 'project_id');
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(milestoneClass::class, 'project_id', 'project_id');
    }
}
