<?php

namespace Modules\Coin\Http\Controllers\Api;

use Modules\Coin\Http\Requests\InternalTradeAddRequest;
use Modules\Coin\Services\InternalTradeService;

class InternalTradeController
{
    /**
     * 提交内部转账请求
     */
    public function create(InternalTradeAddRequest $request, InternalTradeService $internalTradeService)
    {
        return $internalTradeService->create(array_merge([
            'user_id' => $request->user()
        ], $request->validationData()), [
            'checkPayPassword' => false
        ]);
    }
}
