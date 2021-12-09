<?php

namespace Modules\Otc\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Otc\Models\OtcTradeAppeal;

class TradeAppealCreated
{
    use SerializesModels;

    public $otcTradeAppeal;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OtcTradeAppeal $otcTradeAppeal)
    {
        $this->otcTradeAppeal = $otcTradeAppeal;
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
