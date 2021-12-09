<?php

namespace Modules\Coin\Listeners;

use Modules\Coin\Events\TradeHumanTransfer;
use Modules\Coin\Jobs\ProcessTradeJob;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coin\Services\TradeService;

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
        ProcessTradeJob::dispatch($processTrade);
    }
}
