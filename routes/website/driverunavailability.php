<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DriverUnavailabilityController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('driver-unavailabilities', DriverUnavailabilityController::class);
    });
});