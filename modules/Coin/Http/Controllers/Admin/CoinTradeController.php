<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinTrade;
use Modules\Coin\Services\CoinLogModuleService;
use Modules\Coin\Services\CoinService;
use Modules\Coin\Services\TradeService;


/* 链上转账 */

class CoinTradeController extends Controller
{

    public function index(Request $request, CoinService $coinService, CoinTrade $coinTrade, CoinLogModuleService $coinLogModuleService)
    {

        //币种列表
        $coin = $coinService->enabledCoinList();

        //交易状态
        $state = [
            $coinTrade::STATE_NOT_SUFFICIENT_FUNDS => '余额不足',
            $coinTrade::STATE_CANCELED => '被取消',
            $coinTrade::STATE_HUMAN_TRANSFER => '人工转账',
            $coinTrade::STATE_PROCESSING => '已确认待处理【处理中】',
            $coinTrade::STATE_BROADCASTED => '广播完成.已生成哈希值',
            $coinTrade::STATE_PACKAGED => '已打包',
            $coinTrade::STATE_FINISHED => '已完成'
        ];

        //业务类型(只查询coin模块的业务)
        //$module = $coinLogModuleService->all(['module' => 'coin']);
        $module = [
            'withdraw' => '提现',
            'pooling_cold' => '归集',
            'gas' => '补gas',
        ];
        return view('coin::admin.coin_trade.index', [
            'state' => $state,
            'coin' => $coin,
            'module' => $module
        ]);

    }


    /*
     * 信息详情和显示手动转账二维码
     * */
    public function info(Request $request, TradeService $tradeService)
    {

        $id = $request->input('id');
        $info = $tradeService->getById($id, [
            'with' => 'coin'
        ]);
        $info->state_text = $info->state_text;
        $info->num = floatval($info->num);


        $msg = '';
        $money = 0;
        $qrcode = '';
        //计算手动转账时，生成二维码的内容(只有需要手动转账的时候才去计算显示)
        if ($info->state == -2) {
            if ($info->coin->chain == 'ETH') {
                if ($info->symbol == 'USDT') {

                    $money = abs($info['num']) * 1000000; //转成eth金额格式
                    $decimal = 6;
                    $money = $this->numToStr($money); //避免被转成科学计数法
                    $qrcode = "ethereum:" . $info['to'] . "?value=" . $money . "&decimal=" . $decimal;


                } else {
                    $money = abs($info['num']) * 1000000000000000000; //转成eth金额格式
                    $decimal = 18;
                    $money = $this->numToStr($money); //避免被转成科学计数法
                    $qrcode = "ethereum:" . $info['to'] . "?value=" . $money . "&decimal=" . $decimal;
                }


            } elseif ($info->coin->chain == 'BTC') {
                $money = abs($info['num']) * 1000000000000000000; //转成eth金额格式
                $decimal = 18;
                $money = $this->numToStr($money); //避免被转成科学计数法
                $qrcode = "bitcoin:" . $info['to'] . "?amount=" . $money;

            }elseif ($info->coin->chain == 'FIL') {
                $money = abs($info['num']) * 1000000000000000000; //转成eth金额格式
                $decimal = 18;
                $money = $this->numToStr($money); //避免被转成科学计数法
                $qrcode = $info['to'];

            } else {

                $decimal = 0;
                $msg = "该币种对应的主链暂不支持手动转账操作";
            }
        }


        return view('coin::admin.coin_trade.info', [
            'info' => $info,
            'qrcode' => $qrcode,
            'msg' => $msg
        ]);
    }


    function numToStr($num)
    {
        $result = "";
        if (stripos($num, 'e') === false) {
            return $num;
        }
        while ($num > 0) {
            $v = $num - floor($num / 10) * 10;
            $num = floor($num / 10);
            $result = $v . $result;
        }
        return $result;
    }


}
