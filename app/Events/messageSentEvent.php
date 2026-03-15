<?php

namespace App\Events;

use App\Models\message\messageClass;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class messageSentEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;
    public $censorForNonAdmin;

    /**
     * Create a new event instance.
     */
    public function __construct(messageClass $message, bool $censorForNonAdmin = true)
    {
        $this->message = $message;
        $this->censorForNonAdmin = $censorForNonAdmin;

        // Load conversation data
        $this->conversation = DB::table('conversations')
            ->where('conversation_id', $message->conversation_id)
            ->first();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $isAdminConv = (bool) ($this->conversation->is_admin_conversation ?? false);

        if ($isAdminConv) {
            // For admin conversations sender_id = receiver_id = user's ID (ID swap design).
            // Only broadcast censored version to the user's channel.
            // Admin gets the uncensored version exclusively via messageSentEventUncensored.
            // Exception: if message is NOT flagged, admin needs to receive it here too.
            $userChannel = new PrivateChannel('chat.' . $this->conversation->sender_id);

            if (!$this->message->is_flagged) {
                // Clean message — safe to send to both user and admin on this event
                $admin = DB::table('admin_users')->where('is_active', 1)->first();
                if ($admin) {
                    $adminNumericId = (int) preg_replace('/[^0-9]/', '', $admin->admin_id);
                    return [$userChannel, new PrivateChannel('chat.' . $adminNumericId)];
                }
            }

            // Flagged message — user gets censored via this event, admin gets uncensored via messageSentEventUncensored
            return [$userChannel];
        }

        // Regular user-to-user: broadcast to both participants
        $channels = [new PrivateChannel('chat.' . $this->conversation->sender_id)];
        if ($this->conversation->receiver_id !== $this->conversation->sender_id) {
            $channels[] = new PrivateChannel('chat.' . $this->conversation->receiver_id);
        }
        return $channels;
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent';
    }

    /**
     * Get the data to broadcast.
     * Returns a clean JSON payload for mobile/web clients
     * Censors bad words for non-admin users in user-to-user conversations
     */
    public function broadcastWith(): array
    {
        // Get context for user lookup (admin conversation vs user conversation)
        $isAdminConv = (bool) ($this->conversation->is_admin_conversation ?? false);

        // Determine actual sender and receiver based on from_sender boolean and conversation type
        if ($isAdminConv) {
            // For admin conversations: from_sender=true means user sent it, false means admin sent it
            // Both sender_id and receiver_id are the user's ID
            if ($this->message->from_sender) {
                // User sent the message
                $senderId = $this->conversation->sender_id;
                $sender = messageClass::getUserDetails($senderId, false);
                $receiver = messageClass::getAdminDetails();
            } else {
                // Admin sent the message
                $sender = messageClass::getAdminDetails();
                $receiverId = $this->conversation->sender_id;
                $receiver = messageClass::getUserDetails($receiverId, false);
            }
        } else {
            // Regular or contractor conversation
            $senderId = $this->message->from_sender
                ? $this->conversation->sender_id
                : $this->conversation->receiver_id;

            $receiverId = $this->message->from_sender
                ? $this->conversation->receiver_id
                : $this->conversation->sender_id;

            $convContractorId = $this->conversation->contractor_id ?? null;
            if ($convContractorId !== null) {
                // For contractor conversations, resolve contractor owner's user_id
                $cOwnerId = \Illuminate\Support\Facades\DB::table('contractors')
                    ->where('contractor_id', $convContractorId)->value('owner_id');
                $cOwnerUserId = $cOwnerId
                    ? \Illuminate\Support\Facades\DB::table('property_owners')
                        ->where('owner_id', $cOwnerId)->value('user_id')
                    : null;

                // If the sender is the contractor owner, show company details instead of personal profile
                if ($senderId == $cOwnerUserId) {
                    $contractor = \Illuminate\Support\Facades\DB::table('contractors')
                        ->where('contractor_id', $convContractorId)->first();
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
                        $sender = messageClass::getUserDetails($senderId, false);
                    }
                } else {
                    $sender = messageClass::getUserDetails($senderId, false);
                }
                $receiver = messageClass::getUserDetails($receiverId, false);
            } else {
                $sender = messageClass::getUserDetails($senderId, false);
                $receiver = messageClass::getUserDetails($receiverId, false);
            }
        }

        // Prepare content:
        // - For admin conversations: censor for the user (admin gets uncensored via messageSentEventUncensored)
        // - For user-to-user conversations: censor for non-admin viewers if flagged
        $messageContent = $this->message->content;
        if ($this->message->is_flagged) {
            if ($isAdminConv || $this->censorForNonAdmin) {
                $messageContent = messageClass::censorBadWords($this->message->content);
            }
        }

        return [
            'message_id' => $this->message->message_id,
            'conversation_id' => $this->message->conversation_id,
            'content' => $messageContent,
            'sender' => $sender,
            'receiver' => $receiver,
            'attachments' => $this->message->attachments->map(function ($attachment) {
                return [
                    'attachment_id' => $attachment->attachment_id,
                    'file_name' => $attachment->file_name,
                    'file_type' => $attachment->file_type,
                    'file_url' => url('storage/' . $attachment->file_path),
                    'is_image' => in_array(strtolower(pathinfo($attachment->file_name, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif', 'webp'])
                ];
            })->toArray(),
            'is_read' => (bool) $this->message->is_read,
            'is_flagged' => (bool) $this->message->is_flagged,
            'flag_reason' => $this->message->flag_reason,
            'status' => $this->conversation->status,
            'sent_at' => $this->message->created_at->toIso8601String(),
            'sent_at_human' => $this->message->created_at->diffForHumans(),
            'timestamp' => $this->message->created_at->timestamp,
            'contractor_id' => $this->conversation->contractor_id ?? null,
        ];
    }
}

