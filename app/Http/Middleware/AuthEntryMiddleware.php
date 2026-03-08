<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * AuthEntryMiddleware
 *
 * Protects auth/signup GET pages from direct URL access.
 * A valid short-lived session token must be present, set by the
 * gate-setter routes (/auth/gate/*). Without it the request
 * receives a 404 so the page appears non-existent to URL guessers.
 *
 * POST endpoints (form submissions) are not covered here and remain
 * reachable so login / signup multi-step AJAX flows are unaffected.
 */
class AuthEntryMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->session()->get('auth_entry_token');
        $expiry = $request->session()->get('auth_entry_expiry', 0);

        if (!$token || time() > $expiry) {
            // Remove stale token if present
            $request->session()->forget(['auth_entry_token', 'auth_entry_expiry']);
            abort(404);
        }

        // Token is valid — consume it so the same token cannot be replayed
        // (each navigation through a gate produces a fresh one)
        $request->session()->forget(['auth_entry_token', 'auth_entry_expiry']);

        return $next($request);
    }
}
