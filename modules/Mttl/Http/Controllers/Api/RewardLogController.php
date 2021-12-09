<?php

namespace Modules\Mttl\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Mttl\Http\Requests\Api\Reward\DetailRequest;
use Modules\Mttl\Services\RewardLogService;

class RewardLogController extends Controller
{

    /**
     * 奖励首页
     * @param Request $request
     * @param RewardLogService $service
     * @return array
     */
    public function index(Request $request, RewardLogService $service)
    {
        // 总奖励（今日）
        $todayAmount = $service->query()
            ->where('user_id', with_user_id($request->user()))
            ->whereDate('created_at', date('Y-m-d'))
            ->sum('amount');

        // 总奖励（7天）
        $weekAmount = $service->query()
            ->where('user_id', with_user_id($request->user()))
            ->whereBetween('created_at', [
                date('Y-m-d 00:00:00', strtotime('-7 day')),
                date('Y-m-d 23:59:59')
            ])
            ->sum('amount');

        return [
            'todayAmount' => $todayAmount,
            'weekAmount' => $weekAmount
        ];
    }

    /**
     * 奖励明细
     * @param DetailRequest $request
     * @param RewardLogService $service
     * @return array
     */
    public function detail(DetailRequest $request, RewardLogService $service)
    {
        $data = $request->validationData();

        $where = [
            ['user_id', '=', with_user_id($request->user())]
        ];

        if ($data['type'] !== 'today') {
            $where[] = ['type', '=', $data['type']];
        } else {
            if (!isset($data['date']))
                $data['date'] = date('m-d');
        }

        if (!empty($data['start_time'])&&!empty($data['end_time'])){
            $where[]=['created_at','>=',$data['start_time']];
            $where[]=['created_at','<=',$data['end_time']];
        }

        if (!isset($data['id'])) $data['id']='desc';
        if ($data['id']=='desc'){
            $list = $service->paginate($where, [
                'orderBy' => ['id', 'desc'],
                'queryCallback' => function ($query) use ($data) {
                    if (isset($data['date'])) {
                        $query->whereDate('created_at', date('Y') . '-' . $data['date']);
                    }
                }
            ])->toArray();
        }else{
            $list = $service->paginate($where, [
                'orderBy' => ['id', 'asc'],
                'queryCallback' => function ($query) use ($data) {
                    if (isset($data['date'])) {
                        $query->whereDate('created_at', date('Y') . '-' . $data['date']);
                    }
                }
            ])->toArray();
        }
        $total_amount = $service->sum('amount', $where, [
            'queryCallback' => function ($query) use ($data) {
                if (isset($data['date'])) {
                    $query->whereDate('created_at', date('Y') . '-' . $data['date']);
                }
            }
        ]);

        $list['total_amount'] = $total_amount;

        return $list;
    }
}
