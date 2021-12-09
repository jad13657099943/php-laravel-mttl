<?php


namespace Modules\Coin\Http\Controllers\Admin\Api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Coin\Models\CoinUserWallet;
use Modules\Coin\Services\UserWalletService;
use Modules\Core\Services\Frontend\UserService;
use Modules\User\Services\ProjectUserService;

class UserWalletController extends Controller
{

    public function index(Request $request, UserWalletService $service)
    {


        //查询参数
        $where = [];
        $param = $request->input('key');
        if (!empty($param['user_info'])) {

            $userService = resolve(ProjectUserService::class);
            $userInfo = $userService->one(['show_userid' => $param['user_info']], [
                'exception' => false,
                'queryCallback' => function ($query) use ($param) {
                    $query->orWhere('address', $param['user_info'])->value('user_id');
                }
            ]);

            if (!$userInfo) {
                $userId = -1;
            } else {
                $userId = $userInfo->user_id;
            }
            $where[] = ['user_id', '=', $userId];
        }

        $list = $service->paginate($where,[
            'with'=>'projectUser'
        ]);

        foreach ($list as $item) {
            $item->user_name = $item->user ? $item->user->username : '';
        }

        return $list;
    }

    public function edit_wallet(Request $request){
        $param=$request->input();
        CoinUserWallet::query()->where('id',$param['id'])->update(['address'=>$param['address']]);
        return [
          'msg'=>'编辑成功'
        ];
    }

}
