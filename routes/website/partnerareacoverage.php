<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PartnerAreaCoverageController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('partner-area-coverage', PartnerAreaCoverageController::class);
    });
});