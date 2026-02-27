<?php

namespace App\Models\both;

use Illuminate\Database\Eloquent\Model;

class ProjectUpdate extends Model
{
    protected $table = 'project_updates';
    protected $primaryKey = 'extension_id';

    protected $fillable = [
        'project_id',
        'contractor_user_id',
        'owner_user_id',
        'current_end_date',
        'proposed_end_date',
        'reason',
        'current_budget',
        'proposed_budget',
        'budget_change_type',
        'has_additional_cost',
        'additional_amount',
        'milestone_changes',
        'allocation_mode',
        'status',
        'owner_response',
        'revision_notes',
        'applied_at',
    ];

    protected $casts = [
        'has_additional_cost' => 'boolean',
        'additional_amount'   => 'decimal:2',
        'current_budget'      => 'decimal:2',
        'proposed_budget'     => 'decimal:2',
        'current_end_date'    => 'date:Y-m-d',
        'proposed_end_date'   => 'date:Y-m-d',
        'milestone_changes'   => 'array',
        'applied_at'          => 'datetime',
    ];
}
