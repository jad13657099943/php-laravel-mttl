<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinLogModules;
use Modules\Coin\Services\AssetService;
use Modules\Coin\Services\CoinLogModuleService;
use Modules\Coin\Services\CoinService;

/* 资产余额 */

class CoinAssetController extends Controller
{

    //余额列表
    public function index(Request $request, CoinService $coinService)
    {

        //统计每个币种的总数据
        $assetService = resolve(AssetService::class);
        $total = $assetService->all(null, [
            'exception' => false,
            'whereEnabled' => false,
            'queryCallback' => function ($query) {
                $query->select('symbol', \DB::raw('COUNT(*) as total_count'), \DB::raw('SUM(balance) as total_num'))
                    ->groupBy('symbol');
            }
        ]);


        //币种列表
        $coin = $coinService->enabledCoinList();
        return view('coin::admin.coin_asset.index', [
            'coin' => $coin,
            'total' => $total
        ]);
    }

    //资产变更记录
    public function assetLog(CoinService $coinService, CoinLogModuleService $coinLogModuleService)
    {

        //币种列表
        $coin = $coinService->enabledCoinList();

        //业务类型
//        $module = $coinLogModuleService->all(['enable' => 1]);

        $action = CoinLogModules::query()->where('enable', 1)->get()->toArray();
        //print_r($action);exit();

        return view('coin::admin.coin_asset.coin_log', [
            'coin' => $coin,
//            'module' => $module,
            'action' => $action,
        ]);
    }


}
