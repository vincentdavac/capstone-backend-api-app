<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;  // Changed this line
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow  // Changed this line
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');

        // Add logging to verify event is firing
        Log::info('MessageSent event created', [
            'message_id' => $message->id,
            'chat_id' => $message->chat_id
        ]);
    }

    public function broadcastOn()
    {
        // Channel for the specific chat window
        $chatChannel = new PrivateChannel('chat.' . $this->message->chat_id);

        // Global admin listener channel for updating ChatList
        $adminChannel = new PrivateChannel('admin.chats');

        Log::info('Broadcasting on channels', [
            'chat_channel' => 'chat.' . $this->message->chat_id,
            'admin_channel' => 'admin.chats'
        ]);

        return [$chatChannel, $adminChannel];
    }


    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'message' => $this->message,
        ];
    }
}
