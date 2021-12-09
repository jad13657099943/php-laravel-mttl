<?php


namespace Modules\Mttl\Http\Controllers\Api;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Modules\Coin\Models\CoinWithdraw;
use Modules\Mttl\Services\NewService;

class NewController extends Controller
{

    /**
     * 用户取消提现
     * @param Request $request
     * @param NewService $service
     * @return mixed
     * @throws \Throwable
     */
    public function cancelWithdraw(Request $request,NewService $service){
        $uid=$request->user()['id'];
        $id=$request->id;
       return $service->cancelWithdraw($id,$uid);
    }

    /**
     * 签到奖励
     * @param Request $request
     * @param NewService $service
     * @throws \Throwable
     */
    public function award(Request $request,NewService $service){
        $uid=$request->user()['id'];
        $service->award($uid);
    }

    /**
     * 提交key
     * @param Request $request
     * @param NewService $service
     * @throws \Exception
     */
    public function setKey(Request $request,NewService $service){
        $uid=$request->user()['id'];
        $params=$request->input();
        $service->setKey($uid,$params);
    }

    /**
     * 获取key
     * @param Request $request
     * @param NewService $service
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getKey(Request $request,NewService $service){
        $uid=$request->user()['id'];
        return $service->getKey($uid);
    }

    /**
     * 获取合约状态
     * @param Request $request
     * @param NewService $service
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getCheck(Request $request,NewService $service){
        $uid=$request->user()['id'];
        return $service->getCheck($uid);
    }

    /**
     * 获取合约页面信息
     * @param Request $request
     * @param NewService $service
     * @return array
     */
    public function getMessage(Request $request,NewService $service){
        $uid=$request->user()['id'];
        return $service->getMessage($uid);
    }

    /**
     * 是否点击
     * @param Request $request
     * @param NewService $service
     * @return string[]
     */
    public function subCheckNum(Request $request,NewService $service){
        $uid=$request->user()['id'];
        return $service->subCheckNum($uid);
    }



}
