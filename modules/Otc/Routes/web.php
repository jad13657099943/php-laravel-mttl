<?php


use Modules\Otc\Http\Controllers\Admin\OtcCoinController;
use Modules\Otc\Http\Controllers\Admin\OtcUserController;
use Modules\Otc\Http\Controllers\Admin\OtcExchangeController;
use Modules\Otc\Http\Controllers\Admin\OtcTradeController;
use Modules\Otc\Http\Controllers\Admin\OtcTradeAppealController;

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


Route::group([
    'namespace' => 'Admin',
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => [\Modules\Core\Http\Middleware\UseGuard::class . ':admin'],
], function () {

    //OTC模块
    Route::group(['prefix' => 'otc','as'=>'otc.'], function () {

        //币种
        Route::group(['prefix' => 'coin','as'=>'coin.'], function () {

            Route::get('/index', [OtcCoinController::class, 'index'])->name('index');
        });

        //会员
        Route::group(['prefix' => 'user','as'=>'user.'], function () {

            Route::get('/index', [OtcUserController::class, 'index'])->name('index');
        });

        //挂单
        Route::group(['prefix' => 'exchange','as'=>'exchange.'], function () {

            Route::get('/index', [OtcExchangeController::class, 'index'])->name('index');
        });

        //撮合
        Route::group(['prefix' => 'trade','as'=>'trade.'], function () {

            Route::get('/index', [OtcTradeController::class, 'index'])->name('index');
            Route::get('/info', [OtcTradeController::class, 'info'])->name('info');
        });

        //申诉
        Route::group(['prefix' => 'appeal','as'=>'appeal.'], function () {

            Route::get('/index', [OtcTradeAppealController::class, 'index'])->name('index');
            Route::get('/edit', [OtcTradeAppealController::class, 'edit'])->name('edit');
        });

    });

});
