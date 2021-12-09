<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Http\Requests\CoinRequest;
use Modules\Coin\Models\CoinConfig;
use Modules\Coin\Services\CoinService;

class CoinApiController extends Controller
{

    public function index(Request $request, CoinService $coinService)
    {

        $list = $coinService->paginate(null, [

            'orderBy' => ['id', 'desc'],
            'whereEnabled' => false,
        ]);

        foreach ($list as &$item) {
            $item->withdraw_min = floatval($item->withdraw_min);
            $item->withdraw_max = floatval($item->withdraw_max);
            $item->withdraw_fee = floatval($item->withdraw_fee);
            $item->recharge_min = floatval($item->recharge_min);
            $item->status_text = $item->status == 1 ? '开启' : '关闭';
            //提现状态：0=暂停提现、1=全部需要人工审核、2=走系统设置
            switch ($item->withdraw_state) {
                case 0:
                    $item->withdraw_state_text = '暂停提现';
                    break;
                case 1:
                    $item->withdraw_state_text = '人工审核';
                    break;
                case 2:
                    $item->withdraw_state_text = '走系统设置';
                    break;
                default:
                    break;
            }
        }
        return $list;
    }


    public function edit(CoinRequest $request, CoinService $coinService)
    {

        $id = $request->input('id');
        $info = $coinService->getById($id);
        $info->withdraw_min = $request->input('withdraw_min', 0);
        $info->withdraw_max = $request->input('withdraw_max', 0);
        $info->withdraw_fee = $request->input('withdraw_fee', 0);
        $info->gas_price = $request->input('gas_price', 0);
        $info->withdraw_state = $request->input('withdraw_state', 0);
        $info->recharge_state = $request->input('recharge_state', 0);
        $info->recharge_min = $request->input('recharge_min', 0);
        $info->cold_min = $request->input('cold_min', 0);
        $info->internal_state = $request->input('internal_state', 0);
        $info->save();

        //充提状态同步更新到coinConfig表
        if ($info->withdraw_state == 0) {
            //关闭提币
            CoinConfig::query()->where('symbol', $info->symbol)->update([
                'withdraw_state' => 0
            ]);
        } else {
            CoinConfig::query()->where('symbol', $info->symbol)->update([
                'withdraw_state' => 1
            ]);
        }

        if ($info->recharge_state == 0) {
            //关闭充币
            CoinConfig::query()->where('symbol', $info->symbol)->update([
                'recharge_state' => 0
            ]);
        } else {
            CoinConfig::query()->where('symbol', $info->symbol)->update([
                'recharge_state' => 1
            ]);
        }

        //删除缓存
        $key = 'coin:' . $info->symbol;
        \Cache::tags($key)->flush();

        return ['msg' => '设置成功'];
    }


}
