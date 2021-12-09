<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Services\CoinService;

/* 内部转账 */

class InternalTradeController extends Controller
{
    public function index(Request $request,CoinService $coinService)
    {

        //币种列表
        $coin = $coinService->enabledCoinList();
        return view('coin::admin.internal_trade.index', [
            'coin' => $coin
        ]);
    }
}
