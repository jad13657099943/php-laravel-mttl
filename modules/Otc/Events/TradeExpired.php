<?php

namespace Modules\Otc\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Otc\Models\OtcTrade;

class TradeExpired
{
    use SerializesModels;

    public $otcTrade;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OtcTrade $otcTrade)
    {
        $this->otcTrade = $otcTrade;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
