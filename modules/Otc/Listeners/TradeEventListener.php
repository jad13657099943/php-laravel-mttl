<?php

namespace Modules\Otc\Listeners;

use Modules\Otc\Events\TradeCreated;
use Modules\Otc\Jobs\TradeExpiredJob;
use Modules\Otc\Models\OtcTrade;

class TradeEventListener
{

    public function created(OtcTrade $trade)
    {
        TradeExpiredJob::dispatch($trade)->delay(strtotime($trade->expired_at) - time());
    }
}
