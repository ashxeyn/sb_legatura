<?php

namespace App\Http\Controllers\message;

use App\Http\Controllers\Controller;
use App\Http\Requests\message\messageRequest;
use App\Models\message\messageClass;
use App\Events\messageSentEvent;
use App\Events\conversationSuspendedEvent;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class messageController extends Controller
{
    /**
     * Get authenticated user ID from either Bearer token (mobile), session (web), or X-User-Id header
     * NOTE: Uses manual token lookup instead of auth('sanctum') to avoid PHP dev server crash on Windows
     */
    private function getAuthUserId(): ?int
    {
        // 1. Try Bearer token — manual DB lookup (same pattern as payMongoController)
        $bearerToken = request()->bearerToken();
        if ($bearerToken) {
            try {
                // Sanctum tokens are formatted as {id}|{plaintext}
                // We need to hash only the plaintext part (after the pipe)
                $tokenParts = explode('|', $bearerToken, 2);
                $plainText = count($tokenParts) === 2 ? $tokenParts[1] : $bearerToken;
                $tokenHash = hash('sha256', $plainText);
                $tokenRecord = DB::table('personal_access_tokens')
                    ->where('token', $tokenHash)
                    ->first();
                if ($tokenRecord) {
                    return (int) $tokenRecord->tokenable_id;
                }
            } catch (\Exception $e) {
                Log::warning('getAuthUserId: Bearer token lookup failed', ['error' => $e->getMessage()]);
            }
        }

        // 2. Fallback to session (admin web dashboard)
        $sessionUser = session('user');
        if ($sessionUser) {
            if (isset($sessionUser->admin_id)) {
                // admin_id is now VARCHAR ('ADMIN-1') — extract numeric part for conversation sender_id (bigint)
                return (int) preg_replace('/[^0-9]/', '', $sessionUser->admin_id);
            }
            return $sessionUser->user_id ?? $sessionUser->id ?? null;
        }

        // 3. Fallback: X-User-Id header (mobile clients)
        $headerUserId = request()->header('X-User-Id');
        if ($headerUserId) {
            return (int) $headerUserId;
        }

        return null;
    }

    /**
     * Broadcast typing indicator to the receiver's channel
     * Ultra-lightweight: no DB queries, just auth + Pusher trigger
     */
    public function typing(Request $request): JsonResponse
    {
        $userId = $this->getAuthUserId();
        if (!$userId) {
            return response()->json(['success' => false], 401);
        }

        $receiverId = $request->input('receiver_id');
        $conversationId = $request->input('conversation_id');
        if (!$receiverId) {
            return response()->json(['success' => false], 422);
        }

        try {
            $pusher = new \Pusher\Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                [
                    'cluster' => config('broadcasting.connections.pusher.options.cluster'),
                    'useTLS' => true,
                ]
            );

            $pusher->trigger("private-chat.{$receiverId}", 'client-typing', [
                'user_id' => $userId,
                'conversation_id' => $conversationId,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }

    /**
     * Check if current user is an admin
     */
    private function isAdmin(int $userId): bool
    {
        // Check if the CURRENT SESSION is an admin session (has admin_id in session)
        // Do NOT check DB with numeric ID as that causes collision between
        // admin_id='ADMIN-1' and user_id=1
        $sessionUser = session('user');
        return $sessionUser && isset($sessionUser->admin_id);
    }
    /**
     * Get inbox for the authenticated user
     * Returns list of conversations with latest message preview
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $inbox = messageClass::getInbox($userId);

            // ADMIN "All" tab: only show conversations admin directly initiated (is_admin_conversation=1).
            // admin_id is now VARCHAR ('ADMIN-1') but sender_id in conversations is still bigint (numeric part),
            // so getInbox($numericAdminId) accidentally returns user conversations with the same numeric ID.
            // We use the is_admin_conversation flag to cleanly separate admin from user conversations.
            $sessionUser = session('user');
            $isAdminSession = $sessionUser && isset($sessionUser->admin_id);

            if ($isAdminSession) {
                // Admin: show ONLY admin-initiated conversations
                $adminConvIds = DB::table('conversations')
                    ->where('is_admin_conversation', 1)
                    ->pluck('conversation_id')
                    ->toArray();

                $inbox = array_values(array_filter($inbox, function ($conv) use ($adminConvIds) {
                    return in_array($conv['conversation_id'], $adminConvIds);
                }));
            } else {
                // Regular users: Only exclude admin conversations if there's an ID collision
                // (i.e., if the user's ID matches an admin's numeric ID portion)
                // User ID 5 should see their conversation with admin
                // User ID 1 should NOT see admin's conversations (because admin_id='ADMIN-1' extracts to 1)
                $adminNumericIds = DB::table('admin_users')
                    ->pluck('admin_id')
                    ->map(fn($aid) => (int) preg_replace('/[^0-9]/', '', $aid))
                    ->toArray();

                if (in_array($userId, $adminNumericIds)) {
                    // This user's ID collides with an admin - exclude admin conversations
                    $adminConvIds = DB::table('conversations')
                        ->where('is_admin_conversation', 1)
                        ->pluck('conversation_id')
                        ->toArray();

                    $inbox = array_values(array_filter($inbox, function ($conv) use ($adminConvIds) {
                        return !in_array($conv['conversation_id'], $adminConvIds);
                    }));
                }
                // If no collision, user sees all their conversations including those with admin
            }

            return response()->json([
                'success' => true,
                'data' => $inbox,
                'count' => count($inbox)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch inbox',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get full conversation history
     *
     * @param int $conversationId
     * @return JsonResponse
     */
    public function show(int $conversationId): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $isAdminUser = $this->isAdmin($userId);

            // Get the conversation first to check is_admin_conversation
            $conversation = DB::table('conversations')
                ->where('conversation_id', $conversationId)
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found'
                ], 404);
            }

            // Check if user is a participant in this conversation
            $isParticipantInConv = $conversation->sender_id == $userId || $conversation->receiver_id == $userId;

            if ($isAdminUser) {
                // Admin can view ANY conversation for moderation purposes
                // This includes their own admin conversations AND flagged/suspended user conversations
                // No restriction needed - admin is the moderator
            } else {
                // Regular user - must be a participant
                if (!$isParticipantInConv) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this conversation'
                    ], 403);
                }

                // If this is an admin conversation, only block if user's ID collides with an admin ID
                // (user_id=5 can view their chat with admin, but user_id=1 cannot view admin's chats)
                if ($conversation->is_admin_conversation) {
                    $adminNumericIds = DB::table('admin_users')
                        ->pluck('admin_id')
                        ->map(fn($aid) => (int) preg_replace('/[^0-9]/', '', $aid))
                        ->toArray();

                    if (in_array($userId, $adminNumericIds)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Unauthorized access to this conversation'
                        ], 403);
                    }
                }
            }

            $messages = messageClass::getConversationHistory($conversationId);

            // Mark messages as read if user is a participant (sender or receiver)
            $isParticipant = $conversation->sender_id == $userId || $conversation->receiver_id == $userId;

            if ($isParticipant) {
                messageClass::markAsRead($conversationId, $userId);
            }

            // Get conversation participants for admin view

            return response()->json([
                'success' => true,
                'data' => [
                    'conversation_id' => $conversationId,
                    'messages' => $messages,
                    'count' => count($messages),
                    'conversation' => $conversation ? [
                        'sender_id' => $conversation->sender_id,
                        'receiver_id' => $conversation->receiver_id
                    ] : null
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new message and broadcast via Pusher
     *
     * @param MessageRequest $request
     * @return JsonResponse
     */
    public function store(MessageRequest $request): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $validated = $request->validated();

            // SECURITY: Validate message content BEFORE storing
            $validation = messageClass::validateMessageContent($validated['content'] ?? '');

            // Rule A: Block message if contact info detected (Hard Block)
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['error']
                ], 422); // Unprocessable Entity
            }

            // SECURITY: Prevent cross-contamination of admin/user conversations
            $sessionUser = session('user');
            $isAdminSession = $sessionUser && isset($sessionUser->admin_id);

            if (!empty($validated['conversation_id'])) {
                $existingConv = DB::table('conversations')
                    ->where('conversation_id', $validated['conversation_id'])
                    ->first();

                if ($existingConv) {
                    // Check if conversation is suspended - block ALL participants from sending
                    if ($existingConv->status === 'suspended' || $existingConv->is_suspended) {
                        // Check if suspension has expired
                        $isSuspensionActive = true;
                        if ($existingConv->suspended_until) {
                            $suspendedUntil = \Carbon\Carbon::parse($existingConv->suspended_until);
                            if ($suspendedUntil->isPast()) {
                                // Suspension expired - auto-restore
                                DB::table('conversations')
                                    ->where('conversation_id', $validated['conversation_id'])
                                    ->update([
                                        'status' => 'active',
                                        'is_suspended' => 0
                                    ]);
                                $isSuspensionActive = false;
                            }
                        }

                        if ($isSuspensionActive) {
                            return response()->json([
                                'success' => false,
                                'message' => 'This conversation is suspended. No messages can be sent.'
                            ], 403);
                        }
                    }

                    // Admin cannot send to user-to-user conversations
                    if ($isAdminSession && !$existingConv->is_admin_conversation) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Admin cannot send messages in user-to-user conversations'
                        ], 403);
                    }

                    // For admin conversations, only block users whose ID collides with an admin ID
                    // (user_id=5 can reply to admin, but user_id=1 cannot hijack admin's conversations)
                    if (!$isAdminSession && $existingConv->is_admin_conversation) {
                        // Check if this user is actually a participant
                        $isParticipant = $existingConv->sender_id == $userId || $existingConv->receiver_id == $userId;

                        if (!$isParticipant) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Unauthorized access to this conversation'
                            ], 403);
                        }

                        // Check for ID collision
                        $adminNumericIds = DB::table('admin_users')
                            ->pluck('admin_id')
                            ->map(fn($aid) => (int) preg_replace('/[^0-9]/', '', $aid))
                            ->toArray();

                        if (in_array($userId, $adminNumericIds)) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Unauthorized access to this conversation'
                            ], 403);
                        }
                    }
                }
            }

            $data = [
                'sender_id' => $userId,
                'receiver_id' => $validated['receiver_id'],
                'content' => $validated['content'] ?? '',
                'conversation_id' => $validated['conversation_id'] ?? null,
                'attachments' => $request->file('attachments') ?? []
            ];

            $message = messageClass::storeMessage($data);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save message'
                ], 500);
            }

            // When admin sends a message, mark this conversation so it appears in admin "All" tab
            // (needed because admin_id numeric part collides with user_ids in conversations table)
            if ($isAdminSession) {
                DB::table('conversations')
                    ->where('conversation_id', $message->conversation_id)
                    ->update(['is_admin_conversation' => 1]);
            }

            // Broadcast the message via Pusher
            // \Log::info('Broadcasting message event', [
            //     'message_id' => $message->message_id,
            //     'conversation_id' => $message->conversation_id
            // ]);
            broadcast(new messageSentEvent($message));

            // Get conversation to retrieve sender/receiver info
            $conversation = DB::table('conversations')
                ->where('conversation_id', $message->conversation_id)
                ->first();

            // Create notification for receiver
            try {
                $senderDetails = messageClass::getUserDetails($userId, $isAdminSession);
                $senderName = $senderDetails['name'] ?? 'Someone';

                // Truncate message content for notification preview
                $messagePreview = strlen($message->content) > 50
                    ? substr($message->content, 0, 50) . '...'
                    : $message->content;

                Log::info('Creating message notification', [
                    'receiver_id' => $validated['receiver_id'],
                    'sender_id' => $userId,
                    'sender_name' => $senderName,
                    'conversation_id' => $message->conversation_id
                ]);

                $notificationId = NotificationService::create(
                    userId: (int) $validated['receiver_id'],
                    subType: 'message_received',
                    title: 'New Message 💬',
                    message: "{$senderName}: {$messagePreview}",
                    priority: 'normal',
                    referenceType: 'conversation',
                    referenceId: (int) $message->conversation_id,
                    actionData: [
                        'screen' => 'messages',
                        'params' => [
                            'conversationId' => (int) $message->conversation_id
                        ]
                    ]
                );

                Log::info('Message notification created', [
                    'notification_id' => $notificationId,
                    'receiver_id' => $validated['receiver_id']
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create message notification', [
                    'error' => $e->getMessage(),
                    'receiver_id' => $validated['receiver_id'],
                    'conversation_id' => $message->conversation_id
                ]);
                // Don't fail the message send if notification creation fails
            }

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'message_id' => $message->message_id,
                    'conversation_id' => $message->conversation_id,
                    'content' => $message->content,
                    'sender' => messageClass::getUserDetails($userId, $isAdminSession),
                    'receiver' => messageClass::getUserDetails($validated['receiver_id'], $isAdminSession),
                    'attachments' => $message->attachments->map(function ($att) {
                        return [
                            'attachment_id' => $att->attachment_id,
                            'file_name' => $att->file_name,
                            'file_type' => $att->file_type,
                            'file_url' => url('storage/' . $att->file_path),
                            'is_image' => str_starts_with($att->file_type ?? '', 'image/')
                        ];
                    }),
                    'is_read' => (bool) $message->is_read,
                    'is_flagged' => (bool) $message->is_flagged,
                    'flag_reason' => $message->flag_reason,
                    'sent_at' => $message->created_at->toIso8601String()
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard statistics for admin analytics cards
     *
     * @return JsonResponse
     */
    public function getStats(): JsonResponse
    {
        try {
            // Ensure user is authenticated
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $stats = messageClass::getDashboardStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend a conversation
     *
     * @param Request $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function suspend(Request $request, int $conversationId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500'
            ]);

            // Get current suspension count
            $conversation = DB::table('conversations')
                ->where('conversation_id', $conversationId)
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Conversation not found'
                ], 404);
            }

            $currentOffenses = $conversation->no_suspends ?? 0;
            $newOffenseCount = $currentOffenses + 1;

            // Calculate suspension duration based on offense count
            // 1st: 7 days, 2nd: 15 days, 3rd: 30 days, 4th+: permanent
            $suspendedUntil = null;
            $offenseLevel = '';

            switch ($newOffenseCount) {
                case 1:
                    $suspendedUntil = now()->addDays(7)->toDateTimeString();
                    $offenseLevel = '1st offense - 7 days ban';
                    break;
                case 2:
                    $suspendedUntil = now()->addDays(15)->toDateTimeString();
                    $offenseLevel = '2nd offense - 15 days ban';
                    break;
                case 3:
                    $suspendedUntil = now()->addDays(30)->toDateTimeString();
                    $offenseLevel = '3rd offense - 30 days ban';
                    break;
                default: // 4th offense and beyond
                    $suspendedUntil = now()->addYears(100)->toDateTimeString(); // Permanent (100 years)
                    $offenseLevel = '4th offense - Permanent ban';
                    break;
            }

            $fullReason = $offenseLevel . ': ' . $validated['reason'];

            messageClass::suspendConversation(
                $conversationId,
                $fullReason,
                $suspendedUntil,
                $newOffenseCount
            );

            // REAL-TIME: Broadcast to all users including admin (remove toOthers for instant UI update)
            // \Log::info('Broadcasting conversation suspension', [
            //     'conversation_id' => $conversationId,
            //     'reason' => $fullReason,
            //     'offense_count' => $newOffenseCount,
            //     'suspended_until' => $suspendedUntil
            // ]);
            broadcast(new ConversationSuspendedEvent(
                $conversationId,
                'suspended',
                $fullReason,
                $suspendedUntil
            ));

            return response()->json([
                'success' => true,
                'message' => 'Conversation suspended successfully',
                'offense_level' => $offenseLevel,
                'suspended_until' => $suspendedUntil,
                'total_offenses' => $newOffenseCount
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore a suspended conversation
     *
     * @param int $conversationId
     * @return JsonResponse
     */
    public function restore(int $conversationId): JsonResponse
    {
        try {
            messageClass::restoreConversation($conversationId);

            // REAL-TIME: Broadcast to all users including admin (remove toOthers for instant UI update)
            // \Log::info('Broadcasting conversation restoration', [
            //     'conversation_id' => $conversationId
            // ]);
            broadcast(new ConversationSuspendedEvent(
                $conversationId,
                'active'
            ));

            return response()->json([
                'success' => true,
                'message' => 'Conversation restored successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Flag a conversation
     *
     * @param Request $request
     * @param int $conversationId
     * @return JsonResponse
     */
    public function flagConversation(Request $request, int $conversationId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reason' => 'required|string|max:500',
                'notes' => 'nullable|string|max:1000'
            ]);

            messageClass::flagConversation($conversationId, $validated['reason'], $validated['notes'] ?? null);

            return response()->json([
                'success' => true,
                'message' => 'Conversation flagged successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to flag conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unflag a conversation
     *
     * @param int $conversationId
     * @return JsonResponse
     */
    public function unflagConversation(int $conversationId): JsonResponse
    {
        try {
            messageClass::unflagConversation($conversationId);

            return response()->json([
                'success' => true,
                'message' => 'Conversation unflagged successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unflag conversation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search messages by content
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'query' => 'required|string|min:2'
            ]);

            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $query = $validated['query'];

            // Search messages in conversations where user is a participant
            $results = DB::table('messages as m')
                ->join('conversations as c', 'm.conversation_id', '=', 'c.conversation_id')
                ->where(function ($q) use ($userId) {
                    $q->where('c.sender_id', $userId)
                        ->orWhere('c.receiver_id', $userId);
                })
                ->where('m.content', 'LIKE', "%{$query}%")
                ->select('m.*', 'c.sender_id', 'c.receiver_id')
                ->orderBy('m.created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get list of users available for messaging
     *
     * @return JsonResponse
     */
    public function getAvailableUsers(): JsonResponse
    {
        try {
            $currentUserId = $this->getAuthUserId();

            if (!$currentUserId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $users = DB::table('users')
                ->where('user_id', '!=', $currentUserId)
                ->select('user_id', 'email', 'user_type')
                ->get()
                ->map(function ($user) {
                    // These are regular users from users table, not admins
                    return messageClass::getUserDetails($user->user_id, false);
                })
                ->filter(); // Remove nulls

            return response()->json([
                'success' => true,
                'data' => array_values($users->toArray())
            ], 200);

        } catch (\Exception $e) {
            \Log::error('getAvailableUsers - Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * SECURITY: Report a message for inappropriate content
     * Flags the message and stores the user's report reason
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function report(Request $request): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'message_id' => 'required|integer|exists:messages,message_id',
                'reason' => 'required|string|max:500'
            ]);

            $messageId = $request->input('message_id');
            $reason = $request->input('reason');

            // Verify user has access to this message (is part of the conversation)
            $message = messageClass::find($messageId);
            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            $conversation = DB::table('conversations')
                ->where('conversation_id', $message->conversation_id)
                ->where(function ($query) use ($userId) {
                    $query->where('sender_id', $userId)
                        ->orWhere('receiver_id', $userId);
                })
                ->first();

            if (!$conversation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized - You are not part of this conversation'
                ], 403);
            }

            // Flag the message with user report
            DB::table('messages')
                ->where('message_id', $messageId)
                ->update([
                    'is_flagged' => 1,
                    'flag_reason' => 'USER_REPORT: ' . $reason,
                    'updated_at' => now()
                ]);

            // \Log::info('Message reported by user', [
            //     'message_id' => $messageId,
            //     'reported_by' => $userId,
            //     'reason' => $reason
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Message reported successfully. Our team will review it.'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Failed to report message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to report message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN MODERATION: Get flagged conversations
     *
     * @return JsonResponse
     */
    public function getFlaggedConversations(): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $conversations = messageClass::getFlaggedConversations();

            return response()->json([
                'success' => true,
                'data' => $conversations,
                'count' => count($conversations)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flagged conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ADMIN MODERATION: Get suspended conversations
     *
     * @return JsonResponse
     */
    public function getSuspendedConversations(): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $conversations = messageClass::getSuspendedConversations();

            return response()->json([
                'success' => true,
                'data' => $conversations,
                'count' => count($conversations)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch suspended conversations',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
