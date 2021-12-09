<?php


namespace Modules\Otc\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\Frontend\UserService;
use Modules\Otc\Services\OtcExchangeService;

class OtcExchangeController extends Controller
{

    public function index(Request $request, OtcExchangeService $otcExchangeService)
    {

        $where = [];
        $param = $request->input('key');
        if (!empty($param['user_id'])) {
            $where[] = ['user_id', '=', $param['user_id']];
        }
        if (!empty($param['coin'])) {
            $where[] = ['coin', '=', $param['coin']];
        }
        if (!empty($param['type'])) {
            $where[] = ['type', '=', $param['type']];
        }
        if (!empty($param['status'])) {
            $where[] = ['status', '=', $param['status']];
        }
        if (!empty($param['times'])) {
            $time = explode('||', $param['times']);
            $time[0] = date('Y-m-d H:i:s', strtotime($time[0]));
            $time[1] = date('Y-m-d H:i:s', strtotime($time[1]) + 86400);
            $where[] = ['created_at', '>=', [$time[0]]];
            $where[] = ['created_at', '<=', [$time[1]]];
        }

        if (!empty($param['user_info'])) {

            $userService = resolve(UserService::class);
            $userInfo = $userService->one(null, [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('username', $param['user_info'])
                        ->orWhere('mobile', $param['user_info'])->value('id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->id;
            }
            $where[] = ['user_id', '=', $userId];
        }

        $list = $otcExchangeService->paginate($where, [
            'orderBy' => ['id', 'desc'],
            'with' => 'user'
        ]);

        foreach ($list as &$item) {
            $item->user_name = $item->user->username;
            $item->user->created_time = date_format($item->user->created_at, 'Y-m-d H:i:s');
            $item->type_text = $item->type_text;
            $item->status_text = $item->status_text;
            $bankList = $item->bank_list_text;
            $item->bank_list = implode(' ', $bankList);
            $item->deleted_at = empty($item->deleted_at) ? '' : date_format($item->deleted_at, 'Y-m-d H:i:s');
        }

        return $list;
    }
}
