<?php


namespace Modules\User\Http\Controllers\admin;


use Illuminate\Routing\Controller;

class BuyController extends Controller
{
    public function index(){
        return view('user::admin.buy.index');
    }
}
