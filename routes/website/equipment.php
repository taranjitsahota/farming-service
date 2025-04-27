<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EquipmentController;
use Aws\Middleware;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:user')->group(function () {
        Route::apiResource('equipments', EquipmentController::class);
    });
});
