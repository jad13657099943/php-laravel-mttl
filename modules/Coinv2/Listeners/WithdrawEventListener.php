<?php

namespace Modules\Coinv2\Listeners;

use Modules\Coin\Models\CoinWithdraw;
use Modules\Coinv2\Services\TradeService;

class WithdrawEventListener
{
    /**
     * @param CoinWithdraw $withdraw
     */
    public function created(CoinWithdraw $withdraw)
    {
        //需要人工审核的这里不写入trade表，在后台审核之后，再写入trade表进行链上转账
        if ($withdraw->needTrade()) {
            /** @var TradeService $tradeService */
            $tradeService = resolve(TradeService::class);
            $tradeService->createWithWithdraw($withdraw); // 新生成的记录 写入trade表中
        }
    }

}
