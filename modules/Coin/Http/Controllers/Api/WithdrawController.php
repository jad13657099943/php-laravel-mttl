<?php

namespace Modules\Coin\Http\Controllers\Api;


use Illuminate\Http\Request;
use Modules\Coin\Http\Requests\WithdrawAddRequest;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Coin\Services\WithdrawService;

class WithdrawController
{
    /**
     * 提交提现请求
     */
    public function create(WithdrawAddRequest $request, WithdrawService $withdrawService)
    {
        return $withdrawService->create(array_merge($request->validationData(), ['user_id' => $request->user()->id]));
    }


    /**
     * 会员提现列表
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function userWithdrawList(Request $request)
    {

        $user = $request->user();
        $symbol = $request->input('symbol');
        if ($symbol) {
            $where[] = ['symbol', '=', strtoupper($symbol)];
        }
        $where[] = ['user_id', '=', $user->id];
        $list = CoinWithdraw::query()->where($where)
            ->orderBy('id', 'desc')
            ->paginate();
        foreach ($list as $item) {
            $item->state_text = $item->state_text;
        }
        return $list;
    }

}
