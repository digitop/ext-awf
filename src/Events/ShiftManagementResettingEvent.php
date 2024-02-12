<?php

namespace AWF\Extension\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\JsonResponse;
use Illuminate\Broadcasting\Channel;

class ShiftManagementResettingEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('shift-management-reset');
    }

    public function broadcastAs()
    {
        return 'shift-management-reset-event';
    }

    public function broadcastWith()
    {
        return $this->message;
    }
}
