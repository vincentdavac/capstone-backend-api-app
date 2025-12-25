<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Support\Facades\Log;

class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;
    public $receiver;

    public function __construct(Message $message)
    {
        $this->message = $message->load('sender', 'chat');

        // // Load receiver from the chat
        // $this->receiver = $this->message->chat->sender_id === $message->sender_id
        //     ? $this->message->chat->receiver
        //     : $this->message->chat->sender;

        $receiverRole = $this->message->receiver->user_type ?? null;
        $senderRole = $this->message->sender->user_type ?? null;

        Log::info('MessageSent event created', [
            'message_id'    => $this->message->id,
            'chat_id'       => $this->message->chat_id,
            'sender_id'     => $this->message->sender_id,
            'sender_type'   => $senderRole,
            'receiver_id'   => $this->message->receiver_id,
            'receiver_type' => $receiverRole,
        ]);
    }

    public function broadcastOn()
    {
        $channels = [];

        // Chat window (real-time messages)
        $channels[] = new PrivateChannel('chat.' . $this->message->chat_id);

        // Global chat list updates (role-based)
        $receiverRole = $this->message->receiver->user_type;

        switch ($receiverRole) {
            case 'admin':
                $channels[] = new PrivateChannel('admin.chats');
                break;

            case 'barangay':
                $channels[] = new PrivateChannel('barangay.chats');
                break;

            case 'user':
                $channels[] = new PrivateChannel('user.chats');
                break;
        }

        // Log everything for debugging
        Log::info('MessageSent broadcast debug', [
            'chat_id'        => $this->message->chat_id,
            'message_id'     => $this->message->id,
            'message'        => $this->message->message,
            'sender_id'      => $this->message->sender_id,
            'sender_type'    => $this->message->sender->user_type ?? null,
            'receiver_id'    => $this->message->receiver_id,
            'receiver_type'  => $receiverRole,
            'channels'       => array_map(fn($c) => $c->name, $channels),
            'created_at'     => $this->message->created_at,
        ]);

        return $channels;
    }

    public function broadcastAs()
    {
        return 'message.sent';
    }

    public function broadcastWith()
    {
        return [
            'message' => [
                'id' => $this->message->id,
                'chat_id' => $this->message->chat_id,
                'body' => $this->message->body,
                'sender' => [
                    'id' => $this->message->sender->id,
                    'name' => $this->message->sender->name,
                    'role' => $this->message->sender->user_type,
                ],
                'created_at' => $this->message->created_at->toDateTimeString(),
            ],
        ];
    }
}
