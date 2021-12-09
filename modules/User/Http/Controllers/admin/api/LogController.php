<?php


namespace Modules\User\Http\Controllers\admin\api;


use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\Log;
use Modules\User\Models\ProjectUser;

class LogController extends Controller
{

    public function logList(Request $request){
       $admin=$request->admin;
       $user=$request->user;
       $type=$request->type;
       $where[]=['id','>',0];
       if ($admin){
           $admin_id= AdminUser::query()->where('username',$admin)->value('id');
           $where[]=['admin_id','=',$admin_id];
       }
       if ($user){
           $user_id=ProjectUser::query()->where('show_userid',$user)->value('user_id');
           $where[]=['user_id','=',$user_id];
       }
       if ($type){
           $where[]=['type','=',$type];
       }

       $list=Log::query()->where($where)->with('admin','user')->orderBy('id','desc')->paginate($request->limit??10);
       foreach ($list->items() as $item){
         $item->admin_text=$item->admin['username'];
         $item->user_text=$item->user['show_userid'];
         $item->type_text=Log::$state[$item->type];
       }
       return $list;
    }
}
