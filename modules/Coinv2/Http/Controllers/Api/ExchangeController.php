<?php

namespace Modules\Coinv2\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Coinv2\Http\Requests\ExchangeOrderCreateRequest;
use Modules\Coinv2\Services\CoinService;
use Modules\Coinv2\Services\ExchangeService;
use Modules\Coinv2\Services\ExchangeSettingsService;

class ExchangeController
{
    /**
     * 获取闪兑市场交易对
     */
    public function settings(ExchangeSettingsService $service)
    {
        $payList = $service->all([
            'status' => 1,
        ], [
            'exception' => false,
            'queryCallback'=>function($query){
                $query->select('id','coin_pay','status')->groupBy('coin_pay');
            }
        ]);

        //返回支付币种可以兑换哪些获得币种 eg：USDT=>BTC,ETH
        if ($payList->isNotEmpty()) {


            foreach ($payList as $item) {
                $getList = $service->all([
                    'status' => 1,
                    'coin_pay' => $item->coin_pay
                ], [
                    'exception' => false,
                ]);

                $item->get_list = $getList;
            }
        }

        return $payList;
    }

    /**
     * 创建闪兑订单
     */
    public function createOrder(ExchangeOrderCreateRequest $request, ExchangeService $exchangeOrderService)
    {
        return $exchangeOrderService->create(array_merge([
            'user' => $request->user()
        ], $request->validationData()));
    }


    /**
     * 个人兑换订单列表
     * @param Request $request
     * @param ExchangeService $service
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function orderList(Request $request, ExchangeService $service)
    {

        $userId = $request->user()->id;
        $where[] = ['user_id', '=', $userId];
        return $service->paginate($where, [
            'orderBy' => ['id', 'desc'],
        ]);
    }

}
