<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TarnibChooseSeatEvent implements shouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private int $player_number;
    private string $player_name;

    /**
     * Create a new event instance.
     */
    public function __construct(int $player_number, string $player_name)
    {
        $this->player_number = $player_number;
        $this->player_name = $player_name;
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
        return 'seat-chosen';
    }

    public function broadcastWith(){
        return [
            'player_number' => $this->player_number,
            'player_name' => $this->player_name,
        ];
    }
}
