<?php


namespace Modules\Coin\Http\Controllers\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\Coin;
use Modules\Coin\Models\CoinAsset;
use Modules\Coin\Models\CoinConfig;

class SymbolController extends Controller
{

    /**
     * 获取币种的主链、tokenio版本 设置
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function coinConfig(Request $request)
    {

        $type = $request->input('type', 'recharge');
        if ($type == 'recharge') {
            $where[] = ['recharge_state', '=', 1];
        } else {
            $where[] = ['withdraw_state', '=', 1];
        }

        $list = CoinConfig::query()->where($where)
            ->select('id', 'symbol')
            ->groupBy('symbol')
            ->get();
        foreach ($list as $item) {
            $item->chain_list = CoinConfig::query()->where($where)
                ->where('symbol', $item->symbol)
                ->select('chain', 'tokenio_version','agreement')
                ->get();
        }

        return $list;
    }


    /**
     * 获取单币种详情
    */
    public function symbolInfo(Request $request){

        $symbol = $request->input('symbol');
        $symbol = strtoupper($symbol);
        $info = Coin::query()->where('symbol',$symbol)->first();
        return $info;
    }


    /**
     * 获取单币种余额
     * @param Request $request
     * @return array
     */
    public function symbolBalance(Request $request){

        $userId = $request->user()->id;
        $symbol = $request->input('symbol');
        $symbol = strtoupper($symbol);

        $balance = CoinAsset::query()->where('user_id',$userId)
            ->where('symbol',$symbol)
            ->value('balance') ?? 0;

        return [
            'balance'=>floatval($balance),
        ];
    }

}
