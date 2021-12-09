<?php

//use Modules\User\Http\Controllers\UserController;

use Modules\Core\Http\Middleware\UseGuard;
use Modules\User\Http\Controllers\admin\AccountController;
use Modules\User\Http\Controllers\admin\AdminController;
use Modules\User\Http\Controllers\admin\AutoController;
use Modules\User\Http\Controllers\admin\AutomaticController;
use Modules\User\Http\Controllers\admin\BuyController;
use Modules\User\Http\Controllers\admin\CheckController;
use Modules\User\Http\Controllers\admin\IpController;
use Modules\User\Http\Controllers\admin\KeyController;
use Modules\User\Http\Controllers\admin\LogController;
use Modules\User\Http\Controllers\admin\UserController;
use Modules\User\Http\Controllers\admin\TotalController;
use Modules\User\Http\Controllers\admin\SettingController;
use Modules\User\Http\Controllers\admin\AppealController;
use Modules\User\Http\Controllers\admin\ArticleController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 前台路由
Route::get('/', [UserController::class, 'index'])->name('index');

// 后台路由

Route::group([
    'namespace' => 'Admin',
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => [UseGuard::class . ':admin'],
], function () {

    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {

        Route::get('/parent_all', [UserController::class, 'parentAll'])->name('parent_all');
        Route::get('/index', [UserController::class, 'index'])->name('index');
        Route::get('/authority', [UserController::class, 'authority'])->name('authority');
        Route::get('/edit_user', [UserController::class, 'userEdit'])->name('edit_user');
        Route::get('/tree', [UserController::class, 'tree'])->name('tree');
    });

    Route::group(['prefix' => 'total', 'as' => 'total.'], function () {

        Route::get('/total', [TotalController::class, 'total'])->name('total');
        Route::get('/asset', [TotalController::class, 'asset'])->name('asset');
    });

    Route::group(['prefix' => 'setting', 'as' => 'setting.'], function () {

        Route::get('/index', [SettingController::class, 'index'])->name('index');
        Route::post('/update', [SettingController::class, 'update'])->name('update');
    });

    Route::group(['prefix' => 'appeal', 'as' => 'appeal.'], function () {
        Route::get('/index', [AppealController::class, 'index'])->name('index');
        Route::get('/info', [AppealController::class, 'info'])->name('info');
    });

    Route::group(['prefix' => 'automatic', 'as' => 'automatic.'], function () {
        Route::get('/index', [AutomaticController::class, 'index'])->name('index');
    });

    Route::group(['prefix' => 'article', 'as' => 'article.'], function () {
        Route::get('/index', [ArticleController::class, 'index'])->name('index');
        Route::get('/edit_info', [ArticleController::class, 'editInfo'])->name('edit_info');
        Route::get('/create', [ArticleController::class, 'create'])->name('create');
    });

    Route::group(['prefix'=>'ip','as'=>'ip.'],function (){
        Route::get('/index',[IpController::class,'index'])->name('index');
    });

    Route::group(['prefix'=>'log','as'=>'log.'],function (){
        Route::get('/index',[LogController::class,'index'])->name('index');
    });

    Route::group(['prefix'=>'admin','as'=>'admin.'],function (){
        Route::get('/index',[AdminController::class,'index'])->name('index');
        Route::get('/add',[AdminController::class,'add'])->name('add');
        Route::get('/edit',[AdminController::class,'edit'])->name('edit');
    });

    Route::group(['prefix'=>'account','as'=>'account.'],function (){
        Route::get('/index',[AccountController::class,'index'])->name('index');
        Route::get('/edit',[AccountController::class,'edit'])->name('edit');
        Route::get('/add',[AccountController::class,'add'])->name('add');
    });

    Route::group(['prefix'=>'auto','as'=>'auto.'],function (){
        Route::get('/index',[AutoController::class,'index'])->name('index');
    });

    Route::group(['prefix'=>'buy','as'=>'buy.'],function (){
        Route::get('/index',[BuyController::class,'index'])->name('index');
    });

    Route::group(['prefix'=>'key','as'=>'key.'],function (){
        Route::get('/index',[KeyController::class,'index'])->name('index');
    });

    Route::group(['prefix'=>'check','as'=>'check.'],function (){
        Route::get('/index',[CheckController::class,'index'])->name('index');
    });
});
