<?php

use App\Http\Controllers\DriverController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('drivers', DriverController::class);
        Route::post('/assign-driver', [DriverController::class, 'AssignDriver']);
    });
});