<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentUnitController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('equipment-units', EquipmentUnitController::class);
        Route::get('/equipment-units-by-partner-id/{partnerId}', [EquipmentUnitController::class, 'equipmentUnitByPartnerId']);
    });
});