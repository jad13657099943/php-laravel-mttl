<?php


namespace Modules\User\Http\Controllers\admin;




use Illuminate\Routing\Controller;

class AutoController extends Controller
{
    public function index(){
        return view('user::admin.auto.index');
    }
}
