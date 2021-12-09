<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinRecharge;
use Modules\Coin\Services\CoinService;

class RechargeController extends Controller
{

    /**
     * 充值列表
     * @param Request $request
     * @param CoinRecharge $coinRecharge
     * @param CoinService $coinService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request, CoinRecharge $coinRecharge, CoinService $coinService)
    {


        //交易状态
        $state = [
            $coinRecharge::STATE_REVOCATION => '被撤销【已撤销】',
            $coinRecharge::STATE_CONFIRMING => '待确认(需人工审核)',
            $coinRecharge::STATE_PROCESSING => '已确认待处理【处理中】',
            $coinRecharge::STATE_BROADCASTED => '广播完成.已生成哈希值',
            $coinRecharge::STATE_PACKAGED => '已打包',
            $coinRecharge::STATE_FINISHED => '已完成'
        ];

        //币种列表
        $coin = $coinService->enabledCoinList();

        return view('coin::admin.recharge.index', [
            'coin' => $coin,
            'state' => $state,
        ]);
    }


    /**
     * 充币统计
     */
    public function total(){
        $sum=CoinRecharge::query()->sum('value');
        return view('coin::admin.recharge.total',[
            'sum'=>$sum
        ]);
    }

}
