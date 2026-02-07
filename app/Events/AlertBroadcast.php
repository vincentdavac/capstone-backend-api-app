<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class AlertBroadcast implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $alert;

    public function __construct(array $alert)
    {
        $this->alert = $alert;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.alerts'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'alert.sent';
    }

    public function broadcastWith(): array
    {
        return [
            'description' => $this->alert['description'],
            'alert_level' => $this->alert['alert_level'],
            'broadcast_by' => $this->alert['broadcast_by'],
            'sensor_type' => $this->alert['sensor_type'] ?? '',
            'recorded_at' => $this->alert['recorded_at'],
            'counts'=>$this->alert['counts'],
        ];
    }
}
