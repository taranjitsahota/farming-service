<?php

use App\Http\Controllers\ServiceareaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('service-areas', ServiceareaController::class);
    });
});