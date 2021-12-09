<?php

namespace Modules\Otc\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Otc\Models\OtcExchange;

class ExchangeSucceed
{
    use SerializesModels;

    public $otcExchange;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(OtcExchange $otcExchange)
    {
        $this->otcExchange = $otcExchange;
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
