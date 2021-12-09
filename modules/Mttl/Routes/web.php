<?php

use Modules\Mttl\Http\Controllers\Admin\CardBuyController;
use Modules\Mttl\Http\Controllers\Admin\CardController;
use Modules\Mttl\Http\Controllers\Admin\ExchangeCodeController;
use Modules\Mttl\Http\Controllers\MttlController;

// use Modules\Mttl\Http\Controllers\Admin\DefaultController;


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
Route::get('/', [MttlController::class, 'index'])->name('index');
Route::get('/trade', [MttlController::class, 'trade'])->name('trade');

// 后台路由
Route::group([
    'middleware' => ['auth:admin'],
    'as' => 'admin.',
    'prefix' => 'admin',
    'namespace' => 'Admin',
], function () {
    Route::group([
        'prefix' => 'card',
        'as' => 'card.',
    ], function () {
        Route::get('/index', [CardController::class, 'index'])->name('index');

        Route::get('/create', [CardController::class, 'create'])->name('create');

        Route::get('/edit', [CardController::class, 'edit'])->name('edit');
    });

    Route::group([
        'prefix' => 'card_buy',
        'as' => 'card_buy.',
    ], function () {
        Route::get('/index', [CardBuyController::class, 'index'])->name('index');
    });


    Route::group([
        'prefix' => 'exchange_code',
        'as' => 'exchange_code.',
    ], function () {
        Route::get('/index', [ExchangeCodeController::class, 'index'])->name('index');

        Route::get('/create', [ExchangeCodeController::class, 'create'])->name('create');

        Route::get('/edit', [ExchangeCodeController::class, 'edit'])->name('edit');
    });
});
