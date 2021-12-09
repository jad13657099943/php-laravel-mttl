<?php


namespace Modules\User\Http\Controllers\admin\api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Mttl\Models\AutoLog;
use Modules\User\Models\ProjectUser;

class AutoController extends Controller
{
    public function list(Request $request){
        $where=[];
        $user=$request->user;
        if ($user){
            $user_id=ProjectUser::query()->where('show_userid',$user)->value('user_id');
            $where[]=['user_id','=',$user_id];
        }
        $list=AutoLog::query()->where($where)->with('user')->orderBy('id','desc')->paginate($request->limit??10);
        foreach ($list->items() as $item){
            $item->show=$item->user['show_userid'];
            $item->type_text=AutoLog::$state[$item->type];
        }
        return $list;
    }
}
