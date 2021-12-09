<?php

namespace Modules\Coinv2\Listeners;

use Modules\Coinv2\Events\TradeHumanTransfer;
use Modules\Coinv2\Jobs\ProcessTradeJobV2;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coinv2\Services\TradeService;

class TradeEventListener
{
    /**
     * @param CoinTrade $trade
     */
    public function saved(CoinTrade $trade)
    {
        if ($trade->state == CoinWithdraw::STATE_HUMAN_TRANSFER) {
            event(new TradeHumanTransfer($trade));
        }

        $processTrade = $trade; // 默认处理当前

        if ($trade->isDirty('state')) {

            if ($trade->isGas() && $trade->hasFinished()) { // gas通知成功通知处理归集交易
                /** @var TradeService $tradeService */
                $tradeService = resolve(TradeService::class);
                $processTrade = $tradeService->getById($trade->no);
            }
        }
        ProcessTradeJobV2::dispatch($processTrade);
    }
}
