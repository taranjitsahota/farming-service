<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentUnavailabilityController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('equipment-unavailabilities', EquipmentUnavailabilityController::class);
    });
});