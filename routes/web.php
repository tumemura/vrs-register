<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

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

Route::get('/status',[MainController::class,'status']);
Route::get('/status/{small?}',[MainController::class,'status']);

Route::get('/status2',[MainController::class,'status2']);
Route::get('/status2/{small?}',[MainController::class,'status2']);

Route::group(['middleware' => ['maintenance']], function () {

    // 医療従事者用の専用ログイン
    if (env('SP_LOGIN','') == 'on') {
        Route::get('/sp',[MainController::class,'spindex']);
        Route::post('/splogin',[MainController::class,'splogin']);
        Route::post('/cc_missing',[MainController::class,'cc_missing']);
        Route::post('/cc_update',[MainController::class,'cc_update']);
        Route::post('/cc_nodata',[MainController::class,'cc_nodata']);
    }

    Route::get('/',[MainController::class,'index']);

    Route::post('/login',[MainController::class,'login']);
    
    Route::get('/logout',[MainController::class,'logout']);
    Route::post('/step1',[MainController::class,'step1']);
    Route::post('/step1s',[MainController::class,'step1s']);
    Route::post('/step2',[MainController::class,'step2']);
    Route::post('/step4',[MainController::class,'step4']);
    Route::post('/step5',[MainController::class,'step5']);
    Route::post('/step6',[MainController::class,'step6']);
    Route::post('/step1r',[MainController::class,'step1r']);
    Route::post('/register',[MainController::class,'register']);
    Route::get('/step1c',[MainController::class,'step1c']);
    Route::get('/step1n',[MainController::class,'step1n']);
    Route::get('/complete',[MainController::class,'complete']);

    Route::group(['middleware' => ['login']], function () {
        Route::get('/mypage',[MainController::class,'mypage']);
        Route::get('/cancel',[MainController::class,'cancel']);
        Route::get('/auto-reserve/{todayIncluded}',[MainController::class,'autoReserve']);    
        Route::get('/calendar/{vaccine_id}',[MainController::class,'calendar']);
        Route::get('/calendar/start/{date}',[MainController::class,'calendarStart']);
        Route::get('/frame/{vaccine_id}/{date}',[MainController::class,'frame']);
        Route::get('/reserve/{frame_id}',[MainController::class,'reserve']);

        Route::get('/enter_dose_date/{vaccine_id}',[MainController::class,'enterDoseDate']);
        Route::post('/save_dose_date',[MainController::class,'saveDoseDate']);
        
    });
});
    