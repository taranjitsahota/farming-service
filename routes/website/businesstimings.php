<?php

use App\Http\Controllers\BusinessTimingController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:superadmin')->group(function () {

        Route::apiResource('business-timings', BusinessTimingController::class);
        Route::put('/apply-all', [BusinessTimingController::class, 'applyToAll']);

    });
});
