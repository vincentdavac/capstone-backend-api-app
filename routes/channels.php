<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;


/*
|--------------------------------------------------------------------------
| Chat window (per chat)
|--------------------------------------------------------------------------
*/

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    $chat = Chat::find($chatId);

    if (!$chat) {
        return false;
    }

    return $chat->sender_id === $user->id
        || $chat->receiver_id === $user->id;
});


/*
|--------------------------------------------------------------------------
| Global chat list updates (role-based)
|--------------------------------------------------------------------------
*/
Broadcast::channel('admin.chats', function ($user) {
    return $user->user_type === 'admin';
});

Broadcast::channel('barangay.chats', function ($user) {
    return $user->user_type === 'barangay';
});

Broadcast::channel('user.chats', function ($user) {
    return $user->user_type === 'user';
});



/*
|--------------------------------------------------------------------------
| Global notification updates (role-based)
|--------------------------------------------------------------------------
*/

Broadcast::channel('admin.notifications', function ($user) {
    return $user->user_type === 'admin';
});

Broadcast::channel('barangay.notifications', function ($user) {
    return $user->user_type === 'barangay';
});

Broadcast::channel('user.notifications', function ($user) {
    return $user->user_type === 'user';
});
