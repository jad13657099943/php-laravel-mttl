<?php

namespace Modules\Coin\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coin\Services\CoinService;

class WithdrawController extends Controller
{
    /**
     * 提现列表
     * @return Response
     */
    public function index(CoinWithdraw $coinWithdraw, CoinService $coinService)
    {

        //交易状态
        $state = [
            $coinWithdraw::STATE_CANCELED => '被撤销【已撤销】',
            $coinWithdraw::STATE_HUMAN_TRANSFER => '待确认(需人工审核)',
            $coinWithdraw::STATE_PROCESSING => '已确认待处理【处理中】',
            $coinWithdraw::STATE_LOGGED => '广播完成.已生成哈希值',
            $coinWithdraw::STATE_PACKAGED => '已打包',
            $coinWithdraw::STATE_FINISHED => '已完成'
        ];

        //币种列表
        $coin = $coinService->enabledCoinList();

        return view('coin::admin.withdraw.index', [
            'state' => $state,
            'coin' => $coin,
        ]);

    }


    /**
     * 提现统计
     */
    public function total(){
        $succeed=CoinWithdraw::query()->whereIn('state',[0,2])->sum('num');
        $fail=CoinWithdraw::query()->whereNotIn('state',[0,2])->sum('num');
        return view('coin::admin.withdraw.total',[
            'succeed'=>$succeed,
            'fail'=>$fail
        ]);
    }

}
