<?php


namespace Modules\User\Http\Controllers\admin;




use Illuminate\Routing\Controller;
use Modules\User\Models\Log;

class LogController extends Controller
{
    public function index(){

        return view('user::admin.log.index',['list'=>Log::$state]);
    }
}
