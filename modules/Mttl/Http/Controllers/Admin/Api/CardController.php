<?php

namespace Modules\Mttl\Http\Controllers\Admin\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mttl\Models\EnergyCard;
use Modules\Mttl\Services\EnergyCardService;

class CardController extends Controller
{

    public function index(Request $request)
    {
        $where = [];
        $types = $request->input('types');
        if ($types) {
            $where['types'] = $types;
        }

        return EnergyCard::query()
            ->where($where)
            ->orderByDesc('created_at')
            ->limit($request->input('limit', 10))
            ->paginate();
    }

    public function del(Request $request)
    {
        $id = $request->input('id');

        return EnergyCard::query()->where('id', $id)->delete();
    }

    public function create(Request $request, EnergyCardService $service)
    {
        // 2021-02-03 去除能量卡类型，全部默认为基础能量卡
        $types = $request->input('types', 1);
        $principal = $request->input('principal');
        $buyable_level = $request->input('buyable_level');
        $total_days = $request->input('total_days');
        $daily_rate = $request->input('daily_rate');

        $daily_rate = $daily_rate > 1 ? bcdiv($daily_rate, 100, 4) : $daily_rate;

        $level = [];
        foreach ($buyable_level as $key => $item) {
            $level[] = $key;
        }

        $service->create([
            'types' => $types,
            'principal' => $principal,
            'buyable_level' => $level,
            'total_days' => $total_days,
            'daily_rate' => $daily_rate
        ]);

        return ['msg' => '添加成功！'];
    }

    public function edit(Request $request, EnergyCardService $service)
    {
        $id = $request->input('data_id');
        // 2021-02-03 去除能量卡类型，全部默认为基础能量卡
        $types = $request->input('types', 1);
        $principal = $request->input('principal');
        $buyable_level = $request->input('buyable_level');
        $total_days = $request->input('total_days');
        $daily_rate = $request->input('daily_rate');
        $enable = $request->input('enable', 'off');

        $daily_rate = $daily_rate > 1 ? bcdiv($daily_rate, 100, 4) : $daily_rate;
        $enable = $enable == 'on' ? 1 : 0;

        $level = [];
        foreach ($buyable_level as $key => $item) {
            $level[] = $key;
        }

        EnergyCard::query()
            ->where('id', $id)
            ->update([
                'types' => $types,
                'principal' => $principal,
                'buyable_level' => $level,
                'total_days' => $total_days,
                'daily_rate' => $daily_rate,
                'enable' => $enable
            ]);

        return ['msg' => '修改成功'];
    }
}
