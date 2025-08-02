<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use Aws\Middleware;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('equipments', EquipmentController::class);
    });
});
