<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TarnibChooseTarnibEvent implements shouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private string $suit;

    /**
     * Create a new event instance.
     */
    public function __construct(string $suit)
    {
        $this->suit = $suit;
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
        return 'tarnib-chosen';
    }

    public function broadcastWith(){
        return [
            'suit' => $this->suit,
        ];
    }
}
