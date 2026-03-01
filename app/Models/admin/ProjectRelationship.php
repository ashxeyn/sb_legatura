<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectRelationship extends Model
{
    protected $primaryKey = 'rel_id';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'owner_id',
        'project_id'
    ];

    public function owner(): HasOne
    {
        return $this->hasOne(PropertyOwner::class, 'owner_id', 'owner_id');
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class, 'project_id', 'project_id');
    }
}
