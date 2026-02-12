<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ConversationSuspendedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $status; // 'suspended' or 'active'
    public $reason;
    public $suspendedUntil;

    /**
     * Create a new event instance.
     */
    public function __construct($conversationId, string $status, ?string $reason = null, ?string $suspendedUntil = null)
    {
        $this->conversationId = $conversationId;
        $this->status = $status;
        $this->reason = $reason;
        $this->suspendedUntil = $suspendedUntil;
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcast to both participants in the conversation
     */
    public function broadcastOn(): array
    {
        // Get conversation participants
        $conversation = DB::table('conversations')
            ->where('conversation_id', $this->conversationId)
            ->first();

        if (!$conversation) {
            return [];
        }

        // Broadcast to both users
        return [
            new PrivateChannel('chat.' . $conversation->sender_id),
            new PrivateChannel('chat.' . $conversation->receiver_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'conversation.suspended';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'status' => $this->status,
            'is_suspended' => ($this->status === 'suspended'),
            'reason' => $this->reason,
            'suspended_until' => $this->suspendedUntil,
            'timestamp' => now()->timestamp
        ];
    }
}
