<?php

namespace Modules\Coin\Listeners;

use Modules\Coin\Models\CoinConfig;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coin\Services\TradeService;

class WithdrawEventListener
{
    /**
     * @param CoinWithdraw $withdraw
     */
    public function created(CoinWithdraw $withdraw)
    {

        //需要人工审核的这里不写入trade表，在后台审核之后，再写入trade表进行链上转账
        if ($withdraw->needTrade()) {
            $tokenioVersion = CoinConfig::query()->where('symbol',$withdraw->symbol)
                    ->where('chain',$withdraw->chain)
                    ->value('tokenio_version') ?? 1;
            if($tokenioVersion==1){
                /** @var TradeService $tradeService */
                $tradeService = resolve(TradeService::class);
                $tradeService->createWithWithdraw($withdraw); // 新生成的记录 写入trade表中

            }else{

                $tradeService = resolve(\Modules\Coinv2\Services\TradeService::class);
                $tradeService->createWithWithdraw($withdraw);

            }
        }
    }

}
