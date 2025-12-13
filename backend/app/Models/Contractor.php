<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contractor extends Model
{
    protected $primaryKey = 'contractor_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'company_name',
        'years_of_experience',
        'type_id',
        'contractor_type_other',
        'verification_status'
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(Bid::class, 'contractor_id', 'contractor_id');
    }
}
