<?php

namespace Modules\Coin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Services\TradeService;

/**
 * 主动检测并发起链上交易处理
 *
 * @package Modules\Coin\Jobs
 */
class CheckTradeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var array $options
     */
    public $options;

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function handle()
    {
        /** @var TradeService $tradeService */
        $tradeService = resolve(TradeService::class);
        /*$tradeService->query()
            ->whereNeedProcess()
            ->orderBy('id', 'asc') // 先进先出
            ->chunk(100, function(Collection $trades) {
                $trades->each(function(CoinTrade $trade) {
                    ProcessTradeJob::dispatch($trade);
                });
            });*/

        //根据币对应的tokenio版本，选择对应的数据
        $sql = "select * from ti_coin_trade where tokenio_version=1 AND id in (
select min(id) from ti_coin_trade where `from` not in (select `from` from ti_coin_trade where state=0) and state in (-1,-4)
group by `from`,symbol ORDER BY weight DESC )
 AND ( TIMESTAMPDIFF(MINUTE,updated_at,NOW())>10 OR updated_at IS NULL)
 order by weight desc,updated_at asc  limit 10";

        $list = \DB::select($sql);
        foreach ($list as $item) {

            $data = $tradeService->getById($item->id);
            ProcessTradeJob::dispatch($data);

        }
    }
}
