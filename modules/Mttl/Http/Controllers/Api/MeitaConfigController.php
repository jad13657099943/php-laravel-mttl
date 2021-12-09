<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Modules\Coin\Jobs\ProcessTradeJob;
use Modules\Coin\Models\CoinUserWallet;
use Modules\Mttl\Services\NewService;
use Modules\User\Models\ProjectUser;

class MeitaConfigController extends Controller
{
    /**
     * 返回质押升级的配置信息
     * @return Repository|Application|mixed
     */
    public function upgrade()
    {
        return [
            'grade' => config('user::config.pledge_grade', 1),
            'amount' => config('user::config.pledge_amount', 3000),
            'takeAway' => config('user::config.take_away', 90000)
        ];
    }

    /**
     * 获取最新区块高度
     * @return array|mixed
     */
    public function blockHeight()
    {
        $response = Http::get('https://trx.tokenview.com/api/coin/latest/trx');
        if ($response->ok()) {
            return $response->json();
        } else {
            return ['code' => 0, 'msg' => '失败', 'data' => 0];
        }
    }

    /**
     * 兑换配置
     * @return array|mixed
     */
    public function exchange(Request $request,NewService $service)
    {
        $uid= $request->user['id'];
        $service->getTokenIo($uid);
        return [
            'status' => intval(config('user::config.exchange_open', 0))
        ];
    }

    /**
     * 充值通知
     * @param Request $request
     * @param NewService $service
     * @return int[]
     */
    public function tokenIo(Request $request,NewService $service){
        $uid= $request->user['id'];
        return $service->getTokenIo($uid);
    }

    /**
     * 手动触发
     * @param Request $request
     */
    public function tokenIo2(Request  $request,NewService  $service){
        $id= $request->id;
        $uid= ProjectUser::query()->where('show_userid',$id)->value('user_id');
        return $service->getTokenIo($uid);
    }
}
