<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinAsset;
use Modules\Coin\Models\CoinLog;
use Modules\Coin\Services\AssetService;
use Modules\Coin\Services\CoinLogService;
use Modules\Core\Services\Frontend\UserService;
use Modules\Mttl\Models\RewardLog;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;

class CoinAssetApiController extends Controller
{

    /**
     * 资产余额列表
     * @param Request $request
     * @param AssetService $assetService
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request, AssetService $assetService)
    {


        //查询参数
        $where = [];
        $param = $request->input('key');
        if (!empty($param['symbol'])) {
            $where[] = ['symbol', '=', $param['symbol']];
        }

        if (!empty($param['balance'])) {
            $where[] = ['balance', '>=', $param['balance']];
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

   /*     $list = $assetService->paginate($where, [
            'with' => 'user',
            'orderBy' => ['balance', 'desc'],
            'whereEnabled' => false,
        ]);*/
        $sql= CoinAsset::query()->where($where)->with('user')->orderBy('balance','desc');
        if (!empty($param['team_mark'])) {
            $uid=ProjectUser::query()->where('show_userid', 'like', $param['team_mark'] . '%')->distinct()->pluck('user_id');
            $sql->whereIn('user_id',$uid);
        }
        $list=$sql->paginate($request->limit??10);
        foreach ($list->items() as $item) {
            $item->frozen_text = $item->frozen == 1 ? '已冻结' : '未冻结';
           $item->show=substr($item->user['show_userid'],0,1);
        }

        return $list;
    }


    public function coinLog(Request $request, CoinLogService $coinLogService)
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

        if (!empty($param['id'])) {
            $where[] = ['id', '=', $param['id']];
        }

        if (!empty($param['action'])) {
            $where[] = ['action', '=', $param['action']];
        }

        if (!empty($param['module_no'])) {
            $where[] = ['no', '=', $param['module_no']];
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


        $list = $coinLogService->paginate($where, [
            'with' => 'user',
            'orderBy' => ['id', 'desc'],
        ]);

        foreach ($list as $item) {
            if ($item->module === 'mttl') {
                $item->reward = RewardLog::query()
                    ->select('source_show_userid', 'algebra', 'extra')
                    ->where('id', $item->no)->first();
            }

            $item->username = $item->user->username;
            $item->num_text = $item->num . '  ' . $item->symbol;
            $item->module_action = $item->module_action_name;
        }

        //统计总数
        $total = CoinLog::query()->where($where)->sum('num');
        $list = $list->toArray();
        $list['total_data'] = floatval($total);

        return $list;

    }


    //冻结|解冻资产
    public function frozen(Request $request, AssetService $assetService)
    {

        $frozen = $request->input('frozen', 1);
        $id = $request->input('id');
        $info = $assetService->getById($id);
        $info->frozen = $frozen;
        $info->save();

        $text = $frozen == 0 ? '解除冻结成功' : '冻结成功';
        $log = "操作会员ID" . $info->user_id . "的" . $info->symbol . '资产' . $text;
        admin_operate_log('coin.frozen_asset', $log);

        return ['msg' => $text];

    }


}
