<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TelemetryUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deviceId;
    public $data;

    public function __construct($deviceId, $data)
    {
        $this->deviceId = $deviceId;
        $this->data = $data;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('telemetry.' . $this->deviceId),
        ];
    }
}
