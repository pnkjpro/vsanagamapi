<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('admin')->group(function (){
    Route::post('/create/topic', [QuestionController::class, 'createTopic'])->name('admin.topic.create');
    Route::post('/create/mock', [QuestionController::class, 'createMock'])->name('admin.topic.mock');
});
