<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Mttl\Http\Requests\Api\EnergyCardBuy\BuyRequest;
use Modules\Mttl\Services\EnergyCardBuyService;
use Modules\Mttl\Services\EnergyCardService;

class EnergyCardBuyController extends Controller
{
    /**
     * 购买能量卡
     * @param BuyRequest $request
     * @param EnergyCardBuyService $service
     * @return array
     * @throws
     */
    public function add(BuyRequest $request, EnergyCardBuyService $service)
    {
        $data = $request->validationData();
        $cardService = resolve(EnergyCardService::class);
        $card = $cardService->one($request->user(), $data['crad_id']);
        return $service->add($request->user(), $card, $data['automatic'], ['transaction' => true]);
    }

    /**
     * 我的能量卡
     * @param Request $request
     * @param EnergyCardBuyService $service
     * @return LengthAwarePaginator
     */
    public function my(Request $request, EnergyCardBuyService $service)
    {
        return $service->my($request->user());
    }
}
