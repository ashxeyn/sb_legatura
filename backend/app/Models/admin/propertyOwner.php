<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropertyOwner extends Model
{
    protected $primaryKey = 'owner_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone_number',
        'occupation_id',
        'occupation_other',
        'verification_status'
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'owner_id', 'owner_id');
    }
}
