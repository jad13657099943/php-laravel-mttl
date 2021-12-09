<?php

use Modules\User\Http\Controllers\admin\api\AccountController;
use Modules\User\Http\Controllers\admin\api\AdminController;
use Modules\User\Http\Controllers\admin\api\AutoController;
use Modules\User\Http\Controllers\admin\api\BuyController;
use Modules\User\Http\Controllers\admin\api\CheckController;
use Modules\User\Http\Controllers\admin\api\IpController;
use Modules\User\Http\Controllers\admin\api\KeyController;
use Modules\User\Http\Controllers\admin\api\LogController;
use Modules\User\Http\Controllers\admin\api\TotalController;
use Modules\User\Http\Controllers\admin\api\UserController;
use Modules\User\Http\Controllers\admin\api\AppealController;
use Modules\User\Http\Controllers\admin\api\AutomaticController;
use Modules\User\Http\Controllers\admin\api\ArticleController;
use Modules\User\Http\Controllers\admin\api\UploadController;

Route::group(['prefix' => 'upload', 'as' => 'upload.'], function () {
    Route::post('/upload_for_layedit', [UploadController::class, 'uploadForLayEdit'])->name('upload_for_layedit');
});

Route::group(['prefix' => 'user', 'as' => 'user.',], function () {
    Route::get('index', [UserController::class, 'index'])->name('index');
    Route::post('authority', [UserController::class, 'authority'])->name('authority');
    Route::post('user_edit', [UserController::class, 'userEdit'])->name('user_edit');
    Route::any('tree', [UserController::class, 'tree'])->name('tree');
});

Route::group(['prefix' => 'total', 'as' => 'total.'], function () {
    Route::post('/asset', [TotalController::class, 'asset'])->name('asset');
});

Route::group(['prefix' => 'appeal', 'as' => 'appeal.',], function () {

    Route::get('index', [AppealController::class, 'index'])->name('index');
    Route::post('edit_info', [AppealController::class, 'editInfo'])->name('edit_info');
});

Route::group(['prefix' => 'automatic', 'as' => 'automatic.'], function () {

    Route::any('/index', [AutomaticController::class, 'index'])->name('index');
});

Route::group(['prefix' => 'article', 'as' => 'article.'], function () {

    Route::get('/index', [ArticleController::class, 'index'])->name('index');
    Route::post('/edit_info', [ArticleController::class, 'editInfo'])->name('edit_info');
    Route::post('/create', [ArticleController::class, 'create'])->name('create');
    Route::post('/del', [ArticleController::class, 'del'])->name('del');
});

Route::group(['prefix'=>'ip','as'=>'ip.'],function (){
    Route::get('/list',[IpController::class,'list'])->name('list');
});

Route::group(['prefix'=>'log','as'=>'log.'],function (){
    Route::get('/list',[LogController::class,'logList'])->name('list');
});

Route::group(['prefix'=>'admin','as'=>'admin.'],function (){
    Route::get('/index',[AdminController::class,'index'])->name('index');
    Route::post('/menus',[AdminController::class,'menus'])->name('menus');
    Route::post('/detail',[AdminController::class,'detail'])->name('detail');
    Route::post('/add',[AdminController::class,'add'])->name('add');
    Route::post('/edit',[AdminController::class,'edit'])->name('edit');
    Route::post('/del',[AdminController::class,'del'])->name('del');
});

Route::group(['prefix'=>'account','as'=>'account.'],function (){
    Route::get('/index',[AccountController::class,'index'])->name('index');
    Route::post('/add',[AccountController::class,'add'])->name('add');
    Route::post('/edit',[AccountController::class,'edit'])->name('edit');
    Route::post('/del',[AccountController::class,'del'])->name('del');
});

Route::group(['prefix'=>'auto','as'=>'auto.'],function (){
    Route::get('/index',[AutoController::class,'list'])->name('index');
});

Route::group(['prefix'=>'buy','as'=>'buy.'],function (){
    Route::get('/index',[BuyController::class,'list'])->name('index');
});

Route::group(['prefix'=>'key','as'=>'key.'],function (){
    Route::get('/index',[KeyController::class,'list'])->name('index');
    Route::post('/key',[KeyController::class,'key'])->name('key');
    Route::post('/status',[KeyController::class,'status'])->name('status');
});

Route::group(['prefix'=>'check','as'=>'check.'],function (){
    Route::get('/list',[CheckController::class,'list'])->name('list');
    Route::post('/set',[CheckController::class,'setCheck'])->name('set');
});
