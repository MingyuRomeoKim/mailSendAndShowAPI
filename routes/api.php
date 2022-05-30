<?php

use App\Http\Controllers\Api\Release\ReleaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SingleMailController;
use App\Http\Controllers\Api\SingleMailListController;
use App\Http\Controllers\Api\SingleMailListDetailController;
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

/** 단건 메일 발송 */
Route::prefix('mail')->group(function() {
    Route::prefix('single')->group(function() {
        # 단건 메일 전송
        Route::post('/send',[SingleMailController::class,'send']);

        # 단건 메일 전송 리스트 출력
        Route::post('/result-list',[SingleMailListController::class,'index']);

        # 단건 메일 전송 디테일 리스트 출력
        Route::post('/result-detail',[SingleMailListDetailController::class,'index']);
    });
});
