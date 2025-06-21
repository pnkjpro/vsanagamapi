<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\Auth\UserController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->group(function (){
    Route::post('/create/topic', [QuestionController::class, 'createTopic'])->name('admin.topic.create');
    Route::post('/create/mock', [QuestionController::class, 'createMock'])->name('admin.topic.mock');
});

Route::prefix('users')->group(function(){
    Route::middleware('auth:sanctum')->get('/user', [UserController::class, 'fetchUser']);
    Route::post('/create', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::middleware('auth:sanctum')->post('/update/upi', [UserController::class, 'updatePaymentUpi']);
    Route::post('/otp/send', [OtpController::class, 'sendOtp']);
    Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);
    Route::middleware('auth:sanctum')->post('/password/reset', [UserController::class, 'updatePassword']);
    Route::middleware('auth:sanctum')->get('/refer/list', [UserController::class, 'listReferredUsers']);
});

Route::post('/topic', [QuestionController::class, 'getMockByNid']);