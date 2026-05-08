<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationTypingStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatConversation $conversation, public int $userId, public string $name) {}

    public function broadcastOn(): array
    {
        return collect([$this->conversation->student_id, $this->conversation->participant_id])
            ->unique()
            ->map(fn (int $userId): PrivateChannel => new PrivateChannel('messages.user.'.$userId))
            ->all();
    }

    public function broadcastAs(): string
    {
        return 'typing.started';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'user_id' => $this->userId,
            'name' => $this->name,
        ];
    }
}
