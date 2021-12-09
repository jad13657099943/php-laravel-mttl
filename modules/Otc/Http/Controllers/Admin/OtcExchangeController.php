<?php

namespace Modules\Otc\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Otc\Models\OtcExchange;
use Modules\Otc\Services\OtcCoinService;
use Modules\Otc\Services\OtcExchangeService;

class OtcExchangeController extends Controller
{
    /**
     * Display a listing of the resource.
     * @param OtcExchange $otcExchange
     * @param OtcCoinService $otcCoinService
     * @return Response
     */
    public function index(OtcExchange $otcExchange, OtcCoinService $otcCoinService)
    {

        $type = $otcExchange::$typeMap;
        $status = $otcExchange::$statusMap;
        $coin = $otcCoinService->all(null, [
            'exception' => false
        ])->pluck('coin');

        return view('otc::admin.otc_exchange.index', [
            'type' => $type,
            'status' => $status,
            'coin' => $coin
        ]);
    }


}
