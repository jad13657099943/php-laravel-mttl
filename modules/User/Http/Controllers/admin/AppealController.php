<?php


namespace Modules\User\Http\Controllers\admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\UserAppeal;
use Modules\User\Services\AppealService;

class AppealController extends Controller
{
    public function index()
    {

        return view('user::admin.appeal.index', [
            'state_list' => UserAppeal::$stateMap
        ]);
    }

    public function info(Request $request, AppealService $appealService)
    {

        $id = $request->input('id');
        $info = $appealService->getById($id);
        return view('user::admin.appeal.info', [
            'info' => $info,
            'state_list' => UserAppeal::$stateMap
        ]);
    }
}
