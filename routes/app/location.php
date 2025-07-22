<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

// add this in middleware

Route::middleware('auth:sanctum')->group(function () {
    // Route::middleware('role:superadmin')->group(function () {
        Route::prefix('location')->controller(LocationController::class)->group(function () {
            Route::get('/states', 'getStates');
            Route::get('/districts/{state_id}', 'getDistricts');
            Route::get('/tehsils/{district_id}', 'getTehsils');
            Route::get('/villages/{tehsil_id}', 'getServicableVillages');
        });
    // });
});
