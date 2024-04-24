<?php

namespace AWF\Extension\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\JsonResponse;
use Illuminate\Broadcasting\Channel;

class WelderNextProductEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $message;

    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('next-welder-product');
    }

    public function broadcastAs()
    {
        return 'next-welder-product-event';
    }

    public function broadcastWith()
    {
        return $this->message;
    }
}
