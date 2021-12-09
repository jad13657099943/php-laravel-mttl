<?php

namespace Modules\Otc\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Otc\Models\OtcCoin;
use Modules\Otc\Services\OtcCoinService;

class OtcCoinController extends Controller
{
    public function index(Request $request, OtcCoinService $otcCoinService)
    {
        return $otcCoinService->all([
            'is_enable' => OtcCoin::ENABLE
        ], [
            'orderBy' => ['sort', 'asc']
        ]);
    }
}
