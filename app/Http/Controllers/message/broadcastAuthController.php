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
                    $socketId = $socketId ?: ($json['socket_id'] ?? null);
                } else {
                    // Try form-encoded
                    parse_str($rawBody, $parsed);
                    $channelName = $channelName ?: ($parsed['channel_name'] ?? null);
                    $socketId = $socketId ?: ($parsed['socket_id'] ?? null);
                }
            }
        }

        // Try Bearer token — manual DB lookup (avoids auth guard crash on PHP dev server)
        $currentUserId = null;

        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            // Sanctum tokens: {id}|{plaintext} — hash only the plaintext
            $tokenParts = explode('|', $bearerToken, 2);
            $plainText = count($tokenParts) === 2 ? $tokenParts[1] : $bearerToken;
            $tokenHash = hash('sha256', $plainText);
            $tokenRecord = \Illuminate\Support\Facades\DB::table('personal_access_tokens')
                ->where('token', $tokenHash)
                ->first();
            if ($tokenRecord) {
                $currentUserId = (int) $tokenRecord->tokenable_id;
            }
        }

        // Fallback: session-based auth (admin web dashboard)
        if (!$currentUserId) {
            $sessionUser = session('user');
            if ($sessionUser) {
                // Admin users: admin_id is VARCHAR ('ADMIN-1') — extract numeric part; Regular users: user_id or id
                $currentUserId = isset($sessionUser->admin_id)
                    ? (int) preg_replace('/[^0-9]/', '', $sessionUser->admin_id)
                    : ($sessionUser->user_id ?? $sessionUser->id ?? null);
            }
        }

        // Fallback: X-User-Id header
        if (!$currentUserId) {
            $headerUserId = $request->header('X-User-Id');
            if ($headerUserId) {
                $currentUserId = (int) $headerUserId;
            }
        }

        if (!$currentUserId) {
            Log::warning('Broadcasting auth failed - No user found');
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        Log::info('Broadcasting auth attempt', [
            'channel_name' => $channelName,
            'socket_id' => $socketId,
            'current_user_id' => $currentUserId
        ]);

        // Handle presence channels (format: presence-online)
        if (preg_match('/^presence-/', $channelName)) {
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

                // Get user info for presence channel
                $sessionUser = session('user');
                $userName = 'User';
                
                if ($sessionUser) {
                    if (isset($sessionUser->admin_id)) {
                        $userName = $sessionUser->username ?? 'Admin';
                    } else {
                        $userName = $sessionUser->username ?? $sessionUser->email ?? 'User';
                    }
                }

                $presenceData = [
                    'user_id' => $currentUserId,
                    'user_info' => [
                        'id' => $currentUserId,
                        'name' => $userName
                    ]
                ];

                $auth = $pusher->authorizePresenceChannel($channelName, $socketId, $currentUserId, $presenceData['user_info']);

                Log::info('Presence channel auth SUCCESS for user ' . $currentUserId);

                return response()->json(json_decode($auth, true));

            } catch (\Exception $e) {
                Log::error('Pusher presence auth failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['message' => 'Server error: ' . $e->getMessage()], 500);
            }
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

                Log::info('Broadcasting auth SUCCESS for user ' . $currentUserId);

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
