<?php


namespace Modules\User\Http\Controllers\web;


use Illuminate\Routing\Controller;

class RegisterController extends Controller
{
    public function index()
    {

        $mobilePre[] = ['pre'=>'+86'];
        return view('user::register', [
            'mobile_pre' => $mobilePre
        ]);
    }
}
