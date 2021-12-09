<?php


use Modules\Coin\Http\Controllers\Admin\WithdrawController;
use Modules\Coin\Http\Controllers\Admin\RechargeController;
use Modules\Coin\Http\Controllers\Admin\InternalTradeController;
use Modules\Coin\Http\Controllers\Admin\CoinTradeController;
use Modules\Coin\Http\Controllers\Admin\CoinAssetController;
use Modules\Coin\Http\Controllers\Admin\SystemWalletController;
use Modules\Coin\Http\Controllers\Admin\CoinController;
use Modules\Coin\Http\Controllers\Admin\UserWalletController;
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

    //资产管理模块
    Route::group(['prefix' => 'asset','as'=>'asset.'], function () {

        //提现
        Route::group(['prefix' => 'withdraw','as'=>'withdraw.'], function () {

            Route::get('/index', [WithdrawController::class, 'index'])->name('index');
            Route::get('/total', [WithdrawController::class, 'total'])->name('total');
        });

        //充值
        Route::group(['prefix' => 'recharge','as'=>'recharge.'], function () {

            Route::get('/index', [RechargeController::class, 'index'])->name('index');
            Route::get('/total', [RechargeController::class, 'total'])->name('total');
        });

        //内部转账
        Route::group(['prefix' => 'internal','as'=>'internal.'], function () {

            Route::get('/index', [InternalTradeController::class, 'index'])->name('index');
        });

        //链上转账
        Route::group(['prefix' => 'coin_trade','as'=>'coin_trade.'], function () {

            Route::get('/index', [CoinTradeController::class, 'index'])->name('index');
            Route::get('/info', [CoinTradeController::class, 'info'])->name('info');
        });

        //资产余额
        Route::group(['prefix' => 'coin_asset','as'=>'coin_asset.'], function () {

            Route::get('/index', [CoinAssetController::class, 'index'])->name('index');
            Route::get('/coin_log', [CoinAssetController::class, 'assetLog'])->name('coin_log');
        });


        //系统钱包
        Route::group(['prefix' => 'system_wallet','as'=>'system_wallet.'], function () {

            Route::get('/index', [SystemWalletController::class, 'index'])->name('index');
            Route::get('/create', [SystemWalletController::class, 'create'])->name('create');
            Route::get('/edit_info', [SystemWalletController::class, 'editInfo'])->name('edit_info');
        });

        //币种
        Route::group(['prefix' => 'coin','as'=>'coin.'], function () {

            Route::get('/index', [CoinController::class, 'index'])->name('index');
            Route::get('/edit', [CoinController::class, 'edit'])->name('edit');
        });

        //钱包
        Route::group(['prefix' => 'user_wallet','as'=>'user_wallet.'], function () {

            Route::get('/index', [UserWalletController::class, 'index'])->name('index');
        });
    });

});
