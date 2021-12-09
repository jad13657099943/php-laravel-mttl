<?php


namespace Modules\User\Http\Controllers\admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\User\Models\UserIp;

class IpController extends Controller
{
    public function index(){
        return view('user::admin.ip.index');
    }
}
