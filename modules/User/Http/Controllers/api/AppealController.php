<?php


namespace Modules\User\Http\Controllers\api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\UserAppeal;
use Modules\User\Services\AppealService;

class AppealController extends Controller
{

    public function add(Request $request, AppealService $appealService)
    {

        $user = $request->user();
        $type = $request->input('type');
        $message = $request->input('message');
        $images = $request->input('images');
        if (empty($type) || empty($message)) {
            throw new \Exception('请选择申诉类型和填写申诉内容');
        }
        return $appealService->addInfo($user->id, $type, $message, $images);
    }


    public function list(Request $request, AppealService $appealService)
    {

        $user = $request->user();
        return $appealService->list($user->id);
    }

    public function info(Request $request, AppealService $appealService)
    {
        $user = $request->user();
        return $appealService->one([
            'user_id' => $user->id,
            'id' => $request->input('id')
        ]);
    }

    public function typeList(UserAppeal $userAppeal)
    {

        $data = UserAppeal::$typeList;
        return $data;
    }

}
