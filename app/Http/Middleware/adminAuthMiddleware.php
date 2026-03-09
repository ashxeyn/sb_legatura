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

        // Resolve admin_id safely (object or array)
        $adminId = null;
        if (is_object($user)) {
            $adminId = property_exists($user, 'admin_id') ? $user->admin_id : null;
        } elseif (is_array($user)) {
            $adminId = $user['admin_id'] ?? null;
        }

        $authenticated = $adminId && $type === 'admin';

        if (!$authenticated) {
            // AJAX / JSON requests → return 401 JSON so the blade JS can handle it
            if ($request->expectsJson() || $request->ajax() || $request->is('*/data') || $request->is('*/update') || $request->is('*/change-password') || $request->is('*/delete')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated. Please log in as admin.',
                ], 401);
            }

            // Normal page request → redirect to login
            return redirect('/accounts/login')->with('error', 'Admin access only.');
        }

        return $next($request);
    }
}