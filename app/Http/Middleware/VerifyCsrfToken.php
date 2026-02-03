<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    protected $except = [
        // Exclude API routes (token-based) from CSRF verification
        'api/*',
        // Exempt mobile-only signup and switch endpoints which cannot read
        // Laravel's XSRF cookie in React Native/Expo environments.
        'accounts/signup/*',
        'accounts/switch/*',
        // Public storage/file serving used by mobile app
        'storage/*',
    ];
}
