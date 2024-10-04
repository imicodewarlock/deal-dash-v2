<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OfferCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Offer $offer;

    /**
     * Create a new event instance.
     * 
     * @return void
     */
    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }
}
