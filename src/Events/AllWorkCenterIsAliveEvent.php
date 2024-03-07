<?php

namespace AWF\Extension\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\Channel;

class AllWorkCenterIsAliveEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('all-work-center-is-alive');
    }

    public function broadcastAs()
    {
        return 'all-work-center-is-alive-event';
    }

    public function broadcastWith()
    {
        return $this->message;
    }
}
