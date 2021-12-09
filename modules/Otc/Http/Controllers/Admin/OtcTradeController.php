<?php

namespace Modules\Otc\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Services\OtcCoinService;
use Modules\Otc\Services\OtcTradeService;

class OtcTradeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param OtcTrade $otcTrade
     * @param OtcExchange $otcExchange
     * @param OtcCoinService $otcCoinService
     * @return Response
     */
    public function index(OtcTrade $otcTrade, OtcExchange $otcExchange, OtcCoinService $otcCoinService)
    {

        $exchangeType = $otcExchange::$typeMap;
        $type = $otcTrade::$typeMap;
        $status = $otcTrade::$statusMap;
        $coin = $otcCoinService->all(null, [
            'exception' => false
        ])->pluck('coin');

        return view('otc::admin.otc_trade.index', [
            'exchange_type' => $exchangeType,
            'type' => $type,
            'status' => $status,
            'coin' => $coin
        ]);
    }


    /**
     * 详情
     * @param Request $request
     * @param OtcTradeService $otcTradeService
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function info(Request $request, OtcTradeService $otcTradeService)
    {

        $id = $request->input('id');
        $info = $otcTradeService->getById($id, [
            'with' => ['buyerUser.user', 'sellerUser.user']
        ]);


        $info->exchange_type_text = $info->exchange_type_text;
        $info->type_text = $info->type_text;
        $info->status_text = $info->status_text;

        //解析出收款账号信息
        if (!empty($info->bank)) {

            $bankText = '';
            foreach ($info->bank as $item) {
                switch ($item) {
                    case 'bank':
                        $bankText .= '银行卡 ';
                        break;
                    case 'wechat':
                        $bankText .= '微信 ';
                        break;
                    case 'alipay':
                        $bankText .= '支付宝 ';
                        break;
                    default:
                        break;
                }
            }

            $info->bank_type = $bankText;
        } else {
            $info->bank_type = '';
        }

        return view('otc::admin.otc_trade.info', [
            'info' => $info
        ]);
    }
}
