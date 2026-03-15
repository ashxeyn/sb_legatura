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
     * Broadcast to the OTHER participant so they see the double-check update.
     */
    public function broadcastOn(): array
    {
        $conversation = DB::table('conversations')
            ->where('conversation_id', $this->conversationId)
            ->first();

        if (!$conversation) {
            return [];
        }

        $isAdminConv = (bool) ($conversation->is_admin_conversation ?? false);

        if ($isAdminConv) {
            // For admin conversations sender_id = receiver_id = user's ID.
            // Whoever just called markAsRead:
            //   - If it's the user reading admin's messages → broadcast to admin's channel
            //   - If it's the admin reading user's messages → broadcast to user's channel
            $userIdOfConv = $conversation->sender_id; // always the regular user's ID

            if ($this->readByUserId == $userIdOfConv) {
                // User read admin's messages — tell admin their messages were seen
                $admin = DB::table('admin_users')->where('is_active', 1)->first();
                if ($admin) {
                    $adminNumericId = (int) preg_replace('/[^0-9]/', '', $admin->admin_id);
                    return [new PrivateChannel('chat.' . $adminNumericId)];
                }
                return [];
            } else {
                // Admin read user's messages — tell user their messages were seen (double-check)
                return [new PrivateChannel('chat.' . $userIdOfConv)];
            }
        }

        // Regular conversation: broadcast to the sender of those messages (the other user)
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
