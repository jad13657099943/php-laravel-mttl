<?php

namespace Modules\Coin\Http\Controllers\Api;

use Illuminate\Http\Request;
use Modules\Coin\Http\Controllers\Controller;
use Modules\Coin\Services\CoinLogService;

class CoinLogController extends Controller
{
    public function index(Request $request, CoinLogService $coinLogService)
    {
        return $coinLogService->all(['user_id' => $request->user()->id], ['paginate' => true]);
    }
}
