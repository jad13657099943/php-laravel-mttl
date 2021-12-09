<?php

namespace Modules\Coinv2\Events;

use Illuminate\Queue\SerializesModels;
use Modules\Coin\Models\CoinTrade;

/**
 * 人工转账处理事件
 *
 * @package Modules\Coinv2\Events
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
