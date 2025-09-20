<?php

use App\Http\Controllers\TractorUnavailabilityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('tractor-unavailabilities', TractorUnavailabilityController::class);
    });
});
