<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TarnibBidEvent implements shouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $bids_data;

    /**
     * Create a new event instance.
     */
    public function __construct($bids_data)
    {
        $this->bids_data = $bids_data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('public.room.1'),
        ];
    }

    public function broadcastAs(){
        return 'bid';
    }

    public function broadcastWith(){
        return json_decode(json_encode($this->bids_data), true);
    }
}
