<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TarnibMoveToNewRoomEvent implements shouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $new_room_id;
    private $room_id;

    /**
     * Create a new event instance.
     */
    public function __construct($new_room_id, $room_id)
    {
        $this->new_room_id = $new_room_id;
        $this->room_id = $room_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('public.room.'.$this->room_id),
        ];
    }

    public function broadcastAs(){
        return 'moved-room';
    }

    public function broadcastWith(){
        return ['new_room_id' => $this->new_room_id];
    }
}
