<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public ChatMessage $message) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('chat.'.$this->message->channel_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ChatMessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'channel_id' => $this->message->channel_id,
            'body' => $this->message->body,
            'type' => $this->message->type->value,
            'sender' => [
                'id' => $this->message->sender?->id,
                'full_name' => $this->message->sender?->full_name,
            ],
            'created_at' => $this->message->created_at?->toIso8601String(),
        ];
    }
}
