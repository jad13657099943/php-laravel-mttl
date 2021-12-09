<?php

namespace Modules\Otc\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;
use Modules\Core\Exceptions\Frontend\Auth\UserAuthVerifyException;
use Modules\Core\Exceptions\Frontend\Auth\UserBankException;
use Modules\Core\Exceptions\Frontend\Auth\UserPayPasswordEmptyException;
use Modules\Core\Exceptions\Frontend\Auth\UserPayPasswordException;
use Modules\Core\Exceptions\ModelSaveException;
use Modules\Otc\Exceptions\OtcTradeException;
use Modules\Otc\Http\Requests\Api\TradePayRequest;
use Modules\Otc\Http\Requests\Api\TradeRequest;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Models\OtcTrade;
use Modules\Otc\Services\OtcTradeService;
use Modules\Otc\Http\Requests\Api\TradeStoreRequest;
use Throwable;

class OtcTradeController extends Controller
{
    /**
     * @param  TradeStoreRequest  $request
     * @param  OtcTradeService  $otcTradeService
     * @return OtcTrade
     * @throws ModelSaveException
     * @throws OtcTradeException
     * @throws Throwable
     * @throws UserAuthVerifyException
     * @throws UserPayPasswordEmptyException
     * @throws UserPayPasswordException
     */
    public function store(TradeStoreRequest $request, OtcTradeService $otcTradeService)
    {
        /** @var OtcTrade $otcTrade */
        $otcTrade = $otcTradeService->create($request->user(), $request->validationData());

        return $otcTrade;
    }

    /**
     * 获取订单列表
     *
     * @param  TradeRequest  $request
     * @param  OtcTradeService  $otcTradeService
     * @return LengthAwarePaginator|Collection
     */
    public function index(TradeRequest $request, OtcTradeService $otcTradeService)
    {
        $where = [];
        //币种
        if ( !empty($request->coin)) {
            $where['coin'] = $request->coin;
        }

        if ( !empty($request->status)) {
            $where['status'] = $request->status;
        }

        //排序方式
        $sort = isset($request->sort) ? $request->sort : 'asc';
        if ( !empty($request->orderBy)) {
            $options['orderBy'] = [$request->orderBy, $sort];
        }

        return $otcTradeService->allByWhere($request->user(), $where);
    }

    /**
     * 支付订单
     *
     * @param  TradePayRequest  $request
     * @param  OtcTradeService  $otcTradeService
     * @return mixed
     * @throws UserBankException
     * @throws OtcTradeException
     */
    public function pay(TradePayRequest $request, OtcTradeService $otcTradeService)
    {
        return $otcTradeService->pay($request->id, $request->user(), $request->validationData(), ['user_pay' => true]);
    }


    /**
     * @param  TradeRequest  $request
     * @param  OtcTradeService  $otcTradeService
     * @return array
     * @throws ModelSaveException
     * @throws OtcTradeException
     * @throws Throwable
     */
    public function confirm(TradeRequest $request, OtcTradeService $otcTradeService)
    {
        return $otcTradeService->confirm($request->id, $request->user(), $request->pay_password);
    }

    public function detail(TradeRequest $request, OtcTradeService $otcTradeService)
    {

        /** @var OtcTrade $otcTrade */
        $otcTrade = OtcTrade::where('id', $request->id)->where(function ($query) use ($request) {
            $query->where('buyer_id', $request->user()->id)
                ->orWhere('seller_id', $request->user()->id);
        })->with([
            'otcExchange',
//            'sellerUser.user.enableBanks',
            'buyerUser.user.certifyPass',
            'buyerUser.user.userInfo',
        ])->first();

        $otcTrade->sellerUser->user->makeHidden([
            'mobile', 'pay_password_updated_at', 'pay_password_set', 'inviter_id', 'email',
        ]);
        $otcTrade->buyerUser->user->makeHidden([
            'mobile', 'pay_password_updated_at', 'pay_password_set', 'inviter_id', 'email',
        ]);
        if ($otcTrade->buyerUser->user->certifyPass) {
            $otcTrade->buyerUser->user->certifyPass->makeHidden(['status', 'number', 'reverse', 'obverse']);
        }
        $otcTrade->buyerUser->user->userInfo->makeHidden(['real_name']);
        $otcTrade->load([
            'sellerUser.user.enableBanks' => function ($query) use ($otcTrade) {
                if ($otcTrade->otcExchange->type === OtcExchange::TYPE_BUY) {
                    $query->whereIn('bank', $otcTrade->otcExchange->bank_list);
                }

                return $query;
            },
        ]);

        return $otcTrade;
    }
}
