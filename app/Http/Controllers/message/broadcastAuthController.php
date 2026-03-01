<?php

namespace App\Http\Controllers\message;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Pusher\Pusher;

class broadcastAuthController extends Controller
{
    /**
     * Custom broadcasting authorization for session-based users
     * (bypasses Laravel's guard requirement)
     */
    public function authorize(Request $request)
    {
        // Handle both form data and JSON payloads
        $channelName = $request->input('channel_name') ?? $request->channel_name;
        $socketId = $request->input('socket_id') ?? $request->socket_id;

        // Fallback: parse raw body when Content-Type mismatch causes empty input
        // (Pusher JS sends form-encoded body but mobile clients may set JSON content-type)
        if (empty($channelName) || empty($socketId)) {
            $rawBody = $request->getContent();
            if ($rawBody) {
                // Try JSON first
                $json = json_decode($rawBody, true);
                if ($json) {
                    $channelName = $channelName ?: ($json['channel_name'] ?? null);
                    $socketId    = $socketId    ?: ($json['socket_id'] ?? null);
                } else {
                    // Try form-encoded
                    parse_str($rawBody, $parsed);
                    $channelName = $channelName ?: ($parsed['channel_name'] ?? null);
                    $socketId    = $socketId    ?: ($parsed['socket_id'] ?? null);
                }
            }
        }

        // Try Sanctum token auth first (mobile app sends Bearer token)
        $currentUserId = null;

        if (auth('sanctum')->check()) {
            $authUser = auth('sanctum')->user();
            $currentUserId = $authUser->user_id ?? $authUser->id ?? null;
        }

        // Fallback: session-based auth (admin web dashboard)
        if (!$currentUserId) {
            $sessionUser = session('user');
            if ($sessionUser) {
                // Admin users: admin_id, Regular users: user_id or id
                $currentUserId = $sessionUser->admin_id ?? $sessionUser->user_id ?? $sessionUser->id ?? null;
            }
        }

        // Fallback: default Laravel auth guard
        if (!$currentUserId && auth()->check()) {
            $authUser = auth()->user();
            $currentUserId = $authUser->user_id ?? $authUser->id ?? null;
        }

        if (!$currentUserId) {
            Log::warning('Broadcasting auth failed - No user found');
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Parse channel name to get user ID (format: private-chat.{userId})
        if (preg_match('/private-chat\.(\d+)/', $channelName, $matches)) {
            $channelUserId = (int) $matches[1];

            // Only allow users to subscribe to their own channel
            if ($currentUserId !== $channelUserId) {
                Log::warning('Broadcasting auth failed - User mismatch', [
                    'current_user' => $currentUserId,
                    'channel_user' => $channelUserId
                ]);
                return response()->json(['message' => 'Forbidden'], 403);
            }

            // Generate Pusher auth signature
            try {
                $pusher = new Pusher(
                    env('PUSHER_APP_KEY'),
                    env('PUSHER_APP_SECRET'),
                    env('PUSHER_APP_ID'),
                    [
                        'cluster' => env('PUSHER_APP_CLUSTER', 'mt1'),
                        'useTLS' => true
                    ]
                );

                $auth = $pusher->authorizeChannel($channelName, $socketId);

                // Log::info('Broadcasting auth SUCCESS for user ' . $currentUserId);

                return response()->json(json_decode($auth, true));

            } catch (\Exception $e) {
                Log::error('Pusher auth signature failed', [
                    'error' => $e->getMessage()
                ]);
                return response()->json(['message' => 'Server error'], 500);
            }
        }

        Log::warning('Broadcasting auth failed - Invalid channel format', [
            'channel' => $channelName
        ]);

        return response()->json(['message' => 'Invalid channel'], 400);
    }
}
