<?php

namespace Modules\Otc\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Otc\Http\Requests\Api\TradeAppealStoreRequest;
use Modules\Otc\Services\OtcTradeAppealService;

class OtcTradeAppealController extends Controller
{
    /**
     * @param TradeAppealStoreRequest $request
     * @param OtcTradeAppealService $otcTradeAppealService
     * @return mixed
     * @throws \Modules\Otc\Exceptions\OtcTradeException
     */
    public function store(TradeAppealStoreRequest $request, OtcTradeAppealService $otcTradeAppealService)
    {
        $result = $otcTradeAppealService->create($request->user(), array_merge($request->validationData(), ['otc_trade_id' => $request->id]));
        $result->status_text = $result->statusText;
        $result->type_text = $result->typeText;
        return $result;
    }

    /**
     * @param Request $request
     * @param OtcTradeAppealService $otcTradeAppealService
     * @return \Illuminate\Database\Eloquent\Model|mixed
     */
    public function info(Request $request, OtcTradeAppealService $otcTradeAppealService)
    {
        return $otcTradeAppealService->getByUserAndOtcTrade($request->user(), $request->id);
    }
}
