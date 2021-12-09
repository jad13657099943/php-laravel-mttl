<?php

namespace Modules\Coinv2\Listeners;

use Modules\Coin\Models\CoinRecharge;
use Modules\Coinv2\Services\TradeService;

class RechargeEventListener
{
    /**
     * @param CoinRecharge $recharge
     */
    public function saved(CoinRecharge $recharge)
    {
        // 触发归集任务(v2版本不用归集)
//        if ($recharge->needPoolingCold()) {
//            /** @var TradeService $tradeService */
//            $tradeService = resolve(TradeService::class);
//            $tradeService->createWithRecharge($recharge, $options['createOptions'] ?? []);
//        }
    }
}
