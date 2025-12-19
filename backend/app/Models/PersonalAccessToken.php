<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class PersonalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Override to ensure it works with custom primary key (user_id)
     */
    public function tokenable()
    {
        return $this->morphTo('tokenable');
    }
}

