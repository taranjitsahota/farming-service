<?php

use App\Http\Controllers\ServiceController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('services', ServiceController::class);
        Route::get('/equipment-by-service-id/{equipmentId}', [ServiceController::class, 'EquipmentByServiceId']);
    });
});