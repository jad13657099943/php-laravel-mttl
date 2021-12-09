<?php


namespace Modules\Mttl\Http\Controllers\Api;


use Illuminate\Routing\Controller;
use Modules\Coinv2\Services\TokenioNoticeService;
use Modules\Coinv2\Services\TradeService;
use Modules\Mttl\Services\MeitaUserService;
use Modules\User\Models\ProjectUser;
use Illuminate\Support\Collection;
class JobController extends Controller
{
    /**
     * 出金
     * @throws \Exception
     */
    public function job(){
        $sql = "select * from ti_coin_trade where tokenio_version=2 AND id in (
select min(id) from ti_coin_trade where `from` not in (select `from` from ti_coin_trade where state=0) and state in (-1,-4)
group by `from`,symbol ORDER BY weight DESC )
 AND ( TIMESTAMPDIFF(MINUTE,updated_at,NOW())>10 OR updated_at IS NULL)
 order by weight desc,updated_at asc  limit 10";

         $sql2="select * from ti_coin_trade where tokenio_version=2 and state in (-1,-4)
  order by weight desc,updated_at asc  limit 10";
        $list = \DB::select($sql2);

        $tradeService = resolve(TradeService::class);
        foreach ($list as $item) {

            $data = $tradeService->getById($item->id);
//            ProcessTradeJobV2::dispatch($data);
            $tradeService->process($data);

        }
    }

    /**
     * 处理通知
     * @throws \Throwable
     */
    public function job2(){
        $service = resolve(TokenioNoticeService::class);
        $list = $service->all([
            'state' => 0,
            'version' => 2,
        ], [
            'limit' => 10,
            'orderBy' => ['id', 'asc']
        ]);

        if ($list->isNotEmpty()) {

            foreach ($list as $item) {
                $service->process($item);
            }
        }
    }

    /**
     * 自动进化
     */
    public function job3(){
        echo date('Y-m-d H:i:s') . "自动进化job：\n";
        $service = resolve(MeitaUserService::class);

        ProjectUser::query()
            ->where('type', 1)
            ->where('grade', '!=', 5)
            ->orderByDesc('user_id')
            ->chunkById(100, function (Collection $users) use ($service) {
                $users->each(function (ProjectUser $user) use ($service) {
                    $service->evolution($user);
                });
            });
    }


}
