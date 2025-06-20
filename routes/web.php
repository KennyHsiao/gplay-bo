<?php

use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Router;
use App\Http\Controllers\GPayController;
use App\Http\Controllers\TonWalletController;
use Illuminate\Support\Facades\Redis;

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

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('liff-tester', function(){
    return view('front.liff.home');
});

 /**
 * LIFF 共用
 */
Route::group([
    'namespace' => 'Liff',
    'prefix'    => 'liff'
 ], function (Router $router) {
    $router->get('/view/{id}', function($id){
        $url = $_GET["liff_state"] ?? "";
        return redirect($url);
    })->name("liff.view");
 });

Route::get('redis/test', function(){
    $ret = Redis::set('test', 'hihihihihi', 'PX', 6000, 'NX');
    return response()->json(['m'=>$ret]);
});

 # 登入註冊畫面
Route::get('{id}/login', [GPayController::class, 'login'])->middleware('adminuser')->name('auth.login');
Route::get('{id}/line-auth', [GPayController::class, 'LINEAuth'])->middleware('adminuser')->name('line.auth');

Route::controller(DemoController::class)
    ->prefix('demo/{id}')
    ->middleware(['adminuser'])
    ->group(function(){

    Route::get('pgsoft/{code?}', 'pgsoft')->name('game.pgsoft');
    Route::get('gplay/{code?}', 'gplay')->name('game.gplay');
});

Route::controller(GPayController::class)
    ->prefix('gpay/{id}')
    ->middleware(['adminuser'])
    ->group(function(){

    Route::get('reg/{code?}', 'reg')->name('gpay.reg');
    Route::post('post_reg/{code?}', 'postReg')->name('gpay.postReg');
    Route::get('home/{code?}', 'home')->name('gpay.home');
    Route::get('pay/{code?}', 'pay')->name('gpay.pay');
    Route::post('post_pay/{code?}', 'postPay')->name('gpay.postPay');

    # LINE Notify 通知
    Route::get('line-notify/setting', 'LineNotifySetting')->name('line-notify.setting');
    Route::get('line-notify/callback', 'LineNotifyCallback')->name('line-notify.callback');
    Route::get('line-notify/revoke', 'LineNotifyRevoke')->name('line-notify.revoke');
});


Route::controller(TonWalletController::class)
    ->prefix('ton')
    ->group(function(){

    Route::get('home', 'home')->name('tone.home');
});
