<?php

use App\Http\Controllers\SubstationController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('/substations', SubstationController::class);
    });
});