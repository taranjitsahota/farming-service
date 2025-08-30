<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use Aws\Middleware;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('equipments', EquipmentController::class);
        Route::get('/get-equipment-by-area-and-service/{areaId}/{serviceId}', [EquipmentController::class, 'getEquipmentsByAreaAndService']);
    });
});
