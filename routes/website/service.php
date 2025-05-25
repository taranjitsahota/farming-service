<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('services', ServiceController::class);
        Route::get('/service-by-equipment-id/{equipmentId}', [ServiceController::class, 'ServiceByEquipmentId']);
    });
});