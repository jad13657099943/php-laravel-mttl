<?php

// use Modules\User\Http\Controllers\Api\DefaultController;
use Modules\Mttl\Http\Controllers\Api\JobController;
use Modules\Mttl\Http\Controllers\Api\MeitaConfigController;
use Modules\User\Http\Controllers\api\LoginController;
use Modules\User\Http\Controllers\api\UserController;
use Modules\User\Http\Controllers\api\AppealController;
use Modules\User\Http\Controllers\api\ArticleController;
use Modules\User\Http\Controllers\api\UserNoticeController;

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

Route::group(['middleware' => 'guest'], function () {

    Route::get('/job',[JobController::class,'job'])->name('job');

   Route::get('/get',[MeitaConfigController::class,'tokenIo2'])->name('get');

    Route::post('/reg', [LoginController::class, 'register'])->name('reg')
        ->middleware('throttle:30,1');
    Route::post('/login', [LoginController::class, 'login'])->name('login')
        ->middleware('throttle:60,1');

    Route::post('/dapp_login', [LoginController::class, 'dappLogin'])->name('dapp_login')
        ->middleware('throttle:60,1');
    Route::post('/check_registered', [LoginController::class, 'checkRegistered'])->name('check_registered')
        ->middleware('throttle:60,1');


    Route::group(['prefix' => 'article', 'as' => 'article.'], function () {
        Route::get('/cate_list', [ArticleController::class, 'cateList'])->name('cate_list');
        Route::get('/list', [ArticleController::class, 'articleList'])->name('list');
        Route::get('/info', [ArticleController::class, 'articleInfo'])->name('info');
    });

    //地址注册
    Route::group(['prefix' => 'account', 'as' => 'account.'], function () {
        Route::post('/register', [LoginController::class, 'accountRegister'])->name('register')
            ->middleware('throttle:20,1');
        Route::post('/login', [LoginController::class, 'accountLogin'])->name('login')
            ->middleware('throttle:20,1');

    });

    //签名
    Route::group(['prefix'=>'none','as'=>'none.'],function (){
        Route::get('/none',[LoginController::class,'getNone'])->name('none');
        Route::post('/login',[LoginController::class,'noneLogin'])->name('login');
    });
});

Route::group([
    'middleware' => ['auth:sanctum']
], function () {

    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
        Route::get('/invitation', [LoginController::class, 'invitation'])->name('invitation');
        Route::get('/info', [UserController::class, 'info'])->name('info');
        Route::post('/edit', [UserController::class, 'editInfo'])->name('edit');
        Route::get('/team', [UserController::class, 'userSons'])->name('team');
        Route::get('/team_total', [UserController::class, 'teamTotal'])->name('team_total');
        Route::get('/coin_balance', [UserController::class, 'coinBalance'])->name('coin_balance');
        Route::post('/password',[LoginController::class,'setPassword'])->name('password');
    });


    //工单申诉
    Route::group(['prefix' => 'appeal', 'as' => 'appeal.'], function () {
        Route::post('/add', [AppealController::class, 'add'])->name('add');
        Route::get('/list', [AppealController::class, 'list'])->name('list');
        Route::get('/info', [AppealController::class, 'info'])->name('info');
        Route::get('/types', [AppealController::class, 'typeList'])->name('types');
    });

    //通知消息
    Route::group(['prefix' => 'user_notice', 'as' => 'user_notice.'], function () {
        Route::get('/state', [UserNoticeController::class, 'state'])->name('state');
        Route::get('/list', [UserNoticeController::class, 'list'])->name('list');
        Route::get('/info', [UserNoticeController::class, 'info'])->name('info');
    });
});


Route::group([
    'namespace' => 'Admin\Api',
    'prefix' => 'admin',
    'as' => 'admin.api.',
    'middleware' => [\Modules\Core\Http\Middleware\UseGuard::class . ':admin'],
], function () {
    include_route_files(__DIR__ . '/admin/');
});
