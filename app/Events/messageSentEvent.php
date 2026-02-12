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

    /**
     * Create a new event instance.
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
     * Using a private channel for security - only the receiver can listen
     */
    public function broadcastOn(): array
    {
        // Broadcast to both participants
        return [
            new PrivateChannel('chat.' . $this->conversation->sender_id),
            new PrivateChannel('chat.' . $this->conversation->receiver_id),
        ];
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
     */
    public function broadcastWith(): array
    {
        // Determine actual sender based on from_sender boolean
        $senderId = $this->message->from_sender
            ? $this->conversation->sender_id
            : $this->conversation->receiver_id;

        $receiverId = $this->message->from_sender
            ? $this->conversation->receiver_id
            : $this->conversation->sender_id;

        $sender = messageClass::getUserDetails($senderId);
        $receiver = messageClass::getUserDetails($receiverId);

        return [
            'message_id' => $this->message->message_id,
            'conversation_id' => $this->message->conversation_id,
            'content' => $this->message->content,
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

