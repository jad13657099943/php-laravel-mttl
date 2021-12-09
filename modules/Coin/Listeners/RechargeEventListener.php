<?php

namespace Modules\Coin\Listeners;

use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Services\TradeService;

class RechargeEventListener
{
    /**
     * @param CoinRecharge $recharge
     */
    public function saved(CoinRecharge $recharge)
    {
        // 触发归集任务
        if ($recharge->needPoolingCold()) {
            /** @var TradeService $tradeService */
            $tradeService = resolve(TradeService::class);
            $tradeService->createWithRecharge($recharge, $options['createOptions'] ?? []);
        }
    }
}
