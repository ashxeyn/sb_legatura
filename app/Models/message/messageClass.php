<?php

namespace App\Models\message;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\messageSentEvent;
use App\Models\user;

class messageClass extends Model
{
    use HasFactory;

    protected $table = 'messages';
    protected $primaryKey = 'message_id';
    public $timestamps = true;

    protected $fillable = [
        'conversation_id',
        'from_sender',
        'content',
        'is_read',
        'is_flagged',
        'flag_reason'
    ];

    // Get sender ID from conversation based on from_sender flag
    public function getSenderIdAttribute(): int
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $this->conversation_id)
            ->first();

        return $this->from_sender ? $conversation->sender_id : $conversation->receiver_id;
    }

    // Get attachments for this message from database
    public function getAttachmentsAttribute()
    {
        return DB::table('message_attachments')
            ->where('message_id', $this->message_id)
            ->get()
            ->map(function ($att) {
                return (object) [
                    'attachment_id' => $att->attachment_id,
                    'message_id' => $att->message_id,
                    'file_path' => $att->file_path,
                    'file_name' => $att->file_name,
                    'file_type' => $att->file_type,
                    'url' => url('storage/' . $att->file_path)
                ];
            });
    }

    // Store file attachment for message in storage and database
    public function addAttachment($file): ?object
    {
        try {
            $path = $file->store('messages', 'public');

            $attachmentId = DB::table('message_attachments')->insertGetId([
                'message_id' => $this->message_id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType()
            ]);

            return (object) [
                'attachment_id' => $attachmentId,
                'message_id' => $this->message_id,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'file_type' => $file->getMimeType(),
                'url' => url('storage/' . $path)
            ];
        } catch (\Exception $e) {
            \Log::error('Failed to add attachment', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Delete attachment file from storage and database
    public static function deleteAttachment(int $attachmentId): bool
    {
        try {
            $attachment = DB::table('message_attachments')
                ->where('attachment_id', $attachmentId)
                ->first();

            if ($attachment) {
                // Delete file from storage
                Storage::disk('public')->delete($attachment->file_path);

                // Delete from database
                DB::table('message_attachments')
                    ->where('attachment_id', $attachmentId)
                    ->delete();

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error('Failed to delete attachment', ['error' => $e->getMessage()]);
            return false;
        }
    }

    // Get dashboard statistics for admin analytics cards
    public static function getDashboardStats(): array
    {
        // Total suspended conversations (exclude admin conversations)
        $totalSuspended = DB::table('conversations')
            ->where(function ($query) {
                $query->where('status', 'suspended')
                    ->orWhere('is_suspended', 1);
            })
            ->where('is_admin_conversation', 0) // Exclude admin-initiated conversations
            ->count();

        // Active conversations (last 7 days, exclude admin conversations)
        $activeConversations = DB::table('conversations as c')
            ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
            ->where('m.created_at', '>=', Carbon::now()->subDays(7))
            ->where('c.status', '!=', 'suspended')
            ->where('c.is_admin_conversation', 0) // Exclude admin-initiated conversations
            ->distinct()
            ->count('c.conversation_id');

        // Flagged conversations count (unique conversations with flagged messages, exclude admin conversations)
        $flaggedMessages = DB::table('messages as m')
            ->join('conversations as c', 'm.conversation_id', '=', 'c.conversation_id')
            ->where('m.is_flagged', 1)
            ->where('c.is_admin_conversation', 0) // Exclude admin-initiated conversations
            ->distinct()
            ->count('c.conversation_id');

        return [
            'totalSuspended' => $totalSuspended ?? 0,
            'activeConversations' => $activeConversations ?? 0,
            'flaggedMessages' => $flaggedMessages ?? 0
        ];
    }

    // Resolve contractor_id for a user (null if user has no contractor profile)
    public static function getContractorIdForUser(int $userId): ?int
    {
        $ownerId = DB::table('property_owners')->where('user_id', $userId)->value('owner_id');
        if (!$ownerId) return null;
        return DB::table('contractors')->where('owner_id', $ownerId)->value('contractor_id');
    }

    // Get inbox/conversation list for user with latest message preview
    // Pass $contractorId to get contractor-role inbox, null for personal/owner inbox
    public static function getInbox(int $userId, ?int $contractorId = null): array
    {
        $query = DB::table('conversations as c')
            ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
            ->select(
                'c.conversation_id',
                'c.sender_id',
                'c.receiver_id',
                'c.status',
                'c.is_suspended',
                'c.suspended_until',
                'c.reason',
                'c.is_admin_conversation',
                'c.contractor_id',
                'm.content as last_content',
                'm.is_flagged as last_is_flagged',
                'm.created_at as last_sent_at'
            )
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)');

        if ($contractorId !== null) {
            // Contractor role: only conversations tagged with this contractor_id
            $query->where('c.contractor_id', $contractorId);
        } else {
            // Personal/owner role: conversations where user is a participant
            // Include contractor conversations where this user is a direct participant as the EXTERNAL user
            // (they initiated the chat with a contractor company)
            // Exclude contractor conversations where this user IS the contractor owner
            // (those belong in the contractor inbox, not the personal inbox)

            // First, find all contractor_ids owned by this user so we can exclude them
            $ownedContractorIds = DB::table('contractors as c')
                ->join('property_owners as po', 'c.owner_id', '=', 'po.owner_id')
                ->where('po.user_id', $userId)
                ->pluck('c.contractor_id')
                ->toArray();

            $query->where(function ($q) use ($userId, $ownedContractorIds) {
                $q->where(function ($inner) use ($userId) {
                    // Regular conversations (no contractor_id) where user is a participant
                    $inner->whereNull('c.contractor_id')
                        ->where(function ($p) use ($userId) {
                            $p->where('c.sender_id', $userId)
                                ->orWhere('c.receiver_id', $userId);
                        });
                })->orWhere(function ($inner) use ($userId, $ownedContractorIds) {
                    // Contractor conversations where this user is a participant
                    // but NOT as the contractor owner (those go in contractor inbox)
                    $inner->whereNotNull('c.contractor_id')
                        ->where(function ($p) use ($userId) {
                            $p->where('c.sender_id', $userId)
                                ->orWhere('c.receiver_id', $userId);
                        });
                    // Exclude conversations belonging to contractor companies this user owns
                    if (!empty($ownedContractorIds)) {
                        $inner->whereNotIn('c.contractor_id', $ownedContractorIds);
                    }
                });
            });
        }

        $conversations = $query->orderBy('m.created_at', 'desc')->get();

        $result = [];
        foreach ($conversations as $conv) {
            $isAdminConv = (bool) ($conv->is_admin_conversation ?? false);
            $convContractorId = $conv->contractor_id ?? null;

            if ($isAdminConv) {
                $otherUser = self::getAdminDetails();
            } elseif ($convContractorId !== null) {
                // Contractor-role conversation: other user is whoever is NOT the contractor company
                // sender_id/receiver_id hold the user_id of the external party
                // We need to find which side is the contractor owner and which is the external user
                $contractorOwnerId = DB::table('contractors')
                    ->where('contractor_id', $convContractorId)
                    ->value('owner_id');
                $contractorUserId = $contractorOwnerId
                    ? DB::table('property_owners')->where('owner_id', $contractorOwnerId)->value('user_id')
                    : null;

                // The external party is the one who is NOT the contractor's user_id
                $otherUserId = ($conv->sender_id == $contractorUserId)
                    ? $conv->receiver_id
                    : $conv->sender_id;

                // For contractor inbox: show the external user's personal profile
                // For owner/sender inbox: show the contractor company details
                if ($contractorId !== null) {
                    // Viewing as contractor — show the external user who messaged the company
                    $otherUser = self::getUserDetails($otherUserId, false);
                } else {
                    // Viewing as owner/sender — show the contractor company as the contact
                    $contractor = DB::table('contractors')->where('contractor_id', $convContractorId)->first();
                    if ($contractor) {
                        $companyAvatar = $contractor->company_logo
                            ? asset('storage/' . $contractor->company_logo)
                            : 'https://ui-avatars.com/api/?name=' . urlencode($contractor->company_name) . '&background=EC7E00&color=fff&bold=true';
                        $otherUser = [
                            'id' => $otherUserId,
                            'name' => $contractor->company_name,
                            'type' => 'contractor',
                            'avatar' => $companyAvatar,
                            'online' => false,
                            'contractor_id' => $convContractorId,
                        ];
                    } else {
                        $otherUser = self::getUserDetails($otherUserId, false);
                    }
                }
            } else {
                $otherUserId = ($conv->sender_id == $userId) ? $conv->receiver_id : $conv->sender_id;

                // Fallback: check if the other user is actually an admin (legacy rows without flag)
                $adminUser = DB::table('admin_users')->where('admin_id', 'ADMIN-' . $otherUserId)->first();
                if ($adminUser) {
                    $isAdminConv = true;
                    $otherUser = self::getAdminDetails();
                } else {
                    $otherUser = self::getUserDetails($otherUserId, false);
                }
            }

            if (!$otherUser)
                continue;

            // Calculate unread count
            if ($convContractorId !== null) {
                // Contractor inbox: unread = messages sent by the external user (from_sender=true)
                // that the contractor hasn't read yet
                $unreadCount = DB::table('messages')
                    ->where('conversation_id', $conv->conversation_id)
                    ->where('from_sender', true)
                    ->where('is_read', 0)
                    ->count();
            } else {
                // Personal inbox: only count messages sent TO this user
                $isSender = ($userId == $conv->sender_id);
                $unreadCount = DB::table('messages')
                    ->where('conversation_id', $conv->conversation_id)
                    ->where('from_sender', !$isSender)
                    ->where('is_read', 0)
                    ->count();
            }

            // Check if conversation has any flagged messages
            $isFlagged = DB::table('messages')
                ->where('conversation_id', $conv->conversation_id)
                ->where('is_flagged', 1)
                ->exists();

            // Censor last message preview for non-admin users if it was flagged
            $lastContent = $conv->last_content;
            if ($conv->last_is_flagged) {
                $lastContent = self::censorBadWords($lastContent);
            }

            $result[] = [
                'conversation_id' => $conv->conversation_id,
                'other_user' => $otherUser,
                'last_message' => [
                    'content' => $lastContent,
                    'sent_at' => Carbon::parse($conv->last_sent_at)->diffForHumans(),
                    'sent_at_timestamp' => Carbon::parse($conv->last_sent_at, 'UTC')->toIso8601String()
                ],
                'unread_count' => $unreadCount,
                'is_flagged' => $isFlagged,
                'status' => $conv->status,
                'is_suspended' => (bool) $conv->is_suspended,
                'suspended_until' => $conv->suspended_until,
                'reason' => $conv->reason,
                'contractor_id' => $convContractorId,
            ];
        }

        return $result;
    }

    // Get admin inbox showing all admin conversations
    public static function getAdminInbox(): array
    {
        $conversations = DB::table('conversations as c')
            ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
            ->select(
                'c.conversation_id',
                'c.sender_id',
                'c.receiver_id',
                'c.status',
                'c.is_suspended',
                'c.suspended_until',
                'c.reason',
                'c.is_admin_conversation',
                'c.contractor_id',
                'm.content as last_content',
                'm.created_at as last_sent_at',
                'm.from_sender'
            )
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)')
            ->where('c.is_admin_conversation', 1)
            ->orderBy('m.created_at', 'desc')
            ->get();

        $result = [];
        foreach ($conversations as $conv) {
            // For admin conversations, sender_id and receiver_id are both the user's ID
            $userId = $conv->sender_id;
            $convContractorId = $conv->contractor_id ?? null;

            // If this admin conversation is with a contractor company, show company details
            if ($convContractorId !== null) {
                $contractor = DB::table('contractors')->where('contractor_id', $convContractorId)->first();
                if ($contractor) {
                    $companyAvatar = $contractor->company_logo
                        ? asset('storage/' . $contractor->company_logo)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($contractor->company_name) . '&background=EC7E00&color=fff&bold=true';
                    $otherUser = [
                        'id' => $userId,
                        'name' => $contractor->company_name,
                        'type' => 'contractor',
                        'avatar' => $companyAvatar,
                        'online' => false,
                        'contractor_id' => $convContractorId,
                    ];
                } else {
                    $otherUser = self::getUserDetails($userId, false);
                }
            } else {
                $otherUser = self::getUserDetails($userId, false); // Get user details (not admin)
            }

            if (!$otherUser)
                continue;

            // Calculate unread count: count messages from user (from_sender=true)
            $unreadCount = DB::table('messages')
                ->where('conversation_id', $conv->conversation_id)
                ->where('from_sender', true) // Messages from user to admin
                ->where('is_read', 0)
                ->count();

            // Check if conversation has any flagged messages
            $isFlagged = DB::table('messages')
                ->where('conversation_id', $conv->conversation_id)
                ->where('is_flagged', 1)
                ->exists();

            $result[] = [
                'conversation_id' => $conv->conversation_id,
                'other_user' => $otherUser,
                'last_message' => [
                    'content' => $conv->last_content,
                    'sent_at' => Carbon::parse($conv->last_sent_at)->diffForHumans(),
                    'sent_at_timestamp' => Carbon::parse($conv->last_sent_at, 'UTC')->toIso8601String()
                ],
                'unread_count' => $unreadCount,
                'is_flagged' => $isFlagged,
                'status' => $conv->status,
                'is_suspended' => (bool) $conv->is_suspended,
                'suspended_until' => $conv->suspended_until,
                'reason' => $conv->reason,
                'contractor_id' => $convContractorId,
            ];
        }

        return $result;
    }

    // Store new message with attachments, validate content, auto-flag if suspicious
    public static function storeMessage(array $data): ?messageClass
    {
        try {
            DB::beginTransaction();

            // DEBUG: Log incoming data
            \Log::info('storeMessage called', [
                'sender_id' => $data['sender_id'] ?? null,
                'receiver_id' => $data['receiver_id'] ?? null,
                'is_admin_sending' => $data['is_admin_sending'] ?? false,
                'conversation_id' => $data['conversation_id'] ?? null
            ]);

            // SECURITY: Validate message content BEFORE saving
            $validation = self::validateMessageContent($data['content'] ?? '');

            // Note: Hard blocking is handled in controller
            // This check is redundant but kept for API safety
            if (!$validation['valid']) {
                DB::rollBack();
                \Log::warning('Message blocked: contact info detected', ['content' => $data['content']]);
                return null;
            }

            // ADMIN CONVERSATION FIX: When admin sends message, use user ID for both sender and receiver
            // The conversations table has FK constraints on sender_id and receiver_id that reference users.user_id
            // Admin IDs don't exist in users table (or might collide with real user IDs)
            // So for admin conversations, we use the user's ID for BOTH sender_id and receiver_id
            // and track admin status via is_admin_conversation flag + from_sender boolean
            $isAdminSending = $data['is_admin_sending'] ?? false;
            $conversationSenderId = $data['sender_id'];
            $conversationReceiverId = $data['receiver_id'];
            $messageFromSender = true; // Will be recalculated after conversation creation

            // Also detect when a regular user is sending TO an admin
            // (receiver_id is an admin's numeric ID)
            $receiverIsAdmin = false;
            if (!$isAdminSending) {
                $receiverAdminRecord = DB::table('admin_users')
                    ->where('admin_id', 'ADMIN-' . $data['receiver_id'])
                    ->first();
                if ($receiverAdminRecord) {
                    $receiverIsAdmin = true;
                }
            }

            \Log::info('Before swap', [
                'isAdminSending' => $isAdminSending,
                'receiverIsAdmin' => $receiverIsAdmin,
                'conversationSenderId' => $conversationSenderId,
                'conversationReceiverId' => $conversationReceiverId
            ]);

            if ($isAdminSending) {
                // Admin sending: use receiver's (user's) ID for both sides
                $conversationSenderId = $data['receiver_id'];
                $conversationReceiverId = $data['receiver_id'];
                \Log::info('After swap (admin sending)', [
                    'conversationSenderId' => $conversationSenderId,
                    'conversationReceiverId' => $conversationReceiverId
                ]);
            } elseif ($receiverIsAdmin) {
                // User sending to admin: use sender's (user's) ID for both sides
                // so the FK constraint is satisfied (admin IDs don't exist in users table)
                $conversationSenderId = $data['sender_id'];
                $conversationReceiverId = $data['sender_id'];
                \Log::info('After swap (user sending to admin)', [
                    'conversationSenderId' => $conversationSenderId,
                    'conversationReceiverId' => $conversationReceiverId
                ]);
            }

            // Generate conversation ID if not provided (combine user IDs as integer)
            if (!isset($data['conversation_id'])) {
                $contractorId = $data['contractor_id'] ?? null;
                if ($contractorId) {
                    // Contractor conversation: unique per contractor + external user pair.
                    $cOwnerId = DB::table('contractors')->where('contractor_id', $contractorId)->value('owner_id');
                    $cOwnerUserId = $cOwnerId
                        ? DB::table('property_owners')->where('owner_id', $cOwnerId)->value('user_id')
                        : null;
                    $externalUserId = ($data['sender_id'] == $cOwnerUserId)
                        ? $data['receiver_id']
                        : $data['sender_id'];
                    $data['conversation_id'] = ($contractorId * 1000000) + $externalUserId;
                } elseif ($isAdminSending || $receiverIsAdmin) {
                    // Admin conversation: use the user's ID (not admin's numeric ID) for both sides
                    $userSideId = $isAdminSending ? $data['receiver_id'] : $data['sender_id'];
                    $data['conversation_id'] = ($userSideId * 1000000) + $userSideId;
                } else {
                    $minId = min($conversationSenderId, $conversationReceiverId);
                    $maxId = max($conversationSenderId, $conversationReceiverId);
                    $data['conversation_id'] = ($minId * 1000000) + $maxId;
                }
            }

            // Get or create conversation
            $conversation = self::getOrCreateConversation(
                $data['conversation_id'],
                $conversationSenderId,
                $conversationReceiverId,
                $data['contractor_id'] ?? null
            );

            // If contractor_id is provided and the existing conversation doesn't have it set yet, update it
            if (!empty($data['contractor_id']) && empty($conversation->contractor_id)) {
                DB::table('conversations')
                    ->where('conversation_id', $data['conversation_id'])
                    ->update(['contractor_id' => $data['contractor_id']]);
                // Refresh
                $conversation = DB::table('conversations')->where('conversation_id', $data['conversation_id'])->first();
            }

            // Determine if message is from sender or receiver
            if ($isAdminSending) {
                $messageFromSender = false; // admin sent → from_sender=false
            } elseif ($receiverIsAdmin) {
                $messageFromSender = true;  // user sent to admin → from_sender=true
            } elseif (!empty($data['contractor_id'])) {
                // from_sender=true  → external user sent it (incoming to contractor)
                // from_sender=false → contractor owner replied
                $cOwnerId2 = DB::table('contractors')->where('contractor_id', $data['contractor_id'])->value('owner_id');
                $cOwnerUserId2 = $cOwnerId2
                    ? DB::table('property_owners')->where('owner_id', $cOwnerId2)->value('user_id')
                    : null;
                $messageFromSender = ($data['sender_id'] != $cOwnerUserId2);
            } else {
                $messageFromSender = ($data['sender_id'] == $conversation->sender_id);
            }

            // Rule B: Prepare message data with auto-flag if suspicious keywords detected
            $messageData = [
                'conversation_id' => $data['conversation_id'],
                'from_sender' => $messageFromSender,
                'content' => $data['content'],
                'is_read' => 0
            ];

            // Apply automated flagging if validation detected keywords
            if ($validation['flagged']) {
                $messageData['is_flagged'] = 1;
                $messageData['flag_reason'] = $validation['reason'];
            }

            // Create message
            $message = self::create($messageData);

            // Mark conversation as admin conversation if admin is involved
            if ($isAdminSending || $receiverIsAdmin) {
                DB::table('conversations')
                    ->where('conversation_id', $data['conversation_id'])
                    ->update(['is_admin_conversation' => 1]);
            }

            // Handle attachments
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $message->addAttachment($file);
                }
            }

            DB::commit();

            // Reload message
            return self::find($message->message_id);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to store message', ['error' => $e->getMessage()]);
            return null;
        }
    }

    // Get admin details for display in messages
    public static function getAdminDetails(): array
    {
        // Get the first active admin (you can modify this to get a specific admin)
        $admin = DB::table('admin_users')
            ->where('is_active', 1)
            ->first();

        if ($admin) {
            $fullName = trim(($admin->first_name ?? '') . ' ' . ($admin->middle_name ?? '') . ' ' . ($admin->last_name ?? ''));
            $name = !empty($fullName) ? $fullName : ($admin->username ?? 'Admin');
            
            // Extract numeric ID from admin_id (e.g., 'ADMIN-1' -> 1)
            $numericId = (int) preg_replace('/[^0-9]/', '', $admin->admin_id);
            
            // Get avatar from profile_pic or use UI Avatars fallback
            $avatar = null;
            if (!empty($admin->profile_pic)) {
                $avatar = asset('storage/' . $admin->profile_pic);
            } else {
                $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=dc2626&color=fff&bold=true';
            }
            
            return [
                'id' => $numericId,
                'name' => $name,
                'type' => 'Admin',
                'avatar' => $avatar,
                'online' => false // Will be updated in real-time by frontend presence channel
            ];
        }

        // Fallback if no admin found
        return [
            'id' => 1,
            'name' => 'Admin',
            'type' => 'Admin',
            'avatar' => 'https://ui-avatars.com/api/?name=Admin&background=dc2626&color=fff&bold=true',
            'online' => false
        ];
    }

    // Get conversation history with sender details and censoring for non-admins
    public static function getConversationHistory(int|string $conversationId, ?int $limit = null, ?int $viewerUserId = null): array
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation)
            return [];

        $isAdminConv = (bool) ($conversation->is_admin_conversation ?? false);

        $query = self::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc');

        if ($limit !== null) {
            $query->limit($limit);
        }

        $messages = $query->get();

        $result = [];
        foreach ($messages as $msg) {
            // Determine actual sender based on from_sender boolean and conversation type
            if ($isAdminConv) {
                // For admin conversations: from_sender=true means user sent it, false means admin sent it
                // Both sender_id and receiver_id are the user's ID
                if ($msg->from_sender) {
                    // User sent the message
                    $senderId = $conversation->sender_id;
                    $sender = self::getUserDetails($senderId, false); // Get user details
                } else {
                    // Admin sent the message - get actual admin details
                    $sender = self::getAdminDetails();
                }
            } else {
                // Regular or contractor conversation: use normal logic
                $senderId = $msg->from_sender ? $conversation->sender_id : $conversation->receiver_id;

                // For contractor conversations, the contractor owner's messages should show
                // the company name/logo, not their personal profile
                $convContractorId = $conversation->contractor_id ?? null;
                if ($convContractorId !== null) {
                    // Determine which side is the contractor owner
                    $cOwnerId = DB::table('contractors')->where('contractor_id', $convContractorId)->value('owner_id');
                    $cOwnerUserId = $cOwnerId
                        ? DB::table('property_owners')->where('owner_id', $cOwnerId)->value('user_id')
                        : null;

                    if ($senderId == $cOwnerUserId) {
                        // This message is from the contractor owner — show company details
                        $contractor = DB::table('contractors')->where('contractor_id', $convContractorId)->first();
                        if ($contractor) {
                            $companyAvatar = $contractor->company_logo
                                ? asset('storage/' . $contractor->company_logo)
                                : 'https://ui-avatars.com/api/?name=' . urlencode($contractor->company_name) . '&background=EC7E00&color=fff&bold=true';
                            $sender = [
                                'id' => $senderId,
                                'name' => $contractor->company_name,
                                'type' => 'contractor',
                                'avatar' => $companyAvatar,
                                'online' => false,
                                'contractor_id' => $convContractorId,
                            ];
                        } else {
                            $sender = self::getUserDetails($senderId, false);
                        }
                    } else {
                        $sender = self::getUserDetails($senderId, false);
                    }
                } else {
                    $sender = self::getUserDetails($senderId, false);
                }
            }

            // Determine if viewer is admin
            $isViewerAdmin = $viewerUserId ? self::isAdminUser($viewerUserId) : false;
            
            // Censor content for non-admin users
            $messageContent = $msg->content;
            if (!$isViewerAdmin && $msg->is_flagged) {
                $messageContent = self::censorBadWords($msg->content);
            }

            $result[] = [
                'message_id' => $msg->message_id,
                'conversation_id' => $msg->conversation_id,
                'content' => $messageContent,
                'sender' => $sender,
                'is_read' => (bool) $msg->is_read,
                'is_flagged' => (bool) $msg->is_flagged,
                'flag_reason' => $msg->flag_reason,
                'sent_at_human' => Carbon::parse($msg->created_at)->diffForHumans(),
                'sent_at' => $msg->created_at->toIso8601String(),
                'attachments' => $msg->attachments->map(function ($att) {
                    return [
                        'attachment_id' => $att->attachment_id,
                        'file_name' => $att->file_name,
                        'file_type' => $att->file_type,
                        'file_url' => $att->url,
                        'is_image' => str_starts_with($att->file_type, 'image/')
                    ];
                })->toArray()
            ];
        }

        return $result;
    }

    // Get user details with polymorphic lookup for profile info
    /**
     * Get user details for display
     *
     * @param int $userId The numeric user/admin ID
     * @param bool|null $isAdminConversation Context hint: true=admin conversation (ID is admin),
     *                                        false=user conversation (ID is user), null=auto-detect (legacy)
     */
    public static function getUserDetails(int $userId, ?bool $isAdminConversation = null): ?array
    {
        // If explicitly told this is an admin conversation, look up admin first
        // If explicitly told this is NOT an admin conversation, skip admin lookup
        // If null (auto-detect), use legacy behavior (check admin first) - but this can cause ID collision issues

        if ($isAdminConversation === true) {
            // This is an admin conversation - the ID refers to admin_users
            $admin = DB::table('admin_users')->where('admin_id', 'ADMIN-' . $userId)->first();
            if ($admin) {
                $name = $admin->username ?? $admin->email ?? 'Admin';
                $fullName = trim(($admin->first_name ?? '') . ' ' . ($admin->middle_name ?? '') . ' ' . ($admin->last_name ?? ''));
                if (!empty($fullName)) {
                    $name = $fullName;
                }
                
                // Get avatar from profile_pic or use UI Avatars fallback
                $avatar = null;
                if (!empty($admin->profile_pic)) {
                    $avatar = asset('storage/' . $admin->profile_pic);
                } else {
                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=dc2626&color=fff&bold=true';
                }
                
                return [
                    'id' => $userId,
                    'name' => $name,
                    'type' => 'Admin',
                    'avatar' => $avatar,
                    'online' => false
                ];
            }
        }

        if ($isAdminConversation === false) {
            // This is a user conversation - skip admin lookup entirely
            // Fall through to user lookup below
        }

        if ($isAdminConversation === null) {
            // Legacy auto-detect behavior (can cause ID collision - use sparingly)
            $admin = DB::table('admin_users')->where('admin_id', 'ADMIN-' . $userId)->first();
            if ($admin) {
                $name = $admin->username ?? $admin->email ?? 'Admin';
                $fullName = trim(($admin->first_name ?? '') . ' ' . ($admin->middle_name ?? '') . ' ' . ($admin->last_name ?? ''));
                if (!empty($fullName)) {
                    $name = $fullName;
                }
                
                // Get avatar from profile_pic or use UI Avatars fallback
                $avatar = null;
                if (!empty($admin->profile_pic)) {
                    $avatar = asset('storage/' . $admin->profile_pic);
                } else {
                    $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=dc2626&color=fff&bold=true';
                }
                
                return [
                    'id' => $userId,
                    'name' => $name,
                    'type' => 'Admin',
                    'avatar' => $avatar,
                    'online' => false
                ];
            }
        }

        // Get base user from users table
        $user = DB::table('users')->where('user_id', $userId)->first();

        if (!$user)
            return null;

        $name = $user->username ?? $user->email;
        $type = $user->user_type ?? 'user';
        $profilePic = null;
        $avatar = null;

        // Polymorphic lookup for profile details
        if ($type === 'admin') {
            // User type is admin, try to get from admin_users by user_id
            $profile = DB::table('admin_users')->where('user_id', $userId)->first();
            if ($profile) {
                $fullName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                $name = !empty($fullName) ? ($fullName) : ($profile->username ?? $name);
                $profilePic = $profile->profile_pic ?? null;
            }
        } elseif ($type === 'both' || $type === 'owner_staff') {
            // For both (company owner) and owner_staff, get details from property_owners table
            $propertyOwner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($propertyOwner) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''));
                $name = !empty($fullName) ? $fullName : $name;
                $profilePic = $propertyOwner->profile_pic ?? null;
            }
        } elseif ($type === 'property_owner') {
            // For property owners, get profile from property_owners table
            $propertyOwner = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($propertyOwner) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->middle_name ?? '') . ' ' . ($user->last_name ?? ''));
                $name = !empty($fullName) ? $fullName : $name;
                $profilePic = $propertyOwner->profile_pic ?? null;
            }
        }

        // Set avatar URL
        if (!empty($profilePic)) {
            $avatar = asset('storage/' . $profilePic);
        } else {
            // Use UI Avatars as fallback
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6366f1&color=fff&bold=true';
        }

        // Format user type for display
        $displayType = match($type) {
            'property_owner' => 'Property Owner',
            'both' => 'Company Owner',
            'owner_staff' => 'Company Staff',
            'admin' => 'Admin',
            default => ucfirst($type)
        };

        return [
            'id' => $userId,
            'name' => $name,
            'type' => $displayType,
            'avatar' => $avatar,
            'online' => false // Will be updated in real-time by frontend presence channel
        ];
    }

    // Mark messages as read for user in conversation
    public static function markAsRead(int|string $conversationId, int $userId): void
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) {
            return;
        }

        // For contractor conversations, the contractor owner reads messages sent by the external user
        // (from_sender = true means the external user sent it)
        if (!empty($conversation->contractor_id)) {
            $contractorOwnerId = DB::table('contractors')
                ->where('contractor_id', $conversation->contractor_id)
                ->value('owner_id');
            $contractorUserId = $contractorOwnerId
                ? DB::table('property_owners')->where('owner_id', $contractorOwnerId)->value('user_id')
                : null;

            if ($contractorUserId == $userId) {
                // Contractor owner is reading — mark external user's messages (from_sender=true) as read
                $affectedRows = self::where('conversation_id', $conversationId)
                    ->where('from_sender', true)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);
            } else {
                // External user is reading — mark contractor's replies (from_sender=false) as read
                $affectedRows = self::where('conversation_id', $conversationId)
                    ->where('from_sender', false)
                    ->where('is_read', 0)
                    ->update(['is_read' => 1]);
            }
        } else {
            // Regular conversation: determine role by sender_id
            $isSender = ($userId == $conversation->sender_id);
            $affectedRows = self::where('conversation_id', $conversationId)
                ->where('from_sender', !$isSender)
                ->where('is_read', 0)
                ->update(['is_read' => 1]);
        }

        if (!empty($affectedRows) && $affectedRows > 0) {
            broadcast(new \App\Events\messagesReadEvent($conversationId, $userId));
        }
    }

    // Flag all messages in conversation for admin review
    public static function flagConversation(int|string $conversationId, string $reason, ?string $notes = null): void
    {
        DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->update([
                'is_flagged' => 1,
                'flag_reason' => $reason
            ]);
    }

    // Unflag all messages in conversation
    public static function unflagConversation(int|string $conversationId): void
    {
        DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->update([
                'is_flagged' => 0,
                'flag_reason' => null
            ]);
    }

    // Unflag specific message and revert flagged status
    public static function unflagMessage(int $messageId): void
    {
        DB::table('messages')
            ->where('message_id', $messageId)
            ->update([
                'is_flagged' => 0,
                'flag_reason' => null
            ]);
    }

    // Suspend conversation with escalating duration based on offense count
    public static function suspendConversation(int|string $conversationId, ?string $reason = null, ?string $suspendedUntil = null, ?int $offenseCount = null): void
    {
        $updateData = [
            'status' => 'suspended',
            'is_suspended' => 1,
            'reason' => $reason,
            'suspended_until' => $suspendedUntil
        ];

        // Increment suspension count if provided
        if ($offenseCount !== null) {
            $updateData['no_suspends'] = $offenseCount;
        }

        DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->update($updateData);

        // Unflag all messages when suspending (conversation moves from flagged to suspended)
        self::unflagConversation($conversationId);
    }

    // Restore suspended conversation to active status
    public static function restoreConversation(int|string $conversationId): void
    {
        DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->update([
                'status' => 'active',
                'is_suspended' => 0,
                'reason' => null,
                'suspended_until' => null
            ]);
    }

    // Check suspension status and auto-restore if expiry date passed
    public static function checkSuspensionStatus(int|string $conversationId): bool
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) {
            return false;
        }

        // If suspended and has expiry date
        if ($conversation->is_suspended && $conversation->suspended_until) {
            // Check if suspension has expired
            if (now()->greaterThan($conversation->suspended_until)) {
                // Auto-restore
                self::restoreConversation($conversationId);
                return false; // No longer suspended
            }
            return true; // Still suspended
        }

        // If suspended without expiry date
        if ($conversation->is_suspended) {
            return true;
        }

        return false;
    }

    // Get or create conversation record in database
    public static function getOrCreateConversation(int|string $conversationId, int $senderId, int $receiverId, ?int $contractorId = null): object
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) {
            $insert = [
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ];
            if ($contractorId !== null) {
                $insert['contractor_id'] = $contractorId;
            }
            DB::table('conversations')->insert($insert);

            $conversation = DB::table('conversations')
                ->where('conversation_id', $conversationId)
                ->first();
        }

        return $conversation;
    }

    // Detect contact information (emails and Philippine phone numbers) including spaced-out evasion
    private static function detectContactInfo(string $content): bool
    {
        // ── 1. Check the ORIGINAL content first ──────────────────────────

        // Email pattern
        $emailPattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';

        // Philippine phone patterns:
        $phonePatterns = [
            '/\+63\s*\d{10}/',                          // +63 9XX XXX XXXX
            '/\b09\d{9}\b/',                             // 09XXXXXXXXX
            '/\(0?2\)\s*\d{3,4}[\s-]?\d{4}/',           // (02) XXX-XXXX
            '/\b0\d{2,3}[\s-]?\d{3,4}[\s-]?\d{4}\b/'   // 0XX XXX XXXX
        ];

        if (preg_match($emailPattern, $content)) {
            return true;
        }
        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        // ── 2. Strip spaces / common separators and re-check ─────────────
        //    Catches evasion like "e x a m p l e @ g m a i l . c o m"
        //    or "0 9 1 7 - 1 2 3 - 4 5 6 7"
        $normalized = preg_replace('/[\s\-_.]+/', '', $content);

        // Re-check email on normalized string (@ is kept because we only strip spaces/dashes/dots/underscores)
        // We need a slightly relaxed pattern since dots were stripped
        if (preg_match('/[A-Za-z0-9]+@[A-Za-z0-9]+[A-Za-z]{2,}/i', $normalized)) {
            return true;
        }

        // Re-check phone patterns on normalized string
        $phoneNormalized = [
            '/\+63\d{10}/',   // +639XXXXXXXXX
            '/09\d{9}/',      // 09XXXXXXXXX
        ];
        foreach ($phoneNormalized as $pattern) {
            if (preg_match($pattern, $normalized)) {
                return true;
            }
        }

        // ── 3. Detect digits-only sequences (strip ALL non-digits) ───────
        //    Catches "0 9 1 7 1 2 3 4 5 6 7" → "09171234567"
        $digitsOnly = preg_replace('/\D/', '', $content);
        if (preg_match('/^63\d{10}$/', $digitsOnly) || preg_match('/^09\d{9}$/', $digitsOnly)) {
            return true;
        }

        return false;
    }

    // Detect suspicious keywords and platform names for message flagging
    private static function detectSuspiciousKeywords(string $content): bool
    {
        $path = storage_path('app/profanity_dataset.csv');

        $keywords = [];

        try {
            if (file_exists($path) && is_readable($path)) {
                if (($handle = fopen($path, 'r')) !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        foreach ($row as $cell) {
                            $w = trim((string) $cell);
                            if ($w === '')
                                continue;
                            $lw = strtolower($w);
                            // No exclusions - all keywords from dataset will be flagged
                            $keywords[] = $lw;
                        }
                    }
                    fclose($handle);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load profanity dataset', ['error' => $e->getMessage()]);
        }

        // Fallback keywords if CSV not loaded
        if (empty($keywords)) {
            $keywords = [
                'sex',
                'vagina',
                'penis',
                'fuck',
                'bitch',
                'whore',
                'slut',
                'dick',
                'cock',
                'pussy',
                'ass',
                'bastard',
                'damn',
                'harassment',
                'assault',
                'rape',
                'molest',
                'abuse',
                'porn'
            ];
        }

        // ALWAYS add platform keywords (users trying to move conversations off-platform)
        // These will be flagged regardless of CSV content
        $platformKeywords = [
            'gcash',
            'viber',
            'facebook',
            'instagram',
            'twitter',
            'tiktok',
            'youtube',
            'linkedin',
            'pinterest',
            'whatsapp',
            'telegram',
            'messenger',
            'snapchat',
            'discord',
            'wechat',
            'line',
            'skype'
        ];

        $keywords = array_merge($keywords, $platformKeywords);

        $contentLower = strtolower($content);

        foreach ($keywords as $keyword) {
            if ($keyword === '' || strlen($keyword) < 3)
                continue;

            // Sanitize keyword to valid UTF-8 before building regex patterns
            $keyword = mb_convert_encoding($keyword, 'UTF-8', 'UTF-8');
            if ($keyword === '') continue;

            // 1. First check: whole word match with word boundaries
            // This prevents matching 'ass' in 'class' or 'pass'
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/i';
            
            if (@preg_match($pattern, '') !== false && @preg_match($pattern, $content)) {
                return true;
            }

            // 2. For longer keywords (4+ chars), check for spaced-out versions
            // e.g., 'fuck' matches 'f u c k' or 'f.u.c.k'
            if (strlen($keyword) >= 4) {
                // Use mb_str_split so multi-byte chars split correctly
                $letters = array_map(
                    fn($c) => preg_quote($c, '/'),
                    mb_str_split(preg_quote($keyword, '/'), 1, 'UTF-8')
                );
                if (!empty($letters)) {
                    $pattern = '/\b' . implode('[\s\-\.]*', $letters) . '\b/i';
                    if (@preg_match($pattern, '') !== false && @preg_match($pattern, $content)) {
                        return true;
                    }
                }
            }

            // 3. For platform keywords, also check with spaces/dashes removed
            if (in_array($keyword, $platformKeywords)) {
                $normalized = preg_replace('/[\s\-_.]+/', '', $contentLower);
                if (stripos($normalized, $keyword) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    // Censor bad words by replacing with ### to hide profanity from users
    public static function censorBadWords(string $content): string
    {
        $path = storage_path('app/profanity_dataset.csv');
        $keywords = [];

        try {
            if (file_exists($path) && is_readable($path)) {
                if (($handle = fopen($path, 'r')) !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        foreach ($row as $cell) {
                            $w = trim((string) $cell);
                            if ($w === '')
                                continue;
                            $keywords[] = strtolower($w);
                        }
                    }
                    fclose($handle);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to load profanity dataset for censoring', ['error' => $e->getMessage()]);
        }

        // Fallback keywords if CSV not loaded
        if (empty($keywords)) {
            $keywords = [
                'sex', 'vagina', 'penis', 'fuck', 'bitch', 'whore', 'slut',
                'dick', 'cock', 'pussy', 'ass', 'bastard', 'damn',
                'harassment', 'assault', 'rape', 'molest', 'abuse'
            ];
        }

        $censoredContent = $content;

        // Replace each bad word with ### (case-insensitive)
        foreach ($keywords as $keyword) {
            // Sanitize keyword to valid UTF-8 — CSV may contain malformed bytes
            $keyword = mb_convert_encoding($keyword, 'UTF-8', 'UTF-8');
            if ($keyword === '' || strlen($keyword) < 3) continue;

            // Pattern 1: Normal word boundaries (e.g., "ass" but not "class")
            $pattern = '/\b' . preg_quote($keyword, '/') . '\b/iu';
            if (@preg_match($pattern, '') !== false) {
                $result = @preg_replace($pattern, '###', $censoredContent);
                if ($result !== null) $censoredContent = $result;
            }

            // Pattern 2: Words with any characters between letters (spaces, numbers, special chars)
            // Use mb_str_split so multi-byte chars are split correctly, not by raw bytes
            $chars = mb_str_split($keyword, 1, 'UTF-8');
            if (!empty($chars)) {
                $obfuscatedPattern = implode('[^a-z]*', array_map(fn($c) => preg_quote($c, '/'), $chars));
                $obfuscatedRegex = '/\b' . $obfuscatedPattern . '\b/iu';
                if (@preg_match($obfuscatedRegex, '') !== false) {
                    $result = @preg_replace($obfuscatedRegex, '###', $censoredContent);
                    if ($result !== null) $censoredContent = $result;
                }
            }
        }

        return $censoredContent;
    }

    // Validate message content against contact info and suspicious keywords rules
    public static function validateMessageContent(string $content): array
    {
        // Rule A: Hard Block - Contact Information
        if (self::detectContactInfo($content)) {
            return [
                'valid' => false,
                'flagged' => false,
                'reason' => null,
                'error' => 'Sharing contact info is not allowed. Please keep chats within Legatura'
            ];
        }

        // Rule B: Automated Flag - Suspicious Keywords
        if (self::detectSuspiciousKeywords($content)) {
            return [
                'valid' => true,
                'flagged' => true,
                'reason' => 'System: Suspicious Keyword Detected',
                'error' => null
            ];
        }

        // All clear
        return [
            'valid' => true,
            'flagged' => false,
            'reason' => null,
            'error' => null
        ];
    }

    // Check if user is admin from admin_users table or users.user_type
    private static function isAdminUser(int $userId): bool
    {
        // admin_id is now VARCHAR 'ADMIN-{n}' — query with prefix
        $isAdminUser = DB::table('admin_users')->where('admin_id', 'ADMIN-' . $userId)->exists();
        if ($isAdminUser)
            return true;

        // Check users table for user_type='admin'
        $user = DB::table('users')->where('user_id', $userId)->first();
        return $user && $user->user_type === 'admin';
    }

    // Get all conversations with flagged messages for admin moderation
    public static function getFlaggedConversations(): array
    {
        // Get all conversations that have at least one flagged message
        $conversations = DB::table('conversations as c')
            ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
            ->select(
                'c.conversation_id',
                'c.sender_id',
                'c.receiver_id',
                'c.status',
                'c.is_suspended',
                'c.suspended_until',
                'c.reason',
                'c.no_suspends',
                'c.is_admin_conversation',
                'm.content as last_content',
                'm.created_at as last_sent_at',
                DB::raw('(SELECT COUNT(*) FROM messages WHERE conversation_id = c.conversation_id AND is_flagged = 1) as flagged_count')
            )
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('messages')
                    ->whereColumn('messages.conversation_id', 'c.conversation_id')
                    ->where('messages.is_flagged', 1);
            })
            // Only show user-to-user conversations (exclude admin conversations)
            ->where('c.is_admin_conversation', 0)
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)')
            ->orderBy('m.created_at', 'desc')
            ->get();

        $result = [];
        foreach ($conversations as $conv) {
            // Use is_admin_conversation=0; these are user-to-user conversations
            $senderUser = self::getUserDetails($conv->sender_id, false);
            $receiverUser = self::getUserDetails($conv->receiver_id, false);

            if (!$senderUser || !$receiverUser)
                continue;

            $result[] = [
                'conversation_id' => $conv->conversation_id,
                'sender' => $senderUser,
                'receiver' => $receiverUser,
                'other_user' => $senderUser, // For compatibility with existing UI
                'last_message' => [
                    'content' => $conv->last_content,
                    'sent_at' => Carbon::parse($conv->last_sent_at)->diffForHumans(),
                    'sent_at_timestamp' => Carbon::parse($conv->last_sent_at, 'UTC')->toIso8601String()
                ],
                'unread_count' => 0, // Admin moderation - no unread count needed
                'flagged_count' => $conv->flagged_count,
                'is_flagged' => true,
                'status' => $conv->status,
                'is_suspended' => (bool) $conv->is_suspended,
                'suspended_until' => $conv->suspended_until,
                'reason' => $conv->reason,
                'no_suspends' => $conv->no_suspends ?? 0
            ];
        }

        return $result;
    }

    // Get all suspended conversations for admin moderation
    public static function getSuspendedConversations(): array
    {
        $conversations = DB::table('conversations as c')
            ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
            ->select(
                'c.conversation_id',
                'c.sender_id',
                'c.receiver_id',
                'c.status',
                'c.is_suspended',
                'c.suspended_until',
                'c.reason as suspension_reason',
                'c.no_suspends',
                'c.is_admin_conversation',
                'm.content as last_content',
                'm.created_at as last_sent_at'
            )
            ->where('c.status', 'suspended')
            // Only show user-to-user conversations (exclude admin conversations)
            ->where('c.is_admin_conversation', 0)
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)')
            ->orderBy('m.created_at', 'desc')
            ->get();

        $result = [];
        foreach ($conversations as $conv) {
            // Use is_admin_conversation=0; these are user-to-user conversations
            $senderUser = self::getUserDetails($conv->sender_id, false);
            $receiverUser = self::getUserDetails($conv->receiver_id, false);

            if (!$senderUser || !$receiverUser)
                continue;

            // Check if conversation has any flagged messages
            $isFlagged = DB::table('messages')
                ->where('conversation_id', $conv->conversation_id)
                ->where('is_flagged', 1)
                ->exists();

            $result[] = [
                'conversation_id' => $conv->conversation_id,
                'sender' => $senderUser,
                'receiver' => $receiverUser,
                'other_user' => $senderUser, // For compatibility
                'last_message' => [
                    'content' => $conv->last_content,
                    'sent_at' => Carbon::parse($conv->last_sent_at)->diffForHumans(),
                    'sent_at_timestamp' => Carbon::parse($conv->last_sent_at, 'UTC')->toIso8601String()
                ],
                'is_flagged' => $isFlagged,
                'status' => $conv->status,
                'is_suspended' => true,
                'suspended_until' => $conv->suspended_until,
                'reason' => $conv->suspension_reason,
                'no_suspends' => $conv->no_suspends ?? 0
            ];
        }

        return $result;
    }
}
