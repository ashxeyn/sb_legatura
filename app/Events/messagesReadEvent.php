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

class messagesReadEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $conversationId;
    public $readByUserId;

    /**
     * Create a new event instance.
     */
    public function __construct(int|string $conversationId, int $readByUserId)
    {
        $this->conversationId = $conversationId;
        $this->readByUserId = $readByUserId;
    }

    /**
     * Get the channels the event should broadcast on.
     * Broadcast to the OTHER user in the conversation (who sent the messages)
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

        // Broadcast to the other user (who sent the messages that were just read)
        $otherUserId = ($this->readByUserId == $conversation->sender_id)
            ? $conversation->receiver_id
            : $conversation->sender_id;

        return [
            new PrivateChannel('chat.' . $otherUserId),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'messages.read';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversationId,
            'read_by_user_id' => $this->readByUserId,
            'read_at' => now()->toIso8601String()
        ];
    }
}
