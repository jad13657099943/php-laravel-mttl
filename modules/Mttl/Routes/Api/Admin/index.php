<?php

use Modules\Mttl\Http\Controllers\Admin\Api\CardController;
use Modules\Mttl\Http\Controllers\Admin\Api\CardBuyController;
use Modules\Mttl\Http\Controllers\Admin\Api\ExchangeCodeController;

Route::group([
    'prefix' => 'admin',
    'as' => 'admin.',
    'middleware' => ["auth:admin"],
], function () {
    Route::group([
        'prefix' => 'card',
        'as' => 'card.'
    ], function () {
        Route::any('/index', [CardController::class, 'index'])->name('index');

        Route::any('/del', [CardController::class, 'del'])->name('del');

        Route::any('/create', [CardController::class, 'create'])->name('create');

        Route::any('/edit', [CardController::class, 'edit'])->name('edit');
    });

    Route::group([
        'prefix' => 'card_buy',
        'as' => 'card_buy.'
    ], function () {
        Route::any('/index', [CardBuyController::class, 'index'])->name('index');
    });

    Route::group([
        'prefix' => 'exchange_code',
        'as' => 'exchange_code.'
    ], function () {
        Route::any('/index', [ExchangeCodeController::class, 'index'])->name('index');

        Route::any('/create', [ExchangeCodeController::class, 'create'])->name('create');

        Route::any('/edit', [ExchangeCodeController::class, 'edit'])->name('edit');
    });
});
