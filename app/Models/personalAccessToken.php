<?php

namespace App\Models;

use Laravel\Sanctum\personalAccessToken as SanctumPersonalAccessToken;

class personalAccessToken extends SanctumPersonalAccessToken
{
    /**
     * Get the tokenable model that the access token belongs to.
     */
    public function tokenable()
    {
        return $this->morphTo(__FUNCTION__, 'tokenable_type', 'tokenable_id');
    }
}




