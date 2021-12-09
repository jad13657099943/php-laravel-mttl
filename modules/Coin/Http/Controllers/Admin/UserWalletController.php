<?php


namespace Modules\Coin\Http\Controllers\Admin;


use Illuminate\Routing\Controller;

class UserWalletController extends Controller
{

    public function index(){

        return view('coin::admin.user_wallet.index');
    }

}
