<?php

namespace Modules\Coinv2\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Modules\Coin\Models\CoinTrade;
use Modules\Coinv2\Services\TradeService;

class SyncTradeStateJobV2 implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array
     */
    public $options;

    /**
     * 创建一个新的任务实例
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    /**
     * 执行任务
     * 主动同步coin_trade的状态
     *
     * @return void
     */
    public function handle()
    {
        /** @var TradeService $tradeService */
        /*$tradeService = resolve(TradeService::class);
        $tradeService->query()
            ->where('tokenio_version',2)
            ->whereIn('state', [CoinTrade::STATE_NOT_SUFFICIENT_FUNDS, CoinTrade::STATE_BROADCASTED])
            ->orderBy('id', 'asc') // 先进先出
            ->chunk(100, function (Collection $trades) use ($tradeService) {
                $trades->each(function (CoinTrade $trade) use ($tradeService) {
                    $tradeService->syncTradeState($trade);
                });
            });*/
    }
}
