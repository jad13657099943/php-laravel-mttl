<?php

namespace Modules\Coinv2\Http\Controllers\Api;

use Modules\Coinv2\Http\Requests\InternalTradeAddRequest;
use Modules\Coinv2\Services\InternalTradeService;

class InternalTradeController
{
    /**
     * 提交内部转账请求
     */
    public function create(InternalTradeAddRequest $request, InternalTradeService $internalTradeService)
    {
        return $internalTradeService->create(array_merge([
            'user_id' => $request->user()
        ], $request->validationData()));
    }
}
