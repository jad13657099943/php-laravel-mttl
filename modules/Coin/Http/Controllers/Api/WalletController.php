<?php

namespace Modules\Coin\Http\Controllers\Api;

use Modules\Coin\Http\Controllers\Controller;
use Modules\Coin\Http\Requests\WalletChainRequest;
use Modules\Coin\Services\UserWalletService;

class WalletController extends Controller
{
    /**
     * 拉取用户钱包地址
     */
    public function getByChain(WalletChainRequest $request, UserWalletService $userWalletService)
    {
        return $userWalletService->getByChain($request->user(), $request->chain);
    }
}
