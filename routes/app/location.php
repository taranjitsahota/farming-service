<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

// add this in middleware

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:user')->group(function () {
        Route::prefix('location')->controller(LocationController::class)->group(function () {
            Route::get('/states', 'getStates');
            Route::get('/cities/{state_id}', 'getCities');
            Route::get('/villages/{city_id}', 'getVillages');
        });
    });
});
