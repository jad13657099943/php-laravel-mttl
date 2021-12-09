<?php

// use Modules\Coinv2\Http\Controllers\Api\DefaultController;
use Modules\Coinv2\Http\Controllers\Api\AssetController;
use Modules\Coinv2\Http\Controllers\Api\ExchangeController;
use Modules\Coinv2\Http\Controllers\Api\InternalTradeController;
use Modules\Coinv2\Http\Controllers\Api\NoticeController;
use Modules\Coinv2\Http\Controllers\Api\WalletController;
use Modules\Coinv2\Http\Controllers\Api\WithdrawController;
use Modules\Coinv2\Http\Controllers\Api\CoinLogController;
use Modules\Coinv2\Http\Controllers\Api\TestController;

Route::any('v1/tokenio/notice', [NoticeController::class, 'tokenio'])->name('tokenIO.notice'); // TokenIO 回调通知
Route::get('coin/log', [CoinLogController::class, 'index'])->name('coin.log.index');
Route::get('test', [TestController::class, 'test'])->name('test');

// 前台API路由
Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:sanctum'],
], function () {
    Route::post('withdraw', [WithdrawController::class, 'create'])->name('withdraw.create');

    Route::get('assets', [AssetController::class, 'index'])->name('asset.list');

    Route::get('assets/log_list', [AssetController::class, 'logList'])->name('asset.log_list');

    Route::get('assets/log_module_list', [AssetController::class, 'logModuleList'])->name('asset.logModuleList');

    Route::get('wallets/{chain}', [WalletController::class, 'getByChain'])->name('wallets.chain');

    Route::post('internal_trade', [InternalTradeController::class, 'create'])->name('internal_trade.create');

    Route::get('exchange/settings', [ExchangeController::class, 'settings'])->name('exchange.market');

    Route::post('exchange/order', [ExchangeController::class, 'createOrder'])->name('exchange.order.create');
    Route::post('exchange/order_list', [ExchangeController::class, 'orderList'])->name('exchange.order.order_list');

    Route::get('withdraw_list', [WithdrawController::class, 'userWithdrawList'])->name('withdraw.withdraw_list');

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
