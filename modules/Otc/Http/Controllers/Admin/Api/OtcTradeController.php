<?php


namespace Modules\Otc\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\Frontend\UserService;
use Modules\Otc\Services\OtcExchangeService;
use Modules\Otc\Services\OtcTradeService;

class OtcTradeController  extends Controller
{

    public function index(Request $request, OtcTradeService $otcTradeService)
    {

        $where = [];
        $param = $request->input('key');
        if (!empty($param['id'])) {
            $where[] = ['id', '=', $param['id']];
        }
        if (!empty($param['coin'])) {
            $where[] = ['coin', '=', $param['coin']];
        }
        if (!empty($param['type'])) {
            $where[] = ['type', '=', $param['type']];
        }
        if (!empty($param['exchange_type'])) {
            $where[] = ['exchange_type', '=', $param['exchange_type']];
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

        if (!empty($param['seller_user'])) {

            $userService = resolve(UserService::class);
            $userInfo = $userService->one(null, [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('username', $param['seller_user'])
                        ->orWhere('mobile', $param['seller_user'])
                        ->orWhere('id', $param['seller_user'])
                        ->value('id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->id;
            }
            $where[] = ['seller_id', '=', $userId];
        }

        if (!empty($param['buyer_user'])) {

            $userService = resolve(UserService::class);
            $userInfo = $userService->one(null, [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('username', $param['buyer_user'])
                        ->orWhere('mobile', $param['buyer_user'])
                        ->orWhere('id', $param['buyer_user'])
                        ->value('id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->id;
            }
            $where[] = ['buyer_id', '=', $userId];
        }

        $list = $otcTradeService->paginate($where, [
            'orderBy' => ['id', 'desc'],
            'with' => ['buyerUser.user','sellerUser.user']
        ]);

        foreach ($list as &$item) {

            $item->buyer_user_name = $item->buyerUser->user->username;
            $item->buyerUser->user->created_time = date_format($item->buyerUser->user->created_at, 'Y-m-d H:i:s');
            $item->seller_user_name = $item->sellerUser->user->username;
            $item->sellerUser->user->created_time = date_format($item->sellerUser->user->created_at, 'Y-m-d H:i:s');

            $item->exchange_type_text = $item->exchange_type_text;
            $item->type_text = $item->type_text;
            $item->status_text = $item->status_text;
            //$bankList = $item->bank_list_text;
            //$item->bank_list = implode(' ', $bankList);
            $item->deleted_at = empty($item->deleted_at) ? '' : date_format($item->deleted_at, 'Y-m-d H:i:s');
        }

        return $list;
    }

}
