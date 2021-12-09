<?php

use Modules\Otc\Http\Controllers\Admin\Api\OtcCoinController;
use Modules\Otc\Http\Controllers\Admin\Api\OtcUserController;
use Modules\Otc\Http\Controllers\Admin\Api\OtcExchangeController;
use Modules\Otc\Http\Controllers\Admin\Api\OtcTradeAppealController;
use Modules\Otc\Http\Controllers\Admin\Api\OtcTradeController;

/* 币种 */
Route::group(['prefix' => 'coin','as'=>'coin.',], function () {

    Route::get('/index', [OtcCoinController::class, 'index'])->name('index');
});

/* 会员 */
Route::group(['prefix' => 'user','as'=>'user.',], function () {

    Route::get('/index', [OtcUserController::class, 'index'])->name('index');
});


/* 挂单 */
Route::group(['prefix' => 'exchange','as'=>'exchange.',], function () {

    Route::get('/index', [OtcExchangeController::class, 'index'])->name('index');
});

/* 撮合 */
Route::group(['prefix' => 'trade','as'=>'trade.',], function () {

    Route::get('/index', [OtcTradeController::class, 'index'])->name('index');
});

/* 申诉 */
Route::group(['prefix' => 'appeal','as'=>'appeal.',], function () {

    Route::get('/index', [OtcTradeAppealController::class, 'index'])->name('index');
    Route::post('/edit', [OtcTradeAppealController::class, 'edit'])->name('edit');
});
