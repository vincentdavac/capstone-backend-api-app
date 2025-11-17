<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Verify user is part of this chat
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

    // User must be either sender or receiver
    return $chat->sender_id === $user->id || $chat->receiver_id === $user->id;
});

// NEW: Admin global chat list updates
Broadcast::channel('admin.chats', function ($user) {
    // Only admin should listen to this
    return $user->user_type === 'admin';
});
