<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('services', ServiceController::class);
        Route::get('/services-by-area/{areaId}', [ServiceController::class, 'getServicesByArea']);
    });
});