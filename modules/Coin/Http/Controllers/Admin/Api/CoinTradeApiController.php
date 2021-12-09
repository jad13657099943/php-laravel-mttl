<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Components\TokenIO\TokenIO;
use Modules\Coin\Services\CoinService;
use Modules\Coin\Services\RechargeService;
use Modules\Coin\Services\TradeService;
use Modules\Coin\Services\WithdrawService;
use Modules\Core\Services\Frontend\UserService;
use Modules\User\Services\ProjectUserService;

class CoinTradeApiController extends Controller
{

    /**
     * 链上转账列表
     * @param Request $request
     * @param TradeService $tradeService
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function index(Request $request, TradeService $tradeService)
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
        if (isset($param['state']) && $param['state'] < 1000) {
            $where[] = ['state', '=', $param['state']];
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


        $list = $tradeService->paginate($where, [
            'with' => ['user','coin'],
            'orderBy' => ['id', 'desc'],
        ]);


        $coinService = resolve(CoinService::class);
        foreach ($list as $item) {

            $item->state_text = $item->state_text;
            $item->num_text = $item->num . $item->symbol;
            $item->from_text = substr($item->from, 0, 4) . '****' . substr($item->from, -4, 4);
            $item->to_text = substr($item->to, 0, 4) . '****' . substr($item->to, -4, 4);
            $item->hash_text = empty($item->hash) ? '' : substr($item->hash, 0, 4) . '****' . substr($item->hash, -4, 4);
            switch ($item->action) {
                case 'pooling_cold':
                    $actionName = '归集';
                    break;
                case 'gas':
                    $actionName = '补gas';
                    break;
                case 'withdraw':
                    $actionName = '提现';
                    break;
                default:
                    $actionName = $item->action;
                    break;
            }
            $item->module_action = $actionName;
            if(empty($item->user->show_userid)){
                $item->user_info = 0;
            }else{
                $item->user_info = $item->user->show_userid . '(' . $item->user->address . ')';
            }


            //返回钱包地址跳转到区块链浏览器查询网址
            $item->from_link = $coinService->queryChainLink($item->coin->chain, $item->from);
            $item->to_link = $coinService->queryChainLink($item->coin->chain, $item->to);
            $item->hash_link = $coinService->queryChainLink($item->coin->chain, null, $item->hash);

        }

        return $list;
    }


    /**
     * 标记已手动转账
     * @param Request $request
     * @param TradeService $tradeService
     * @return string[]
     * @throws \Exception
     */
    public function manualTransfer(Request $request, TradeService $tradeService)
    {

        $id = $request->input('id');
        $res = $request->input('res', 0);
        if ($res == 0) {
            throw new \Exception('没有转账请不要提交');
        }

        $info = $tradeService->getById($id);
        if ($info->state != -2) {
            throw new \Exception('该信息无法执行手动转账操作');
        }

        $info->state = 2;
        $info->updated_at = date('Y-m-d H:i:s');
        $info->state_info = $info->state_info . ',手动确定转账';
        if ($info->save()) {

            //提现记录，更新对应的提现记录状态为2已完成
            if ($info->action == 'withdraw' && $info->module == 'coin' && $info->no > 0) {

                $withdrawService = resolve(WithdrawService::class);
                $withdrawInfo = $withdrawService->getById($info->no);
                if ($withdrawInfo && $withdrawInfo->state != 2) {
                    $withdrawInfo->state = 2;
                    $withdrawInfo->save();

                    $log = "修改了链上转账记录ID" . $id . "状态为已手动转账";
                    admin_operate_log('coin.coin_trade',$log);

                }
            }

            //\Log::info("修改了链上转账记录ID" . $id . "状态为已手动转账", $info->toArray());
            return ['msg' => '操作成功'];
        } else {

            \Log::warning("修改了链上转账记录ID" . $id . "状态为已手动转账,操作失败", $info->toArray());
            throw new \Exception('更改状态失败，请联系开发者');
        }
    }


    /**
     * 二次请求转账
     */
    public function tradeAgain(Request $request, TradeService $tradeService)
    {

        $id = $request->input('id');
        $info = $tradeService->getById($id);
        if (empty($info)) {
            throw new \Exception('信息不存在');
        }

        //二次请求转账
        $tradeService->recallToTokenio($id);

        \Log::info("为链上转账记录ID" . $id . "执行了再次请求转账", $info->toArray());
        return ['msg' => '请求成功，实际转账以链上到账为准'];
    }


    /**
     * 获取跳转外链链接
     * @return mixed
     * @throws \app\common\exceptions\MissingValueException
     */
    public function link(Request $request)
    {


        $id = $request->input('id');
        $rechargeService = resolve(TradeService::class);
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


}
