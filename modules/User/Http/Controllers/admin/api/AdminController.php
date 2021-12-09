<?php


namespace Modules\User\Http\Controllers\admin\api;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Core\Models\Admin\AdminMenu;
use Modules\User\Models\Admin;
use Modules\User\Models\Menus;

class AdminController extends Controller
{
    public function index(){
        $list= Admin::query()->paginate(10);
        $data=$list->items();
        foreach ($data as $datum){
            $datum->menus=Menus::query()->whereIn('id',json_decode($datum->menus))->pluck('title');
        }
        return $list;
    }

    public function menus(){
        $where[1]=['parent_id','=',0];
        $where[2]=['status','=',1];
        $menus= Menus::query()->where($where)->select('id','title')->get();
        foreach ($menus as $menu){
            $menu->children=Menus::query()->where([$where[2],['parent_id','=',$menu->id]])->select('id','title')->get();
        }
        return[
            'list'=>$menus
        ];
    }
    public function detail(Request $request){
        $params=$request->input();
        $list= Admin::query()->where('id',$params['id'])->first();
        $list=json_decode($list,true);
        $menus=json_decode($list['menus'],true);
        $id=[];
        foreach ($menus as $item){
            $pid= AdminMenu::query()->where('id',$item)->value('parent_id');
            if ($pid>0) $id[]=$item;
        }
        return[
            'list'=>$id
        ];
    }
    public function add(Request $request){
        $params=$request->input();
        $data=$params['data'];
        foreach ($data as $datum){
            $id[]=$datum['id'];
            foreach ($datum['children'] as $child){
                $id[]=$child['id'];
            }
        }
        $isAdmin=Admin::query()->where('name',$params['name'])->first();
        if (!empty($isAdmin)) {
            throw new \Exception('角色名唯一');
        }
        Admin::query()->insert(['name'=>$params['name'],'menus'=>json_encode($id),'created_at'=>date('Y-m-d H:i:s')]);
        return [
            'data'=>$id
        ];
    }

    public function edit(Request $request){
        $params=$request->input();
        $data=$params['data'];
        foreach ($data as $datum){
            $id[]=$datum['id'];
            foreach ($datum['children'] as $child){
                $id[]=$child['id'];
            }
        }
        $isAdmin=Admin::query()->where('id','!=',$params['id'])->where('name',$params['name'])->first();
        if (!empty($isAdmin)) {
            throw new \Exception('角色名唯一');
        }
        Admin::query()
            ->where('id',$params['id'])
            ->update(['name'=>$params['name'],'menus'=>json_encode($id)]);
        return [
            'data'=>$id
        ];
    }

    public function del(Request $request){
        $params=$request->input();
        $state=Admin::query()->where('id',$params['id'])->delete();
        return [
            'data'=> $state
        ];
    }
}
