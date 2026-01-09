<?php

namespace App\Events;

use App\Models\SystemNotifications;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Support\Facades\Log;

class SystemNotificationSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;

    public function __construct(SystemNotifications $notification)
    {
        $this->notification = $notification->load('sender');

        Log::info('SystemNotificationSent fired', [
            'notification_id' => $notification->id,
            'receiver_role'   => $notification->receiver_role,
        ]);
    }

    public function broadcastOn()
    {
        $channels = [];

        if ($this->notification->receiver_role === 'admin') {
            $channels[] = new PrivateChannel('admin.notifications');
        }

        if ($this->notification->receiver_role === 'barangay') {
            $channels[] = new PrivateChannel('barangay.notifications');
        }

        if ($this->notification->receiver_role === 'user') {
            $channels[] = new PrivateChannel('user.notifications');
        }
 
        return $channels;
    }

    public function broadcastAs()
    {
        return 'notification.sent';
    }

    public function broadcastWith()
    {
        return [
            'notification_id' => $this->notification->id,
            'title'           => $this->notification->title,
            'message'         => $this->notification->message,
            'receiver_role'   => $this->notification->receiver_role,
            'created_at'      => $this->notification->created_at,
        ];
    }
}
