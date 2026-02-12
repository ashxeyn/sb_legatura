<?php

namespace App\Models\message;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Events\messageSentEvent;
use App\Models\User;

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

    /**
     * Get the user_id of who sent this message
     */
    public function getSenderIdAttribute(): int
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $this->conversation_id)
            ->first();

        return $this->from_sender ? $conversation->sender_id : $conversation->receiver_id;
    }

    /**
     * Get attachments for this message
     *
     * @return \Illuminate\Support\Collection
     */
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

    /**
     * Store an attachment for this message
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return object|null
     */
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

    /**
     * Delete an attachment
     *
     * @param int $attachmentId
     * @return bool
     */
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

    /**
     * Get dashboard statistics for admin analytics
     *
     * @return array
     */
    public static function getDashboardStats(): array
    {
        // Total suspended conversations (exclude admin conversations)
        $totalSuspended = DB::table('conversations')
            ->where(function($query) {
                $query->where('status', 'suspended')
                      ->orWhere('is_suspended', 1);
            })
            ->whereNotExists(function($query) {
                // Exclude conversations where sender is admin
                $query->select(DB::raw(1))
                    ->from('admin_users')
                    ->whereColumn('admin_users.admin_id', 'conversations.sender_id');
            })
            ->whereNotExists(function($query) {
                // Exclude conversations where receiver is admin
                $query->select(DB::raw(1))
                    ->from('admin_users')
                    ->whereColumn('admin_users.admin_id', 'conversations.receiver_id');
            })
            ->whereNotExists(function($query) {
                // Exclude conversations where sender has user_type='admin'
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.user_id', 'conversations.sender_id')
                    ->where('users.user_type', 'admin');
            })
            ->whereNotExists(function($query) {
                // Exclude conversations where receiver has user_type='admin'
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.user_id', 'conversations.receiver_id')
                    ->where('users.user_type', 'admin');
            })
            ->count();

        // Active conversations (last 7 days, exclude admin conversations)
        $activeConversations = DB::table('conversations as c')
            ->join('messages as m', 'c.conversation_id', '=', 'm.conversation_id')
            ->where('m.created_at', '>=', Carbon::now()->subDays(7))
            ->where('c.status', '!=', 'suspended')
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('admin_users')
                    ->whereColumn('admin_users.admin_id', 'c.sender_id');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('admin_users')
                    ->whereColumn('admin_users.admin_id', 'c.receiver_id');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.user_id', 'c.sender_id')
                    ->where('users.user_type', 'admin');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.user_id', 'c.receiver_id')
                    ->where('users.user_type', 'admin');
            })
            ->distinct()
            ->count('c.conversation_id');

        // Flagged conversations count (unique conversations with flagged messages, exclude admin conversations)
        $flaggedMessages = DB::table('messages as m')
            ->join('conversations as c', 'm.conversation_id', '=', 'c.conversation_id')
            ->where('m.is_flagged', 1)
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('admin_users')
                    ->whereColumn('admin_users.admin_id', 'c.sender_id');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('admin_users')
                    ->whereColumn('admin_users.admin_id', 'c.receiver_id');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.user_id', 'c.sender_id')
                    ->where('users.user_type', 'admin');
            })
            ->whereNotExists(function($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.user_id', 'c.receiver_id')
                    ->where('users.user_type', 'admin');
            })
            ->distinct()
            ->count('c.conversation_id');

        return [
            'totalSuspended' => $totalSuspended ?? 0,
            'activeConversations' => $activeConversations ?? 0,
            'flaggedMessages' => $flaggedMessages ?? 0
        ];
    }

    /**
     * Get inbox/conversation list for a user
     *
     * @param int $userId
     * @return array
     */
    public static function getInbox(int $userId): array
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
                'm.content as last_content',
                'm.created_at as last_sent_at'
            )
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)')
            ->where(function ($query) use ($userId) {
                $query->where('c.sender_id', $userId)
                      ->orWhere('c.receiver_id', $userId);
            })
            ->orderBy('m.created_at', 'desc')
            ->get();

        $result = [];
        foreach ($conversations as $conv) {
            $otherUserId = ($conv->sender_id == $userId) ? $conv->receiver_id : $conv->sender_id;
            $otherUser = self::getUserDetails($otherUserId);

            if (!$otherUser) continue;

            // Calculate unread count: only count messages sent TO this user
            $isSender = ($userId == $conv->sender_id);
            $unreadCount = DB::table('messages')
                ->where('conversation_id', $conv->conversation_id)
                ->where('from_sender', !$isSender) // Opposite of user's role
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
                'reason' => $conv->reason
            ];
        }

        return $result;
    }

    /**
     * Store a new message with attachments
     *
     * @param array $data
     * @return messageClass|null
     */
    public static function storeMessage(array $data): ?messageClass
    {
        try {
            DB::beginTransaction();

            // SECURITY: Validate message content BEFORE saving
            $validation = self::validateMessageContent($data['content'] ?? '');

            // Note: Hard blocking is handled in controller
            // This check is redundant but kept for API safety
            if (!$validation['valid']) {
                DB::rollBack();
                \Log::warning('Message blocked: contact info detected', ['content' => $data['content']]);
                return null;
            }

            // Generate conversation ID if not provided (combine user IDs as integer)
            if (!isset($data['conversation_id'])) {
                $minId = min($data['sender_id'], $data['receiver_id']);
                $maxId = max($data['sender_id'], $data['receiver_id']);
                // Formula: smaller_id * 1000000 + larger_id (ensures uniqueness)
                $data['conversation_id'] = ($minId * 1000000) + $maxId;
            }

            // Get or create conversation
            $conversation = self::getOrCreateConversation(
                $data['conversation_id'],
                $data['sender_id'],
                $data['receiver_id']
            );

            // Determine if message is from sender or receiver
            $fromSender = ($data['sender_id'] == $conversation->sender_id);

            // Rule B: Prepare message data with auto-flag if suspicious keywords detected
            $messageData = [
                'conversation_id' => $data['conversation_id'],
                'from_sender' => $fromSender,
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

    /**
     * Get conversation history
     *
     * @param int $conversationId
     * @param int $limit
     * @return array
     */
    public static function getConversationHistory(int|string $conversationId, int $limit = 50): array
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) return [];

        $messages = self::where('conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get();

        $result = [];
        foreach ($messages as $msg) {
            // Determine actual sender based on from_sender boolean
            $senderId = $msg->from_sender ? $conversation->sender_id : $conversation->receiver_id;
            $sender = self::getUserDetails($senderId);

            $result[] = [
                'message_id' => $msg->message_id,
                'conversation_id' => $msg->conversation_id,
                'content' => $msg->content,
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

    /**
     * Get user details with polymorphic lookup
     *
     * @param int $userId
     * @return array|null
     */
    public static function getUserDetails(int $userId): ?array
    {
        // Check if this is an admin user first (admin_users table uses admin_id, not user_id)
        $admin = DB::table('admin_users')->where('admin_id', $userId)->first();

        if ($admin) {
            $name = $admin->username ?? $admin->email ?? 'Admin';
            $fullName = trim(($admin->first_name ?? '') . ' ' . ($admin->middle_name ?? '') . ' ' . ($admin->last_name ?? ''));
            if (!empty($fullName)) {
                $name = $fullName;
            }

            // Admin users don't have profile_pic in admin_users table yet
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=dc2626&color=fff&bold=true';

            return [
                'id' => $userId,
                'name' => $name,
                'type' => 'Admin',
                'avatar' => $avatar,
                'online' => false
            ];
        }

        // Get base user from users table
        $user = DB::table('users')->where('user_id', $userId)->first();

        if (!$user) return null;

        $name = $user->username ?? $user->email;
        $type = $user->user_type ?? 'user';
        $avatar = url('storage/' . ($user->profile_pic ?? 'default-avatar.png'));

        // Polymorphic lookup for profile details
        if ($type === 'admin') {
            // User type is admin, try to get from admin_users by user_id
            $profile = DB::table('admin_users')->where('user_id', $userId)->first();
            if ($profile) {
                $fullName = trim(($profile->first_name ?? '') . ' ' . ($profile->middle_name ?? '') . ' ' . ($profile->last_name ?? ''));
                $name = !empty($fullName) ? ($fullName) : ($profile->username ?? $name);
            }
        } elseif ($type === 'contractor' || $type === 'staff') {
            $profile = DB::table('contractors')->where('user_id', $userId)->first();
            if ($profile) {
                $name = $profile->company_name ?? $name;
            }
        } elseif ($type === 'owner' || $type === 'property_owner') {
            $profile = DB::table('property_owners')->where('user_id', $userId)->first();
            if ($profile) {
                $fullName = trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''));
                $name = !empty($fullName) ? $fullName : $name;
            }
        }

        // Use UI Avatars as fallback
        if (!$user->profile_pic) {
            $avatar = 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=6366f1&color=fff&bold=true';
        }

        return [
            'id' => $userId,
            'name' => $name,
            'type' => ucfirst($type),
            'avatar' => $avatar,
            'online' => false // Could implement real online status later
        ];
    }

    /**
     * Mark messages as read
     */
    public static function markAsRead(int|string $conversationId, int $userId): void
    {
        // Get conversation to determine user's role
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) {
            return;
        }

        // Determine if current user is sender or receiver
        $isSender = ($userId == $conversation->sender_id);

        // Only mark messages as read that were sent TO this user
        // If user is sender: mark messages where from_sender = false (sent by receiver)
        // If user is receiver: mark messages where from_sender = true (sent by sender)
        $affectedRows = self::where('conversation_id', $conversationId)
            ->where('from_sender', !$isSender) // Opposite of user's role
            ->where('is_read', 0)
            ->update(['is_read' => 1]);

        // Broadcast read event if any messages were marked as read
        if ($affectedRows > 0) {
            broadcast(new \App\Events\messagesReadEvent($conversationId, $userId));
        }
    }

    /**
     * Flag all messages in a conversation
     */
    public static function flagConversation(int|string $conversationId, string $reason, ?string $notes = null): void
    {
        DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->update([
                'is_flagged' => 1,
                'flag_reason' => $reason
            ]);
    }

    /**
     * Unflag all messages in a conversation
     */
    public static function unflagConversation(int|string $conversationId): void
    {
        DB::table('messages')
            ->where('conversation_id', $conversationId)
            ->update([
                'is_flagged' => 0,
                'flag_reason' => null
            ]);
    }

    /**
     * Suspend a conversation
     *
     * @param int|string $conversationId
     * @param string|null $reason
     * @param string|null $suspendedUntil DateTime string (Y-m-d H:i:s)
     */
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

    /**
     * Restore a suspended conversation
     */
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

    /**
     * Check and auto-restore expired suspensions
     *
     * @param int|string $conversationId
     * @return bool True if conversation is currently suspended (not expired)
     */
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

    /**
     * Get or create conversation
     */
    public static function getOrCreateConversation(int|string $conversationId, int $senderId, int $receiverId): object
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $conversationId)
            ->first();

        if (!$conversation) {
            DB::table('conversations')->insert([
                'conversation_id' => $conversationId,
                'sender_id' => $senderId,
                'receiver_id' => $receiverId,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $conversation = DB::table('conversations')
                ->where('conversation_id', $conversationId)
                ->first();
        }

        return $conversation;
    }

    /**
     * SECURITY: Detect contact information (emails and Philippine phone numbers)
     *
     * @param string $content
     * @return bool
     */
    private static function detectContactInfo(string $content): bool
    {
        // Email pattern
        $emailPattern = '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/';

        // Philippine phone patterns:
        // +63XXXXXXXXXX, 09XXXXXXXXX, (02) XXX-XXXX, etc.
        $phonePatterns = [
            '/\+63\s*\d{10}/',           // +63 9XX XXX XXXX
            '/\b09\d{9}\b/',             // 09XXXXXXXXX
            '/\(0?2\)\s*\d{3,4}[\s-]?\d{4}/', // (02) XXX-XXXX
            '/\b0\d{2,3}[\s-]?\d{3,4}[\s-]?\d{4}\b/' // 0XX XXX XXXX
        ];

        // Check email
        if (preg_match($emailPattern, $content)) {
            return true;
        }

        // Check phone patterns
        foreach ($phonePatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * SECURITY: Detect suspicious keywords that should flag a message
     *
     * @param string $content
     * @return bool
     */
    private static function detectSuspiciousKeywords(string $content): bool
    {
        $keywords = [
            'gcash', 'viber', 'telegram', 'pay outside', 'bank transfer',
            'sex', 'nigga', 'vagina', 'penis', 'fuck', 'bitch', 'whore',
            'slut', 'dick', 'cock', 'pussy', 'ass', 'bastard', 'damn',
            'harassment', 'assault', 'rape', 'molest', 'abuse', 'facebook', 'instagram', 'twitter',
            'porn', 'pornhub', 'negro', 'bobo', 'sinto sinto', 'kingina mo', 'putangina', 'putanginamo',
            'nigger', 'tarantado', 'ulol', 'gago', 'tanga amputa', 'amputa', 'punyemas',
            'tite', 'contact'
        ];

        $contentLower = strtolower($content);

        foreach ($keywords as $keyword) {
            if (stripos($contentLower, $keyword) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * SECURITY: Validate message content against all rules
     * Returns: ['valid' => bool, 'flagged' => bool, 'reason' => string|null, 'error' => string|null]
     *
     * @param string $content
     * @return array
     */
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

    /**
     * Check if a user is an admin (from admin_users table or users.user_type='admin')
     *
     * @param int $userId
     * @return bool
     */
    private static function isAdminUser(int $userId): bool
    {
        // Check admin_users table
        $isAdminUser = DB::table('admin_users')->where('admin_id', $userId)->exists();
        if ($isAdminUser) return true;

        // Check users table for user_type='admin'
        $user = DB::table('users')->where('user_id', $userId)->first();
        return $user && $user->user_type === 'admin';
    }

    /**
     * ADMIN MODERATION: Get all conversations with flagged messages
     *
     * @return array
     */
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
                'c.no_suspends',
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
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)')
            ->orderBy('m.created_at', 'desc')
            ->get();

        $result = [];
        foreach ($conversations as $conv) {
            // Skip conversations involving admin users
            if (self::isAdminUser($conv->sender_id) || self::isAdminUser($conv->receiver_id)) {
                continue;
            }

            $senderUser = self::getUserDetails($conv->sender_id);
            $receiverUser = self::getUserDetails($conv->receiver_id);

            if (!$senderUser || !$receiverUser) continue;

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
                'no_suspends' => $conv->no_suspends ?? 0
            ];
        }

        return $result;
    }

    /**
     * ADMIN MODERATION: Get all suspended conversations
     *
     * @return array
     */
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
                'm.content as last_content',
                'm.created_at as last_sent_at'
            )
            ->where('c.status', 'suspended')
            ->whereRaw('m.message_id = (SELECT message_id FROM messages WHERE conversation_id = c.conversation_id ORDER BY created_at DESC LIMIT 1)')
            ->orderBy('m.created_at', 'desc')
            ->get();

        $result = [];
        foreach ($conversations as $conv) {
            // Skip conversations involving admin users
            if (self::isAdminUser($conv->sender_id) || self::isAdminUser($conv->receiver_id)) {
                continue;
            }

            $senderUser = self::getUserDetails($conv->sender_id);
            $receiverUser = self::getUserDetails($conv->receiver_id);

            if (!$senderUser || !$receiverUser) continue;

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
                'suspension_reason' => $conv->suspension_reason,
                'no_suspends' => $conv->no_suspends ?? 0
            ];
        }

        return $result;
    }
}
