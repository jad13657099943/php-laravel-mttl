<?php

namespace Modules\Mttl\Http\Controllers\Admin\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Modules\Mttl\Models\ExchangeCode;
use Modules\Mttl\Services\ExchangeCodeService;

class ExchangeCodeController extends Controller
{
    public function index(Request $request)
    {
        $where = [];

        return ExchangeCode::query()
            ->where($where)
            ->orderByDesc('created_at')
            ->limit($request->input('limit', 10))
            ->paginate();
    }

    public function create(Request $request, ExchangeCodeService $service)
    {
        $amount = $request->input('amount');
        $counts = $request->input('counts');
        $quota = $request->input('quota');
        $effective_time = $request->input('effective_time');
        $expiration_time = $request->input('expiration_time');

        if (!$amount || strtotime($expiration_time) <= strtotime($effective_time)) {
            throw new \Exception('请检查输入项！');
        }

        $service->create([
            'code' => $request->input('code') ?: Str::random(16),
            'amount' => $amount,
            'counts' => $counts,
            'quota' => $quota,
            'effective_time' => $effective_time,
            'expiration_time' => $expiration_time
        ]);

        return ['msg' => '添加成功！'];
    }

    public function edit(Request $request, ExchangeCodeService $service)
    {
        $id = $request->input('data_id');
        $counts = $request->input('counts');
        $quota = $request->input('quota');
        $effective_time = $request->input('effective_time');
        $expiration_time = $request->input('expiration_time');
        $state = $request->input('state', 'off');

        $state = $state == 'on' ? 1 : 0;

        ExchangeCode::query()
            ->where('id', $id)
            ->update([
                'counts' => $counts,
                'quota' => $quota,
                'effective_time' => $effective_time,
                'expiration_time' => $expiration_time,
                'state' => $state
            ]);

        return ['msg' => '修改成功'];
    }
}
