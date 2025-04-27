<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:user')->group(function () {
        Route::apiResource('services', ServiceController::class);
    });
});