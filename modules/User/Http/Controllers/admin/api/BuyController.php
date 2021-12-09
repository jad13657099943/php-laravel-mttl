<?php


namespace Modules\User\Http\Controllers\admin\api;


use Illuminate\Http\Request;
use Modules\Mttl\Models\AutoLog;
use Modules\User\Models\BuyLog;
use Modules\User\Models\ProjectUser;

class BuyController
{

    public function list(Request $request){
        $where=[];
        $user=$request->user;
        if ($user){
            $user_id=ProjectUser::query()->where('show_userid',$user)->value('user_id');
            $where[]=['user_id','=',$user_id];
        }
        $list=BuyLog::query()->where($where)->with('user')->orderBy('id','desc')->paginate($request->limit??10);
        foreach ($list->items() as $item){
            $item->show=$item->user['show_userid'];
            $item->type_text=BuyLog::$state[$item->type];
        }
        return $list;
    }
}
