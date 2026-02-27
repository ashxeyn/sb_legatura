<?php

namespace App\Http\Controllers\message;

use App\Http\Controllers\Controller;
use App\Http\Requests\message\messageRequest;
use App\Models\message\messageClass;
use App\Events\messageSentEvent;
use App\Events\conversationSuspendedEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class messageController extends Controller
{
    /**
     * Get authenticated user ID from either Sanctum (mobile) or session (web)
     */
    private function getAuthUserId(): ?int
    {
        // Try Laravel auth first (Sanctum for API)
        $userId = auth()->id();

        // Fallback to session (admin web dashboard)
        if (!$userId) {
            $sessionUser = session('user');
            // Admin users: admin_id, Regular users: user_id or id
            $userId = $sessionUser->admin_id ?? $sessionUser->user_id ?? $sessionUser->id ?? null;

            // Log::info('getAuthUserId - Using session auth', [
            //     'session_user_exists' => !!$sessionUser,
            //     'resolved_user_id' => $userId
            // ]);
        } else {
            // Log::info('getAuthUserId - Using Sanctum auth', ['user_id' => $userId]);
        }

        return $userId;
    }

    /**
     * Check if current user is an admin
     */
    private function isAdmin(int $userId): bool
    {
        // Check admin_users table
        $isAdminUser = DB::table('admin_users')->where('admin_id', $userId)->exists();
        if ($isAdminUser) return true;

        // Check users table for user_type='admin'
        $user = DB::table('users')->where('user_id', $userId)->first();
        return $user && $user->user_type === 'admin';
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

            // Admins can view any conversation, regular users only their own
            $isAdminUser = $this->isAdmin($userId);

            if (!$isAdminUser) {
                // Verify user is part of this conversation
                $hasAccess = DB::table('conversations')
                    ->where('conversation_id', $conversationId)
                    ->where(function ($query) use ($userId) {
                        $query->where('sender_id', $userId)
                              ->orWhere('receiver_id', $userId);
                    })
                    ->exists();

                if (!$hasAccess) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this conversation'
                    ], 403);
                }
            }

            $messages = messageClass::getConversationHistory($conversationId);

            // Get conversation participants
            $conversation = DB::table('conversations')
                ->where('conversation_id', $conversationId)
                ->first();

            // Mark messages as read if user is a participant (sender or receiver)
            // This includes admins who are direct participants in the conversation
            $isParticipant = $conversation && (
                $conversation->sender_id == $userId ||
                $conversation->receiver_id == $userId
            );

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

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'message_id' => $message->message_id,
                    'conversation_id' => $message->conversation_id,
                    'content' => $message->content,
                    'sender' => messageClass::getUserDetails($userId),
                    'receiver' => messageClass::getUserDetails($validated['receiver_id']),
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
                    return messageClass::getUserDetails($user->user_id);
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
