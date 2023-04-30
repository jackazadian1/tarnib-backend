<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TarnibNewRoundEvent implements shouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $data;
    private $room_id;
    private $token;

    /**
     * Create a new event instance.
     */
    public function __construct($data, $room_id, $token)
    {
        $this->data = $data;
        $this->room_id = $room_id;
        $this->token = $token;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('public.room.'.$this->room_id.$this->token),
        ];
    }

    public function broadcastAs(){
        return 'new-round';
    }

    public function broadcastWith(){
        return $this->data;
    }
}
