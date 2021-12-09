<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\web\RegisterController;
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

Route::get('/', 'WelcomeController@index');
//Route::view('/c', 'core::frontend.register.index');
Route::get('/c', [RegisterController::class, 'index'])->name('index');
