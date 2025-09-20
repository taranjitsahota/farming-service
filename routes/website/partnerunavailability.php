<?php

use App\Http\Controllers\PartnerUnavailabilityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('partner-unavailabilities', PartnerUnavailabilityController::class);
    });
});
