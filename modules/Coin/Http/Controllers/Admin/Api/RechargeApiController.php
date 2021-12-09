<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Services\CoinService;
use Modules\Coin\Services\RechargeService;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;

class RechargeApiController extends Controller
{

    public function index(Request $request, RechargeService $rechargeService)
    {

        //查询参数
        $where = [];
        $param = $request->input('key');
        if (!empty($param['times'])) {
            $time = explode('||', $param['times']);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            $where[] = ['created_at', '>=', [$time[0]]];
            $where[] = ['created_at', '<=', [$time[1]]];
        }
        if (!empty($param['symbol'])) {
            $where[] = ['symbol', '=', $param['symbol']];
        }
        if (!empty($param['from'])) {
            $where[] = ['from', '=', $param['from']];
        }
        if (!empty($param['id'])) {
            $where[] = ['id', '=', $param['id']];
        }
        if (isset($param['state']) && $param['state'] < 1000) {
            $where[] = ['state', '=', $param['state']];
        }
        if (!empty($param['user_info'])) {

            $userService = resolve(ProjectUserService::class);
            $userInfo = $userService->one(['show_userid' => $param['user_info']], [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('address', $param['user_info'])->value('user_id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->user_id;
            }
            $where[] = ['user_id', '=', $userId];
        }

       /* $list = $rechargeService->paginate($where, [
            'with' => ['projectUser', 'coin'],
            'orderBy' => ['id', 'desc'],
        ]);*/

         $sql=CoinRecharge::query()->where($where)->with('projectUser','coin')->orderBy('id','desc');
        if (!empty($param['team_mark'])) {
            $uid=ProjectUser::query()->where('show_userid', 'like', $param['team_mark'] . '%')->distinct()->pluck('user_id');
            $sql->whereIn('user_id',$uid);
        }
        $list=$sql->paginate($request->limit??10);

        $coinService = resolve(CoinService::class);
        foreach ($list->items() as $item) {
            $item->show=substr($item->projectUser['show_userid'],0,1);
            $item->username = $item->user->username;
            $item->value_text = $item->value . $item->symbol;
            $item->from_text = substr($item->from, 0, 4) . '****' . substr($item->from, -4, 4);
            $item->to_text = substr($item->to, 0, 4) . '****' . substr($item->to, -4, 4);
            $item->hash_text = empty($item->hash) ? '' : substr($item->hash, 0, 4) . '****' . substr($item->hash, -4, 4);
            $item->state_text = $item->state_name;

            //返回钱包地址跳转到区块链浏览器查询网址
            $item->from_link = $coinService->queryChainLink($item->coin->chain, $item->from);
            $item->to_link = $coinService->queryChainLink($item->coin->chain, $item->to);
            $item->hash_link = $coinService->queryChainLink($item->coin->chain, null, $item->hash);

        }

        return $list;
    }

    //统计
    public function sum(Request $request){

        $where[] = ['state', '=', 2];
        $param = $request->input('key');
        if (!empty($param['times'])) {
            $time = explode('||', $param['times']);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            if ($param['time_type'] == 0) {
                $where[] = ['created_at', '>=', [$time[0]]];
                $where[] = ['created_at', '<=', [$time[1]]];
            } else {
                $where[] = ['packaged_at', '>=', [$time[0]]];
                $where[] = ['packaged_at', '<=', [$time[1]]];
            }
        }

        $sql=CoinRecharge::query()->where($where)->with('projectUser','coin')->orderBy('id','desc');
        if (!empty($param['team_mark'])) {
            $uid=ProjectUser::query()->where('show_userid', 'like', $param['team_mark'] . '%')->distinct()->pluck('user_id');
            $sql->whereIn('user_id',$uid);
        }
        $sum=$sql->sum('value');
        return [
          'sum'=>$sum
        ];
    }

    /**
     * 获取跳转外链链接
     * @param Request $request
     * @return mixed
     */
    public function link(Request $request)
    {


        $id = $request->input('id');
        $rechargeService = resolve(RechargeService::class);
        $info = $rechargeService->getById($id, [
            'with' => 'coin'
        ]);

        $coinService = resolve(CoinService::class);
        if (!empty($info['hash'])) {
            $res = $coinService->queryChainLink($info->coin->chain, '', $info->hash);
        } else {
            $res = $coinService->queryChainLink($info->coin->chain, $info->from, '');
        }

        return ['url' => $res];
    }


    /**
     * 充值统计
     * 结果按团队标识分组
     * @param Request $request
     * @param RechargeService $rechargeService
     * @return
     */
    public function total(Request $request, RechargeService $rechargeService)
    {

        $userService = resolve(ProjectUserService::class);

        // 拿到所有的团队标识
        $teamMarks = $userService->getTeamMark();

        $where[] = ['state', '=', 2];
        $param = $request->input('key');
        if (!empty($param['times'])) {
            $time = explode('||', $param['times']);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            if ($param['time_type'] == 0) {
                $where[] = ['created_at', '>=', [$time[0]]];
                $where[] = ['created_at', '<=', [$time[1]]];
            } else {
                $where[] = ['packaged_at', '>=', [$time[0]]];
                $where[] = ['packaged_at', '<=', [$time[1]]];
            }
        }

//        if (!empty($param['user_info'])) {
//
//            $userInfo = $userService->one(['show_userid' => $param['user_info']], [
//                'exception' => false,
//                'queryCallback' => function ($query) use ($param) {
//                    $query->orWhere('address', $param['user_info'])->value('user_id');
//                }
//            ]);
//
//            if (!$userInfo) {
//                $userId = -1;
//            } else {
//                $userId = $userInfo->user_id;
//            }
//            $where[] = ['user_id', '=', $userId];
//        }

        if (!empty($param['team_mark'])) {
            $teamMarks = array($param['team_mark']);
        }

        $list = array();

        foreach ($teamMarks as $mark) {
            $data = $rechargeService->all($where, [
                'exception' => false,
                'queryCallback' => function ($query) use ($mark) {
                    $query->select(\DB::raw('SUM(value) as total_value'))
                        ->whereHas('projectUser', function (Builder $query) use ($mark) {
                            $query->where('show_userid', 'like', $mark . '%');
                        });
                }
            ])->toArray();

            foreach ($data as $item) {
                $item['team_mark'] = $mark;
                $item['total_value'] = floatval($item['total_value']);
                $item['symbol'] = 'USDT';
                $list[] = $item;
            }
        }


        return $list;
    }

}
