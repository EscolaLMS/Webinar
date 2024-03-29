<?php

use EscolaLms\Webinar\Http\Controllers\WebinarAPIController;
use EscolaLms\Webinar\Http\Controllers\WebinarController;
use Illuminate\Support\Facades\Route;

// admin endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/admin'], function () {
    Route::post('webinars/{id}', [WebinarController::class, 'update']);
    Route::resource('webinars', WebinarController::class);
    Route::get('webinars/users/assignable', [WebinarController::class, 'assignableUsers']);
});

// user endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/webinars'], function () {
    Route::get('/me', [WebinarAPIController::class, 'forCurrentUser']);
    Route::get('/generate-jitsi/{id}', [WebinarAPIController::class, 'generateJitsi']);
    Route::get('/start-live-stream/{id}', [WebinarAPIController::class, 'startLiveStream']);
    Route::get('/stop-live-stream/{id}', [WebinarAPIController::class, 'stopLiveStream']);
});


Route::group(['prefix' => 'api/webinars'], function () {
    Route::get('/', [WebinarAPIController::class, 'index']);
    Route::get('/{id}', [WebinarAPIController::class, 'show']);
});
