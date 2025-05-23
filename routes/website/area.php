<?php

use App\Http\Controllers\AreaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('areas', AreaController::class);
    });
});