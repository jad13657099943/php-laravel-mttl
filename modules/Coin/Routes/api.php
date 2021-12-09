<?php

// use Modules\Coin\Http\Controllers\Api\DefaultController;
use Modules\Coin\Http\Controllers\Api\AssetController;
use Modules\Coin\Http\Controllers\Api\ExchangeController;
use Modules\Coin\Http\Controllers\Api\InternalTradeController;
use Modules\Coin\Http\Controllers\Api\NoticeController;
use Modules\Coin\Http\Controllers\Api\RechargeController;
use Modules\Coin\Http\Controllers\Api\WalletController;
use Modules\Coin\Http\Controllers\Api\WithdrawController;
use Modules\Coin\Http\Controllers\Api\CoinLogController;
use Modules\Coin\Http\Controllers\Api\TestController;
use Modules\Coin\Http\Controllers\Api\SymbolController;

Route::any('v1/tokenio/notice', [NoticeController::class, 'tokenio'])->name('tokenIO.notice'); // TokenIO 回调通知
Route::get('coin/log', [CoinLogController::class, 'index'])->name('coin.log.index');
Route::get('test', [TestController::class, 'test'])->name('test');

// 前台API路由
Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:sanctum'],
], function () {
    Route::post('withdraw', [WithdrawController::class, 'create'])->name('withdraw.create');

    Route::get('recharge', [RechargeController::class, 'list'])->name('recharge');

    Route::get('assets', [AssetController::class, 'index'])->name('asset.list');

    Route::get('assets/log_list', [AssetController::class, 'logList'])->name('asset.log_list');

    Route::get('assets/log_module_list', [AssetController::class, 'logModuleList'])->name('asset.logModuleList');

    Route::get('wallets/{chain}', [WalletController::class, 'getByChain'])->name('wallets.chain');

    Route::post('internal_trade', [InternalTradeController::class, 'create'])->name('internal_trade.create');

    Route::get('exchange/settings', [ExchangeController::class, 'settings'])->name('exchange.market');

    Route::post('exchange/order', [ExchangeController::class, 'createOrder'])->name('exchange.order.create');
    Route::post('exchange/order_list', [ExchangeController::class, 'orderList'])->name('exchange.order.order_list');

    Route::get('withdraw_list', [WithdrawController::class, 'userWithdrawList'])->name('withdraw.withdraw_list');

    Route::get('symbol/coin_config', [SymbolController::class, 'coinConfig'])->name('symbol.coin_config');
    Route::get('symbol/balance', [SymbolController::class, 'symbolBalance'])->name('symbol.balance');
    Route::get('symbol/info', [SymbolController::class, 'symbolInfo'])->name('symbol.info');

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


    include_route_files(__DIR__ . '/api/admin/');
});
