<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Exception;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinLog;
use Modules\Mttl\Http\Requests\Api\ExchangeCode\ExchangeRequest;
use Modules\Mttl\Models\ExchangeCode;
use Modules\Mttl\Services\ExchangeCodeService;

class ExchangeCodeController extends Controller
{
    /**
     * 兑换码兑换
     * @param ExchangeRequest $exchangeRequest
     * @param ExchangeCodeService $service
     * @return array
     * @throws Exception
     */
    public function exchange(ExchangeRequest $exchangeRequest, ExchangeCodeService $service)
    {
        $data = $exchangeRequest->validationData();
        $code = ExchangeCode::query()
            ->where('code', $data['code'])
            ->where('state', 1)
            ->first();

        if (!$code) {
            throw new Exception('序号错误！');
        }

        if (time() < strtotime($code->effective_time)) {
            throw new Exception('还未到开始兑换的时间！');
        }

        if (time() > strtotime($code->expiration_time)) {
            throw new Exception('该序号已失效！');
        }

        if ($code->counts != 0 && $code->received_count >= $code->counts) {
            throw new Exception('剩余可兑换次数为0！');
        }

        $user_count = CoinLog::query()
            ->where('user_id', with_user_id($exchangeRequest->user()))
            ->where('no', $code->id)
            ->where('action', 'number_exchange')
            ->count();
        if ($code->quota != 0 && $user_count >= $code->quota) {
            throw new Exception('剩余可兑换次数为0！');
        }

        return $service->exchange($code, $exchangeRequest->user(), $user_count);
    }
}
