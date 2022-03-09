<?php

use EscolaLms\Webinar\Http\Controllers\WebinarAPIController;
use EscolaLms\Webinar\Http\Controllers\WebinarController;
use Illuminate\Support\Facades\Route;

// admin endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/admin'], function () {
    Route::post('webinars/{id}', [WebinarController::class, 'update']);
    Route::resource('webinars', WebinarController::class);
});

// user endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/webinars'], function () {
});


Route::group(['prefix' => 'api/webinars'], function () {
    Route::get('/', [WebinarAPIController::class, 'index']);
    Route::get('/{id}', [WebinarAPIController::class, 'show']);
});
