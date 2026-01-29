<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class AdminAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Session::get('user');
        $type = Session::get('userType');

        if (!$user || $type !== 'admin') {
            return redirect('/accounts/login')->with('error', 'Admin access only.');
        }

        return $next($request);
    }
}
