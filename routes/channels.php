<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/*
|--------------------------------------------------------------------------
| Private Chat Channels
|--------------------------------------------------------------------------
| Authorization for private chat channels
| Only the receiver of the message can listen to their own channel
*/

Broadcast::channel('chat.{userId}', function ($user, $userId) {
    // Handle both Sanctum (API) and session (web) authentication
    $currentUserId = null;

    if ($user) {
        // Sanctum authenticated user (mobile app)
        $currentUserId = $user->user_id ?? $user->id ?? null;
    }

    // If no $user from guard, check custom session data (admin web dashboard)
    if (!$currentUserId) {
        $sessionUser = session('user');
        if ($sessionUser) {
            // Admin users: admin_id is VARCHAR 'ADMIN-1' - extract numeric part
            // Regular users: user_id or id
            if (isset($sessionUser->admin_id)) {
                $currentUserId = (int) preg_replace('/[^0-9]/', '', $sessionUser->admin_id);
            } else {
                $currentUserId = $sessionUser->user_id ?? $sessionUser->id ?? null;
            }
        }
    }

    // Also check Laravel's default auth (fallback)
    if (!$currentUserId && auth()->check()) {
        $authUser = auth()->user();
        $currentUserId = $authUser->user_id ?? $authUser->id ?? null;
    }

    \Log::info('Broadcasting auth check', [
        'channel_user_id' => $userId,
        'current_user_id' => $currentUserId,
        'has_guard_user' => !!$user,
        'has_session' => !!session('user'),
        'session_admin_id' => session('user')->admin_id ?? null,
        'has_auth' => auth()->check(),
        'session_id' => session()->getId(),
        'request_ip' => request()->ip()
    ]);

    // Allow if user IDs match
    if ((int) $currentUserId === (int) $userId) {
        \Log::info('Broadcasting auth SUCCESS for user ' . $currentUserId);
        return true;
    }

    \Log::warning('Broadcasting auth FAILED', [
        'expected' => $userId,
        'got' => $currentUserId
    ]);

    return false;
});
