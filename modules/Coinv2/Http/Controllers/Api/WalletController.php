<?php

namespace Modules\Coinv2\Http\Controllers\Api;

use Modules\Coin\Http\Controllers\Controller;
use Modules\Coinv2\Http\Requests\WalletChainRequest;
use Modules\Coinv2\Services\UserWalletService;

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
