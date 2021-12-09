<?php


namespace Modules\User\Http\Controllers\admin;


use App\Http\Controllers\Controller;
use Modules\User\Models\ProjectUser;

class CheckController extends Controller
{
    public function index(){
        $list=ProjectUser::$type;
        return view('user::admin.check.index',['list'=>$list]);
    }
}
