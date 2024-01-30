<?php

namespace AWF\Extension\Events;

use AWF\Extension\Responses\CustomJsonResponse;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\JsonResponse;
use Illuminate\Broadcasting\Channel;

class NextProductEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected array $message;
    
    public function __construct(array $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('next-product');
    }

    public function broadcastAs()
    {
        return 'next-product-event';
    }

    public function broadcastWith()
    {
        return $this->message;
    }
}
