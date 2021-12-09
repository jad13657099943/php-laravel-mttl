<?php


namespace Modules\User\Http\Controllers\admin\api;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Models\UserIp;

class IpController extends Controller
{
    public function list(Request $request){
            $show_userid=$request->user;
            $ip=$request->ip;
            $where[]=['id','>',0];
            if ($ip) $where[]=['ip','=',$ip];
            if ($show_userid) $where[]=['show_userid','=',$show_userid];
            return  UserIp::query()->where($where)->orderBy('id','desc')->paginate($request->limit);
        }

}
