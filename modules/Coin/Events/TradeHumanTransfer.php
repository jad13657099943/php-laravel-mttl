<?php

namespace Modules\Coin\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Coin\Models\CoinTrade;

/**
 * 人工转账处理事件
 *
 * @package Modules\Coin\Events
 */
class TradeHumanTransfer
{
    use SerializesModels;

    public $trade;

    public function __construct(CoinTrade $trade)
    {
        $this->trade = $trade;
    }
}
