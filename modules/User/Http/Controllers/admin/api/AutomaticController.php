<?php

namespace Modules\User\Http\Controllers\admin\api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Mttl\Services\EnergyCardAutomaticService;
use Modules\User\Services\ProjectUserService;

class AutomaticController extends Controller
{

    public function index(Request $request, EnergyCardAutomaticService $service)
    {
        $where = [];
        if (!empty($request->state)) {
            if ($request->state == 1) {
                $where[] = ['automatic', '=', 1];
            } else {
                $where[] = ['automatic', '=', 0];
            }
        }

        if (!empty($request->types)) {
            $where[] = ['types', '=', $request->types];
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
            'orderBy' => ['id', 'desc']
        ]);
    }
}
