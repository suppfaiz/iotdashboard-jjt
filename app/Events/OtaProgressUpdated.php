<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OtaProgressUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $deviceId;
    public $progress;
    public $status;
    public $message;

    public function __construct($deviceId, $progress, $status, $message = '')
    {
        $this->deviceId = $deviceId;
        $this->progress = intval($progress);
        $this->status = $status;
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('telemetry.' . $this->deviceId),
        ];
    }
}
