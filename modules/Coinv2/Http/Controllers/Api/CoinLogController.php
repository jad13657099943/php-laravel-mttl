<?php

namespace Modules\Coinv2\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coinv2\Services\CoinLogService;

class CoinLogController extends Controller
{
    public function index(Request $request, CoinLogService $coinLogService)
    {
        return $coinLogService->all(['user_id' => $request->user()->id], ['paginate' => true]);
    }
}
