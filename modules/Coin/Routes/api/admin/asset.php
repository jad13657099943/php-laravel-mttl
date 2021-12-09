<?php

use Modules\Coin\Http\Controllers\Admin\Api\WithdrawApiController;
use Modules\Coin\Http\Controllers\Admin\Api\RechargeApiController;
use Modules\Coin\Http\Controllers\Admin\Api\InternalTradeApiController;
use Modules\Coin\Http\Controllers\Admin\Api\CoinAssetApiController;
use Modules\Coin\Http\Controllers\Admin\Api\CoinTradeApiController;
use Modules\Coin\Http\Controllers\Admin\Api\SystemWalletApiController;
use Modules\Coin\Http\Controllers\Admin\Api\CoinApiController;
use Modules\Coin\Http\Controllers\Admin\Api\UserWalletController;

/* 资产后台模块api接口路由 */

Route::group([
    //'namespace' => 'Admin\Api',
    //'prefix' => 'admin',
    //'as' => 'admin.api.',
    //'middleware' => [\Modules\Core\Http\Middleware\UseGuard::class . ':admin'],
], function () {


    /* 提现模块 */
    Route::group(['prefix' => 'withdraw','as'=>'withdraw.',], function () {

        Route::post('/list', [WithdrawApiController::class, 'index'])->name('index');
        Route::post('/edit_state', [WithdrawApiController::class, 'editState'])->name('edit_state');
        Route::post('/cancel2', [WithdrawApiController::class, 'cancel2'])->name('cancel2');
        Route::get('/total', [WithdrawApiController::class, 'total'])->name('total');
        Route::post('/sum',[WithdrawApiController::class,'sum'])->name('sum');
        Route::post('/accomplish',[WithdrawApiController::class,'accomplish'])->name('accomplish');
    });

    /* 充值模块 */
    Route::group(['prefix' => 'recharge','as'=>'recharge.'], function () {

        Route::post('/list', [RechargeApiController::class, 'index'])->name('index');
        Route::get('/link', [RechargeApiController::class, 'link'])->name('link');
        Route::get('/total', [RechargeApiController::class, 'total'])->name('total');
        Route::post('/sum',[RechargeApiController::class,'sum'])->name('sum');

    });

    /* 内部转账模块 */
    Route::group(['prefix' => 'internal','as'=>'internal.'], function () {

        Route::post('/list', [InternalTradeApiController::class, 'index'])->name('index');
    });

    /* 资产余额明细与日志模块 */
    Route::group(['prefix' => 'coin_asset','as'=>'coin_asset.'], function () {

        //资产列表
        Route::post('/list', [CoinAssetApiController::class, 'index'])->name('index');
        //变更记录
        Route::post('/coin_log', [CoinAssetApiController::class, 'coinLog'])->name('coin_log');
        //冻结|解冻资产
        Route::post('/frozen', [CoinAssetApiController::class, 'frozen'])->name('frozen');
    });

    /* 链上转账模块 */
    Route::group(['prefix' => 'coin_trade','as'=>'coin_trade.'], function () {

        Route::post('/list', [CoinTradeApiController::class, 'index'])->name('index');
        Route::get('/link', [CoinTradeApiController::class, 'link'])->name('link');
        //标记已手动转账
        Route::post('/manual', [CoinTradeApiController::class, 'manualTransfer'])->name('manual');
        //调用二次转账
        Route::post('/again', [CoinTradeApiController::class, 'tradeAgain'])->name('again');
    });

    //系统钱包
    Route::group(['prefix' => 'system_wallet','as'=>'system_wallet.'], function () {

        Route::post('/balance', [SystemWalletApiController::class, 'balance'])->name('balance');
        Route::post('/create', [SystemWalletApiController::class, 'create'])->name('create');
        Route::post('/edit_info', [SystemWalletApiController::class, 'editInfo'])->name('edit_info');

    });


    //币种管理
    Route::group(['prefix' => 'coin','as'=>'coin.'], function () {

        Route::post('/index', [CoinApiController::class, 'index'])->name('index');
        Route::post('/edit', [CoinApiController::class, 'edit'])->name('edit');
    });

    //钱包
    Route::group(['prefix' => 'user_wallet','as'=>'user_wallet.'], function () {

        Route::get('/index', [UserWalletController::class, 'index'])->name('index');
        Route::post('/edit_wallet',[UserWalletController::class,'edit_wallet'])->name('edit_wallet');
    });
});
