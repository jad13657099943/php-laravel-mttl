<?php

// use Modules\Mttl\Http\Controllers\Api\DefaultController;

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
use Modules\Mttl\Http\Controllers\Api\EnergyCardAutomaticController;
use Modules\Mttl\Http\Controllers\Api\EnergyCardBuyController;
use Modules\Mttl\Http\Controllers\Api\EnergyCardController;
use Modules\Mttl\Http\Controllers\Api\ExchangeCodeController;
use Modules\Mttl\Http\Controllers\Api\MeitaConfigController;
use Modules\Mttl\Http\Controllers\Api\MeitaUserController;
use Modules\Mttl\Http\Controllers\Api\NewController;
use Modules\Mttl\Http\Controllers\Api\RewardLogController;

include_route_files(__DIR__ . '/Api/Admin/');

// 能量卡
Route::group([
    'prefix' => 'energy_card',
], function () {
    Route::group(['middleware' => ['auth:sanctum']], function () {
        // 能量卡类型
        Route::get('types', [EnergyCardController::class, 'types']);
        // 能量卡列表
        Route::get('list', [EnergyCardController::class, 'list']);
        // 购买能量卡
        Route::post('buy', [EnergyCardBuyController::class, 'add']);
        // 我的能量卡
        Route::get('my', [EnergyCardBuyController::class, 'my']);
    });
});

// 自动购买
Route::group([
    'prefix' => 'automatic',
    'middleware' => ['auth:sanctum']
], function () {
    // 我的自动购买设置
    Route::get('index', [EnergyCardAutomaticController::class, 'index']);

    // 自动购买设置
    Route::post('setup', [EnergyCardAutomaticController::class, 'setup']);
});

// 奖励
Route::group([
    'prefix' => 'reward',
    'middleware' => ['auth:sanctum']
], function () {
    // 奖励首页
    Route::get('index', [RewardLogController::class, 'index']);

    // 奖励明细
    Route::get('detail', [RewardLogController::class, 'detail']);
});

// 社群
Route::group([
    'prefix' => 'community',
    'middleware' => ['auth:sanctum']
], function () {
    // 我的社群
    Route::get('index', [MeitaUserController::class, 'myCommunity']);

    // 直推列表
    Route::get('son_list', [MeitaUserController::class, 'sonList']);

    // 社群明细
    Route::get('detail', [MeitaUserController::class, 'communityDetail']);
});

// 项目配置
Route::group([
    'prefix' => 'config',
    'middleware' => ['auth:sanctum']
], function () {
    // 质押升级
    Route::get('upgrade', [MeitaConfigController::class, 'upgrade']);

    // 区块高度
    Route::get('block_height', [MeitaConfigController::class, 'blockHeight']);

    // 兑换配置
    Route::get('exchange', [MeitaConfigController::class, 'exchange']);

    // 充值通知
    Route::get('tokenIo',[MeitaConfigController::class,'tokenIo']);
});

// 用户
Route::group([
    'prefix' => 'user',
    'middleware' => ['auth:sanctum']
], function () {
    // 质押升级
    Route::post('upgrade', [MeitaUserController::class, 'upgrade']);

    // 取走质押
    Route::any('take_away', [MeitaUserController::class, 'takeAway']);
});



// 阿卡西记录
Route::group([
    'prefix' => 'exchange_code',
    'middleware' => ['auth:sanctum'],
    'throttle' => '30,1'
], function () {
    Route::post('exchange', [ExchangeCodeController::class, 'exchange']);
});

// 新增
Route::group([
    'prefix' => 'new',
    'middleware' => ['auth:sanctum']
], function () {
    // 取消提现
    Route::post('cancel', [NewController::class, 'cancelWithdraw']);

    // 签到奖励
    Route::post('award', [NewController::class, 'award']);

    //提交key
    Route::post('set',[NewController::class,'setKey']);

    //获取key
    Route::get('get',[NewController::class,'getKey']);

    //获取合约状态
    Route::get('check',[NewController::class,'getCheck']);

    //获取合约页面信息
    Route::get('message',[NewController::class,'getMessage']);

    //是否点击
    Route::post('sub',[NewController::class,'subCheckNum']);

});
