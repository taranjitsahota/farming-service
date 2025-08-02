<?php

use App\Http\Controllers\ServiceAreaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('/service-areas', ServiceAreaController::class);
    });
});