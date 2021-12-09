<?php


namespace Modules\Otc\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Services\Frontend\UserService;
use Modules\Otc\Services\OtcExchangeService;
use Modules\Otc\Services\OtcTradeAppealService;

class OtcTradeAppealController extends Controller
{

    public function index(Request $request, OtcTradeAppealService $otcTradeAppealService)
    {

        $where = [];
        $param = $request->input('key');
        if (!empty($param['user_id'])) {
            $where[] = ['user_id', '=', $param['user_id']];
        }
        if (!empty($param['user_type'])) {
            $where[] = ['user_type', '=', $param['user_type']];
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

        $list = $otcTradeAppealService->paginate($where, [
            'orderBy' => ['id', 'desc'],
            'with' => 'user'
        ]);

        foreach ($list as &$item) {
            $item->user_name = $item->user->username;
            $item->user->created_time = date_format($item->user->created_at, 'Y-m-d H:i:s');
            $item->type_text = $item->type_text;
            $item->status_text = $item->status_text;
            $item->user_type_text = $item->user_type_text;

        }

        return $list;
    }


    /**
     * 处理申诉
     * @param Request $request
     * @param OtcTradeAppealService $otcTradeAppealService
     * @return string[]
     * @throws \Exception
     */
    public function edit(Request $request, OtcTradeAppealService $otcTradeAppealService)
    {

        $id = $request->input('id');
        $handle = $request->input('handle');
        $status = $request->input('status', 1);
        $info = $otcTradeAppealService->getById($id);
        if ($info->status != -1) {
            throw new \Exception('该信息状态不能提交处理');
        }
        if ($status == 1 && empty($handle)) {
            throw new \Exception('请输入驳回处理说明...');
        }
        $info->handle = $handle;
        $info->status = $status;
        $info->save();

        return ['msg' => '处理成功'];
    }


}
