<?php

namespace Modules\Mttl\Http\Controllers\Admin\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Mttl\Services\EnergyCardBuyService;
use Modules\User\Models\ProjectUser;
use Modules\User\Services\ProjectUserService;

class CardBuyController extends Controller
{
    public function index(Request $request, EnergyCardBuyService $service)
    {
        $where = [];

        if (!empty($request->state)) {
            if ($request->state == 1) {
                $where[] = ['surplus_days', '>', 0];
            } else {
                $where[] = ['surplus_days', '=', 0];
            }
        }

        if (!empty($request->types)) {
            $where[] = ['types', '=', $request->types];
        }

        if (!empty($request->created_at)) {
            $time = explode('||', $request->created_at);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            $where[] = ['created_at', '>=', [$time[0]]];
            $where[] = ['created_at', '<=', [$time[1]]];
        }

        if (!empty($request->user_id)) {
            $userService = resolve(ProjectUserService::class);
            $userInfo = $userService->one(null, [
                'exception' => false,
                'queryCallback' => function ($query) use ($request) {
                    $query->orWhere('show_userid', $request['user_id'])
                        ->value('user_id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->user_id;
            }
            $where[] = ['user_id', '=', $userId];

        }

        return $service->paginate($where, [
            'with' => 'user',
            'orderBy' => ['created_at', 'desc'],
            'queryCallback' => function (Builder $query) use ($request) {
                if (!empty($request->team_mark)) {
                    $userInfo = ProjectUser::query()
                        ->where('show_userid', 'like', $request->team_mark . '%')
                        ->pluck('user_id')
                        ->toArray();

                    return $query->whereIn('user_id', $userInfo);
                }
            }
        ]);
    }
}
