<?php


namespace Modules\User\Http\Controllers\admin;


use Illuminate\Routing\Controller;
use Modules\User\Models\Key;

class KeyController extends Controller
{
    public function index(){
        $list= Key::$type;
        return view('user::admin.key.index',['list'=>$list]);
    }
}
