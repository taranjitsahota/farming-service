<?php

use App\Http\Controllers\SlotController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('slots', SlotController::class);
        Route::post('/available-slots', [SlotController::class, 'getAvailableSlots']);
    });
});