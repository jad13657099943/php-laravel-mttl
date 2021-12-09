<?php


namespace Modules\Otc\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\Frontend\UserService;
use Modules\Otc\Services\OtcUserService;

class OtcUserController extends Controller
{

    public function index(Request $request, OtcUserService $otcUserService)
    {

        //查询参数
        $where = [];
        $param = $request->input('key');
        if (!empty($param['user_id'])) {
            $where[] = ['user_id', '=', $param['user_id']];
        }

        if (!empty($param['user_info'])) {

            $userService = resolve(UserService::class);
            $userInfo = $userService->one(['mobile' => $param['user_info']], [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('username', $param['user_info'])->value('id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->id;
            }
            $where[] = ['user_id', '=', $userId];
        }

        $list = $otcUserService->paginate($where, [
            'orderBy' => ['id', 'desc'],
            'with' => 'user'
        ]);

        foreach ($list as &$item) {
            $item->user_name = $item->user->username;
            $item->buy_state = $item->enable_buy == 1 ? '是' : '否';
            $item->sell_state = $item->enable_sell == 1 ? '是' : '否';
            $item->user->created_time = date_format($item->user->created_at,'Y-m-d H:i:s');
        }

        return $list;

    }
}
