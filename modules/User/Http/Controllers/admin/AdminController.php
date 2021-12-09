<?php


namespace Modules\User\Http\Controllers\admin;


use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Models\Admin;

class AdminController extends Controller
{
    public function index(){
        return view('user::admin.admin.index');
    }

    public function add(){
        return view('user::admin.admin.add');
    }
    public function edit(Request $request){
        $params=$request->input();
        $list=Admin::query()->where('id',$params['id'])->first();
        return view('user::admin.admin.edit',[
            'list'=>$list
        ]);
    }
}
