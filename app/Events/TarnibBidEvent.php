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

    private int $player_index;
    private int $amount;

    /**
     * Create a new event instance.
     */
    public function __construct(int $player_index, string $amount)
    {
        $this->player_index = $player_index;
        $this->amount = $amount;
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
        return [
            'player_index' => $this->player_index,
            'amount' => $this->amount,
        ];
    }
}
