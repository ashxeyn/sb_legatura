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

class messageSentEventUncensored implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $conversation;

    /**
     * Create a new event instance.
     * This event broadcasts the UNCENSORED message to admin channels
     */
    public function __construct(messageClass $message)
    {
        $this->message = $message;

        // Load conversation data
        $this->conversation = DB::table('conversations')
            ->where('conversation_id', $message->conversation_id)
            ->first();
    }

    /**
     * Get the channels the event should broadcast on.
     * Only broadcast to admin channel — admin needs to see the uncensored version.
     */
    public function broadcastOn(): array
    {
        $isAdminConv = (bool) ($this->conversation->is_admin_conversation ?? false);

        if ($isAdminConv) {
            // Broadcast uncensored version to ALL active admins
            $channels = [];
            $admins = DB::table('admin_users')->where('is_active', 1)->get();
            foreach ($admins as $admin) {
                $adminNumericId = (int) preg_replace('/[^0-9]/', '', $admin->admin_id);
                $channels[] = new PrivateChannel('chat.' . $adminNumericId);
            }
            return $channels;
        }

        return [];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'message.sent.uncensored';
    }

    /**
     * Get the data to broadcast (UNCENSORED).
     * Returns the original message content for admin viewing
     */
    public function broadcastWith(): array
    {
        $isAdminConv = (bool) ($this->conversation->is_admin_conversation ?? false);

        if ($isAdminConv) {
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
            $senderId = $this->message->from_sender
                ? $this->conversation->sender_id
                : $this->conversation->receiver_id;

            $receiverId = $this->message->from_sender
                ? $this->conversation->receiver_id
                : $this->conversation->sender_id;

            $sender = messageClass::getUserDetails($senderId, false);
            $receiver = messageClass::getUserDetails($receiverId, false);
        }

        return [
            'message_id' => $this->message->message_id,
            'conversation_id' => $this->message->conversation_id,
            'content' => $this->message->content, // UNCENSORED
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
            'timestamp' => $this->message->created_at->timestamp
        ];
    }
}
