<?php

use Modules\Web3\Http\Controllers\Web3Controller;
// use Modules\Web3\Http\Controllers\Admin\DefaultController;


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
Route::get('/', [Web3Controller::class, 'index'])->name('index');
Route::get('/tron', [Web3Controller::class, 'tron'])->name('tron');

// 后台路由
/*
Route::group([
    'middleware' => ['admin']
    'as' => 'admin.'
], function() {

    Route::get('/default', [DefaultController::class, 'index'])->name('home');

});
*/
