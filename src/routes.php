<?php

use EscolaLms\Webinar\Http\Controllers\WebinarController;
use Illuminate\Support\Facades\Route;

// admin endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/admin'], function () {
    Route::resource('webinars', WebinarController::class);
});

// user endpoints
Route::group(['middleware' => ['auth:api'], 'prefix' => 'api/consultations'], function () {
});

