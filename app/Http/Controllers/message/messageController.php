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
use Illuminate\Support\Facades\Storage;

class messageController extends Controller
{
    use \App\Traits\WithAtomicLock;
    // Get authenticated user ID from Bearer token, session, or X-User-Id header
    private function getAuthUserId(): ?int
    {
        $bearerToken = request()->bearerToken();
        if ($bearerToken) {
            try {
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

        $sessionUser = session('user');
        if ($sessionUser) {
            if (isset($sessionUser->admin_id)) {
                return (int) preg_replace('/[^0-9]/', '', $sessionUser->admin_id);
            }
            return $sessionUser->user_id ?? $sessionUser->id ?? null;
        }

        $headerUserId = request()->header('X-User-Id');
        if ($headerUserId) {
            return (int) $headerUserId;
        }

        return null;
    }

    // Broadcast typing indicator to receiver via Pusher
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

    // Check if current user is an admin from session
    private function isAdmin(int $userId): bool
    {
        $sessionUser = session('user');
        return $sessionUser && isset($sessionUser->admin_id);
    }

    // Mark all messages in a conversation as read for the current user
    public function markRead(int $conversationId): JsonResponse
    {
        try {
            $userId = $this->getAuthUserId();
            if (!$userId) {
                return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
            }

            $conversation = DB::table('conversations')
                ->where('conversation_id', $conversationId)
                ->first();

            if (!$conversation) {
                return response()->json(['success' => false, 'message' => 'Conversation not found'], 404);
            }

            // Admin session: always allowed to mark admin conversations as read
            $sessionUser = session('user');
            $isAdminSession = $sessionUser && isset($sessionUser->admin_id);

            if ($isAdminSession && $conversation->is_admin_conversation) {
                messageClass::markAsReadByAdmin($conversationId);
                return response()->json(['success' => true]);
            }

            $isParticipant = $conversation->sender_id == $userId || $conversation->receiver_id == $userId;

            // Also allow contractor owner to mark messages as read
            if (!$isParticipant && !empty($conversation->contractor_id)) {
                $contractorOwnerId = DB::table('contractors')
                    ->where('contractor_id', $conversation->contractor_id)
                    ->value('owner_id');
                $contractorUserId = $contractorOwnerId
                    ? DB::table('property_owners')->where('owner_id', $contractorOwnerId)->value('user_id')
                    : null;
                $isParticipant = ($contractorUserId == $userId);
            }

            if (!$isParticipant) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            messageClass::markAsRead($conversationId, $userId);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Get inbox for authenticated user with latest message previews
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

            // Check if this is an admin session
            $sessionUser = session('user');
            $isAdminSession = $sessionUser && isset($sessionUser->admin_id);

            if ($isAdminSession) {
                // Admin: use special admin inbox that shows all admin conversations
                $inbox = messageClass::getAdminInbox();
            } else {
                // Resolve contractor context from header
                $contractorId = request()->header('X-Contractor-Id')
                    ? (int) request()->header('X-Contractor-Id')
                    : null;

                // Regular users: use normal inbox filtered by role context
                $inbox = messageClass::getInbox($userId, $contractorId);

                // Exclude admin conversations if there's an ID collision
                $adminNumericIds = DB::table('admin_users')
                    ->pluck('admin_id')
                    ->map(fn($aid) => (int) preg_replace('/[^0-9]/', '', $aid))
                    ->toArray();

                if (in_array($userId, $adminNumericIds)) {
                    $adminConvIds = DB::table('conversations')
                        ->where('is_admin_conversation', 1)
                        ->pluck('conversation_id')
                        ->toArray();

                    $inbox = array_values(array_filter($inbox, function ($conv) use ($adminConvIds) {
                        return !in_array($conv['conversation_id'], $adminConvIds);
                    }));
                }
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

    // Get full conversation history with authorization checks
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

            // Also check if user owns the contractor company for this conversation
            $isContractorOwner = false;
            if (!empty($conversation->contractor_id)) {
                $contractorOwnerId = DB::table('contractors')
                    ->where('contractor_id', $conversation->contractor_id)
                    ->value('owner_id');
                $contractorUserId = $contractorOwnerId
                    ? DB::table('property_owners')->where('owner_id', $contractorOwnerId)->value('user_id')
                    : null;
                $isContractorOwner = ($contractorUserId == $userId);
            }

            if ($isAdminUser) {
                // Admin can view ANY conversation for moderation purposes
            } else {
                // Regular user - must be a direct participant OR the contractor company owner
                if (!$isParticipantInConv && !$isContractorOwner) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Unauthorized access to this conversation'
                    ], 403);
                }

                // If this is an admin conversation, only block if user's ID collides with an admin ID
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

            $messages = messageClass::getConversationHistory($conversationId, null, $userId);

            // Mark as read: admin session marks user messages as read; regular users mark received messages
            if ($isAdminUser && $conversation->is_admin_conversation) {
                messageClass::markAsReadByAdmin($conversationId);
            } elseif ($isParticipantInConv || $isContractorOwner) {
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
                        'receiver_id' => $conversation->receiver_id,
                        'is_admin_conversation' => (bool) $conversation->is_admin_conversation
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

    // Store new message, validate content, broadcast via Pusher
    public function store(messageRequest $request): JsonResponse
    {
        $userId = $this->getAuthUserId();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'User not authenticated'], 401);
        }

        $validated = $request->validated();

        // Lock key scoped to sender + conversation (or receiver for new convos) — prevents double-send
        $convKey = !empty($validated['conversation_id'])
            ? $validated['conversation_id']
            : 'new_' . ($validated['receiver_id'] ?? 'unknown');

        return $this->withLock("msg_send_{$userId}_{$convKey}", function () use ($request, $validated, $userId) {
            try {

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
                'contractor_id' => $validated['contractor_id'] ?? null,
                'attachments' => $request->file('attachments') ?? [],
                'is_admin_sending' => $isAdminSession
            ];

            // For contractor conversations: ensure receiver_id is the contractor owner's user_id
            // so the FK constraint on conversations.receiver_id is satisfied.
            // The sender stays as the external user (the one initiating the chat).
            if (!empty($data['contractor_id'])) {
                $contractorOwnerId = DB::table('contractors')
                    ->where('contractor_id', $data['contractor_id'])
                    ->value('owner_id');
                $contractorOwnerUserId = $contractorOwnerId
                    ? DB::table('property_owners')->where('owner_id', $contractorOwnerId)->value('user_id')
                    : null;

                if ($contractorOwnerUserId) {
                    // If the current user IS the contractor owner (they're replying), swap roles:
                    // sender = owner, receiver = the external user they're replying to
                    if ($userId == $contractorOwnerUserId) {
                        // receiver_id stays as provided (the external user)
                        // sender_id stays as the owner — no change needed
                    } else {
                        // External user messaging the contractor: receiver = owner's user_id
                        $data['receiver_id'] = $contractorOwnerUserId;
                    }
                }
            }

            $message = messageClass::storeMessage($data);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save message'
                ], 500);
            }

            // Get conversation to retrieve sender/receiver info
            $conversation = DB::table('conversations')
                ->where('conversation_id', $message->conversation_id)
                ->first();

            // Broadcast the message via Pusher — wrapped in its own try/catch so a
            // Pusher failure does NOT cause the whole request to return success:false.
            // The message is already saved; broadcasting is best-effort.
            try {
                broadcast(new messageSentEvent($message));

                // For admin conversations with flagged content, also broadcast uncensored version to admin
                if ($message->is_flagged && $conversation && $conversation->is_admin_conversation) {
                    broadcast(new \App\Events\messageSentEventUncensored($message));
                }
            } catch (\Exception $broadcastEx) {
                Log::warning('Broadcast failed (message still saved)', [
                    'message_id' => $message->message_id,
                    'error' => $broadcastEx->getMessage()
                ]);
            }

            // Censor content in the response for non-admin users so the sender
            // immediately sees ### instead of the raw bad word
            $responseContent = $message->content;
            if (!$isAdminSession && $message->is_flagged) {
                $responseContent = messageClass::censorBadWords($message->content);
            }

            $conversation = DB::table('conversations')
                ->where('conversation_id', $message->conversation_id)
                ->first();
            
            $actualSenderId = $userId;
            

            if ($message->from_sender) {
                // Message is from the conversation's original sender
                $actualReceiverId = $conversation->receiver_id;
            } else {
                // Message is from the conversation's original receiver
                $actualReceiverId = $conversation->sender_id;
            }

            \Log::info('Controller response sender/receiver', [
                'userId' => $userId,
                'actualSenderId' => $actualSenderId,
                'actualReceiverId' => $actualReceiverId,
                'message_from_sender' => $message->from_sender,
                'conversation_sender_id' => $conversation->sender_id,
                'conversation_receiver_id' => $conversation->receiver_id,
                'conversation_id' => $message->conversation_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [
                    'message_id' => $message->message_id,
                    'conversation_id' => $message->conversation_id,
                    'content' => $responseContent,
                    'sender' => messageClass::getUserDetails($actualSenderId, $isAdminSession),
                    'receiver' => messageClass::getUserDetails($actualReceiverId, $isAdminSession),
                    'attachments' => $message->attachments->map(function ($att) {
                        return [
                            'attachment_id' => $att->attachment_id,
                            'file_name' => $att->file_name,
                            'file_type' => $att->file_type,
                            'file_url' => Storage::disk('public')->url($att->file_path),
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
        }, 5); // end withLock — 5s TTL prevents double-send
    }

    // Get dashboard statistics for admin analytics cards
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

    // Suspend conversation with escalating duration based on offense count
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

    // Restore suspended conversation and broadcast event
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

    // Flag entire conversation for admin review
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

    // Unflag entire conversation
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

    // Unflag specific message and revert flagged status
    public function unflagMessage(int $messageId): JsonResponse
    {
        try {
            messageClass::unflagMessage($messageId);

            return response()->json([
                'success' => true,
                'message' => 'Message unflagged successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unflag message',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Search messages by content in user's conversations
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

    // Get list of users available for messaging
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

            // Regular users
            $users = DB::table('users')
                ->where('user_id', '!=', $currentUserId)
                ->select('user_id', 'email', 'user_type')
                ->get()
                ->map(function ($user) {
                    return messageClass::getUserDetails($user->user_id, false);
                })
                ->filter();

            // Contractor companies — each appears as a separate messageable entity
            // identified by contractor_id so messages are scoped to the company inbox
            $contractors = DB::table('contractors as c')
                ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                ->join('users as u', 'po.user_id', '=', 'u.user_id')
                ->where('c.verification_status', 'approved')
                ->where('u.user_id', '!=', $currentUserId)
                ->select('c.contractor_id', 'c.company_name', 'c.company_logo', 'u.user_id')
                ->get()
                ->map(function ($c) {
                    $avatar = $c->company_logo
                        ? asset('storage/' . $c->company_logo)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($c->company_name) . '&background=EC7E00&color=fff&bold=true';
                    return [
                        'id' => $c->user_id,          // user_id of the owner (for conversations table FK)
                        'contractor_id' => $c->contractor_id,
                        'name' => $c->company_name,
                        'type' => 'contractor',
                        'avatar' => $avatar,
                        'online' => false,
                    ];
                });

            $all = array_values(array_merge($users->toArray(), $contractors->toArray()));

            return response()->json([
                'success' => true,
                'data' => $all
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

    // Report message for inappropriate content, flag and store reason
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

    // Get flagged conversations for admin moderation
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

    // Get suspended conversations for admin moderation
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
