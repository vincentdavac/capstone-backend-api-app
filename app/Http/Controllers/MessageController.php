<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Chat;
use App\Models\Message;
use App\Traits\HttpResponses;
use App\Http\Requests\MessageRequest;
use App\Http\Resources\MessageSentResource;
use App\Http\Resources\ChatResource;
use App\Http\Resources\PendingChatsResource;
use App\Http\Resources\UserResource;
use App\Events\MessageSent;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    use HttpResponses;


    // Barangay and Admin Side: Send a message
    public function send(MessageRequest $request)
    {
        $sender = Auth::user();
        // $sender = User::findOrFail($request->sender_id);
        $receiver = User::findOrFail($request->receiver_id);

        /**
         *  ROLE RESTRICTIONS
         */
        // ALLOWED PAIRS
        if (
            ($sender->user_type === 'admin' && $receiver->user_type === 'admin') ||
            ($sender->user_type === 'admin' && $receiver->user_type === 'barangay') ||
            ($sender->user_type === 'barangay' && $receiver->user_type === 'admin') ||
            ($sender->user_type === 'barangay' && $receiver->user_type === 'user' && $sender->barangay_id === $receiver->barangay_id) ||
            ($sender->user_type === 'user' && $receiver->user_type === 'barangay' && $sender->barangay_id === $receiver->barangay_id)
        ) {

            /**
             *  CHECK IF CHAT EXISTS OR CREATE NEW
             */
            $chat = Chat::where(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $sender->id)
                    ->where('receiver_id', $receiver->id);
            })->orWhere(function ($query) use ($sender, $receiver) {
                $query->where('sender_id', $receiver->id)
                    ->where('receiver_id', $sender->id);
            })->first();

            if (!$chat) {
                $chat = Chat::create([
                    'sender_id'   => $sender->id,
                    'receiver_id' => $receiver->id,
                ]);
            }

            /**
             *  MESSAGE DATA
             */
            $messageData = [
                'chat_id'   => $chat->id,
                'sender_id' => $sender->id,
                'message'   => $request->message,
            ];

            /**
             *  ATTACHMENT HANDLING (10MB MAX)
             */
            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $filename = Str::random(32) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('message_attachments'), $filename);
                $messageData['attachment'] = $filename;
            }

            /**
             *  CREATE MESSAGE
             */
            $message = Message::create($messageData);

            broadcast(new MessageSent($message))->toOthers();


            /**
             *  RETURN RESOURCE
             */
            return new MessageSentResource($message->load('sender'));
        } else {
            return $this->error(null, 'Chat not allowed between these user types', 403);
        }
    }

    // User Side: Send message to Barangay
    public function sendMessageUserToBarangay(MessageRequest $request)
    {
        $sender = Auth::user();

        // Only allow sending if sender is a user or admin
        if (!in_array($sender->user_type, ['user', 'admin'])) {
            return $this->error(null, 'Only admin or user can send messages to barangay', 403);
        }

        // Find the barangay user in the same barangay as sender
        $receiver = User::where('user_type', 'barangay')
            ->where('barangay_id', $sender->barangay_id)
            ->first();

        if (!$receiver) {
            return $this->error(null, 'No barangay user found in your barangay', 404);
        }

        /**
         *  CHECK IF CHAT EXISTS OR CREATE NEW
         */
        $chat = Chat::where(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $sender->id)
                ->where('receiver_id', $receiver->id);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender_id', $receiver->id)
                ->where('receiver_id', $sender->id);
        })->first();

        if (!$chat) {
            $chat = Chat::create([
                'sender_id'   => $sender->id,
                'receiver_id' => $receiver->id,
            ]);
        }


        /**
         *  MESSAGE DATA
         */
        $messageData = [
            'chat_id'   => $chat->id,
            'sender_id' => $sender->id,
            'message'   => $request->message,
        ];

        /**
         *  ATTACHMENT HANDLING (10MB MAX)
         */
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = Str::random(32) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('message_attachments'), $filename);
            $messageData['attachment'] = $filename;
        }


        $message = Message::create($messageData);

        broadcast(new MessageSent($message))->toOthers();


        return new MessageSentResource($message->load('sender'));
    }

    // User Side: Get chat with Barangay
    public function getChatUserToBarangay()
    {
        $user = Auth::user();

        // Only allow regular users
        if ($user->user_type !== 'user') {
            return $this->error(null, 'Only regular users can access this chat', 403);
        }

        // Find the barangay user in the same barangay
        $barangayUser = User::where('user_type', 'barangay')
            ->where('barangay_id', $user->barangay_id)
            ->first();

        if (!$barangayUser) {
            return $this->error(null, 'No barangay user found in your barangay', 404);
        }

        // Get the chat with messages only
        $chat = Chat::with([
            'messages' => function ($query) {
                $query->whereNotNull('message')->orderBy('created_at', 'asc');
            },
            'messages.sender',
            'sender',
            'receiver'
        ])->where(function ($query) use ($user, $barangayUser) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $barangayUser->id);
        })->orWhere(function ($query) use ($user, $barangayUser) {
            $query->where('sender_id', $barangayUser->id)
                ->where('receiver_id', $user->id);
        })->first();

        if (!$chat || $chat->messages->isEmpty()) {
            return $this->error(null, 'No messages found in this chat', 404);
        }

        // Return messages using MessageSentResource
        $messages = MessageSentResource::collection($chat->messages);

        return $this->success([
            'chatId'   => $chat->id,
            'sender'   => $chat->sender,
            'receiver' => $chat->receiver,
            'messages' => $messages,
        ], 'Chat retrieved successfully');
    }

    //  Get messages for a specific chat
    public function getChat($chatId)
    {
        $user = Auth::user();

        // Load chat + messages + sender details
        $chat = Chat::with([
            'messages' => function ($query) {
                $query->orderBy('created_at', 'asc');
            },
            'messages.sender',
            'sender',
            'receiver'
        ])->find($chatId);

        // If chat does not exist
        if (!$chat) {
            return $this->error(null, 'Chat not found', 404);
        }

        // Security: Ensure user belongs to the chat
        if ($chat->sender_id !== $user->id && $chat->receiver_id !== $user->id) {
            return $this->error(null, 'Unauthorized to access this chat', 403);
        }

        // Return messages using MessageSentResource
        $messages = ChatResource::collection($chat->messages);

        return $this->success([
            'chatId'   => $chat->id,
            'sender'   => new UserResource($chat->sender),    // ✅ Use UserResource
            'receiver' => new UserResource($chat->receiver),  // ✅ Use UserResource
            'messages' => $messages,
        ], 'Chat retrieved successfully');
    }

    // Admin Side: Get all Barangays and Admins that have chats, or create one if none exists
    public function getAllBarangayChats()
    {
        $admin = Auth::user();

        // Make sure only admin can access this
        if ($admin->user_type !== 'admin') {
            return $this->error(null, 'Unauthorized', 403);
        }

        // Get all users with user_type = barangay or admin, exclude self
        $users = User::whereIn('user_type', ['barangay', 'admin'])
            ->where('id', '!=', $admin->id)
            ->get();

        $data = $users->map(function ($user) use ($admin) {

            // Find existing chat between admin and this user
            $chat = Chat::where(function ($query) use ($admin, $user) {
                $query->where('sender_id', $admin->id)
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($admin, $user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $admin->id);
            })->first();

            // If chat does not exist, create one
            if (!$chat) {
                $chat = Chat::create([
                    'sender_id'   => $admin->id,
                    'receiver_id' => $user->id,
                ]);
            }

            // Load messages for the chat, descending
            $chat->load(['messages' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);

            // Get latest message timestamp for sorting
            $latestMessageTime = $chat->messages->first()?->created_at ?? null;

            return [
                'user' => new UserResource($user),
                'chat' => new PendingChatsResource($chat),
                'latest_message_time' => $latestMessageTime,
            ];
        });

        // Sort users by latest message time (DESC)
        $sortedData = $data
            ->sortByDesc('latest_message_time')
            ->map(function ($item) {
                unset($item['latest_message_time']); // Remove temporary sorting key
                return $item;
            })
            ->values(); // Reset array keys

        return $this->success($sortedData, 'All barangay and admin users chats retrieved successfully');
    }


    // Barangay Side: Get all Users and Admins that have chats, or create one if none exists
    public function getAllUserAndAdminChats()
    {
        $barangay = Auth::user();

        // Make sure only barangay can access this
        if ($barangay->user_type !== 'barangay') {
            return $this->error(null, 'Unauthorized', 403);
        }

        // Get all users with user_type = admin or user
        $users = User::whereIn('user_type', ['admin', 'user'])->get();

        $data = $users->map(function ($user) use ($barangay) {

            // Find existing chat between barangay and this user
            $chat = Chat::where(function ($query) use ($barangay, $user) {
                $query->where('sender_id', $barangay->id)
                    ->where('receiver_id', $user->id);
            })->orWhere(function ($query) use ($barangay, $user) {
                $query->where('sender_id', $user->id)
                    ->where('receiver_id', $barangay->id);
            })->first();

            // If chat does not exist, create one
            if (!$chat) {
                $chat = Chat::create([
                    'sender_id' => $barangay->id,
                    'receiver_id' => $user->id,
                ]);
            }

            // Load messages for the chat, descending
            $chat->load(['messages' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }]);

            // Get the latest message timestamp for sorting
            $latestMessageTime = $chat->messages->first()?->created_at ?? null;

            return [
                'user' => new UserResource($user),
                'chat' => new PendingChatsResource($chat),
                'latest_message_time' => $latestMessageTime,
            ];
        });

        // Sort users by latest message time (DESC), so most recent chats appear first
        $sortedData = $data
            ->sortByDesc('latest_message_time')
            ->map(function ($item) {
                unset($item['latest_message_time']); // Remove temp sorting field
                return $item;
            })
            ->values(); // Reset array keys

        return $this->success($sortedData, 'All admin and regular users chats retrieved successfully');
    }

    // Mark all messages in a chat as read
    public function markChatAsRead($chatId)
    {
        $user = Auth::user();

        // Load chat with sender + receiver
        $chat = Chat::with(['sender', 'receiver'])
            ->find($chatId);

        if (!$chat) {
            return $this->error(null, 'Chat not found', 404);
        }

        // Ensure user belongs to the chat
        if ($chat->sender_id !== $user->id && $chat->receiver_id !== $user->id) {
            return $this->error(null, 'Unauthorized to update this chat', 403);
        }

        /**
         * Mark all messages as read
         * BUT ONLY messages where the viewer is the receiver
         */
        Message::where('chat_id', $chatId)
            ->where('sender_id', '!=', $user->id)      // only messages from the other user
            ->where('is_read', false)                  // only unread messages
            ->update(['is_read' => true]);

        return $this->success(null, 'Messages marked as read successfully');
    }
}
