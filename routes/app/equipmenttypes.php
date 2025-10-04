<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentTypeController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('equipment-types', EquipmentTypeController::class);
    });
});