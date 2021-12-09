<?php


namespace Modules\User\Http\Controllers\admin\api;


use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\User\Models\Admin;
use Modules\User\Models\AdminU;

class AccountController extends Controller
{
    public function index(){
        $list= AdminU::query()->paginate(10);
        $data=$list->items();
        foreach ($data as $item){
            $item->admin=Admin::query()->where('id',$item->admin_id)->value('name');
        }
        return $list;
    }

    public function add(Request $request){
        $params=$request->input();
        $isAdminU=AdminU::query()->where('username',$params['username'])->first();
        if (!empty($isAdminU)){
            throw new \Exception('账号唯一');
        }
        $params['password']=Hash::make($params['password']);
        $params['created_at']=date('Y-m-d H:i:s');
        $state=AdminU::query()->insert($params);
        return [
            'data'=>$state
        ];
    }

    public  function del(Request $request){
        $params=$request->input();
        if ($params['id']!=1){
            AdminU::query()->where('id',$params['id'])->delete();
        }
        return [
            'data'=>true
        ];
    }

    public function edit(Request $request){
        $params=$request->input();
        $isAdminU=AdminU::query()->where('id','!=',$params['id'])->where('username',$params['username'])->first();
        if (!empty($isAdminU)){
            throw new \Exception('账号唯一');
        }
        if ($params['id']==1){
            throw new \Exception('无法操作该账号');
        }
        $id=$params['id'];
        if (!empty($params['password'])) {
            $params['password']=Hash::make($params['password']);
        }else{
            unset($params['password']);
        }
        if (empty($params['admin_id'])) unset($params['admin_id']);
        AdminU::query()->where('id',$id)->update($params);
        return [
            'data'=>true
        ];
    }
}
