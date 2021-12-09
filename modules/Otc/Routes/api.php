<?php

use Modules\Otc\Http\Controllers\Api\OtcCoinController;
use Modules\Otc\Http\Controllers\Api\OtcExchangeController;
use Modules\Otc\Http\Controllers\Api\OtcTradeController;
use Modules\Otc\Http\Controllers\Api\OtcTradeAppealController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 前台API路由
Route::group([
    'middleware' => ['auth:sanctum'],
], function () {

    /**
     * 可交易币种
     */
    Route::group([
        'prefix' => 'coin',
        'as' => 'coin.'
    ], function () {
        Route::get('/', [OtcCoinController::class, 'index'])->name('index');
    });


    /**
     * 挂单交易
     */
    Route::group([
        'prefix' => 'exchange',
        'as' => 'exchange.'
    ], function () {
        Route::get('/', [OtcExchangeController::class, 'index'])->name('index');
        Route::get('/my', [OtcExchangeController::class, 'userExchange'])->name('my.exchange');
        Route::post('/', [OtcExchangeController::class, 'store'])->name('store');
        Route::post('/{id}/cancel', [OtcExchangeController::class, 'cancel'])->name('cancel');
    });


    /**
     * 撮合订单
     */
    Route::group([
        'prefix' => 'trade',
        'as' => 'trade.'
    ], function () {
        Route::get('/', [OtcTradeController::class, 'index'])->name('index');
        Route::get('/{id}', [OtcTradeController::class, 'detail'])->name('detail');
        Route::get('/{id}/seller', [OtcTradeController::class, 'detail'])->name('seller.detail');
        Route::get('/{id}/buyer', [OtcTradeController::class, 'detail'])->name('buyer.detail');

        Route::post('/', [OtcTradeController::class, 'store'])->name('store');
        Route::post('/{id}/pay', [OtcTradeController::class, 'pay'])->name('pay');
        Route::post('/{id}/confirm', [OtcTradeController::class, 'confirm'])->name('confirm');

        //申诉
        Route::post('/{id}/appeal', [OtcTradeAppealController::class, 'store'])->name('appeal.store');
        Route::get('/{id}/appeal', [OtcTradeAppealController::class, 'info'])->name('appeal.info');
    });
});


/*
 * Admin Routes
 * Namespaces indicate folder structure
 */
Route::group([
    'namespace' => 'Admin\Api',
    'prefix' => 'admin',
    'as' => 'admin.api.',
    'middleware' => [\Modules\Core\Http\Middleware\UseGuard::class . ':admin'],
], function () {


    include_route_files(__DIR__.'/api/admin/');
});
