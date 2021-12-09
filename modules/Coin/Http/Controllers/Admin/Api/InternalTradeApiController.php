<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Services\InternalTradeService;
use Modules\Core\Services\Frontend\UserService;
use Modules\User\Services\ProjectUserService;

class InternalTradeApiController extends Controller
{

    public function index(Request $request, InternalTradeService $service)
    {


        //查询参数
        $where = [];
        $param = $request->input('key');
        if (!empty($param['times'])) {
            $time = explode('||', $param['times']);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            $where[] = ['created_at', '>=', [$time[0]]];
            $where[] = ['created_at', '<=', [$time[1]]];
        }
        if (!empty($param['symbol'])) {
            $where[] = ['symbol', '=', $param['symbol']];
        }

        if (!empty($param['id'])) {
            $where[] = ['id', '=', $param['id']];
        }

        if (!empty($param['from_user'])) {

            $userService = resolve(ProjectUserService::class);
            $userInfo = $userService->one(['show_userid' => $param['from_user']], [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('address', $param['from_user'])->value('user_id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->user_id;
            }
            $where[] = ['from_user_id', '=', $userId];
        }

        if (!empty($param['to_user'])) {

            $userService = resolve(ProjectUserService::class);
            $userInfo = $userService->one(['show_userid' => $param['to_user']], [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('address', $param['to_user'])->value('user_id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->user_id;
            }
            $where[] = ['to_user_id', '=', $userId];
        }


        $list = $service->paginate($where, [
            'with' => ['to_user', 'from_user'],
            'orderBy' => ['id', 'desc'],
        ]);


        foreach ($list as $item) {

            //$users = $service->infoUsers($item->id);
            $item->from_user_data = $item->from_user->show_userid;
            $item->to_user_data = $item->to_user->show_userid;
        }

        return $list;

    }

}
