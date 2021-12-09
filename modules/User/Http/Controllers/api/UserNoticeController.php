<?php


namespace Modules\User\Http\Controllers\api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\UserNotice;

class UserNoticeController extends Controller
{

    public function state(Request $request)
    {

        $userId = $request->user()->id;
        $num = UserNotice::query()->where('user_id', $userId)
            ->where('state', 0)
            ->count();
        return [
            'num' => $num
        ];
    }


    /**
     * 我的消息列表
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function list(Request $request)
    {

        $user = $request->user();
        $list = UserNotice::query()->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate($request->limit);

        foreach ($list as $item) {
            $item->state_text = $item->state;
            $item->type_text = $item->type;
        }

        //更新所有未读消息为已读
        UserNotice::query()->where('user_id', $user->id)
            ->where('state', 0)
            ->update([
                'state' => 1
            ]);

        return $list;
    }


    public function info(Request $request)
    {

        $userId = $request->user()->id;
        $id = $request->input('id');
        $info = UserNotice::query()->where('user_id', $userId)
            ->where('id', $id)
            ->first();
        if ($info && $info->state == 0) {
            $info->state = 1;
            $info->save();
        }

        $info->state_text = $info->state;
        $info->type_text = $info->type;
        return $info;
    }

}
