<?php

namespace Modules\Mttl\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Modules\Mttl\Models\EnergyCardBuy;
use Modules\Mttl\Services\MeitaUserService;
use Modules\Mttl\Services\RewardLogService;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;
use Modules\Coinv2\Services\TradeService;

class MttlController extends Controller
{
    // 处理静态收益发放
    public function index()
    {
        $service = resolve(RewardLogService::class);
        EnergyCardBuy::query()
            ->where('surplus_days', '>', 0)
            ->where('begin_date', '<', date('Y-m-d H:i:s'))
//            ->where('finish_date', '>', date('Y-m-d H:i:s'))
            // ->orderByDesc('created_at')
            ->chunkById(100, function (Collection $buys) use ($service) {
                $buys->each(function (EnergyCardBuy $buy) use ($service) {
                    $service->staticReward($buy);
                });
            });
    }

    // 处理提现
    public function trade()
    {
        $sql = "select * from ti_coin_trade where tokenio_version=2 AND id in (
select min(id) from ti_coin_trade where `from` not in (select `from` from ti_coin_trade where state=0) and state in (-1,-4)
group by `from`,symbol ORDER BY weight DESC )
 AND ( TIMESTAMPDIFF(MINUTE,updated_at,NOW())>10 OR updated_at IS NULL)
 order by weight desc,updated_at asc  limit 10";

        $sql2="select * from ti_coin_trade where tokenio_version=2 and state in (-1,-4)
 order by weight desc,updated_at asc  limit 10";
        $list = \DB::select($sql);

        $tradeService = resolve(TradeService::class);
        foreach ($list as $item) {

            $data = $tradeService->getById($item->id);
//            ProcessTradeJobV2::dispatch($data);
            $tradeService->process($data);

        }
    }
}
