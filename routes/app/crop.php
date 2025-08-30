<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CropController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('crops', CropController::class);
    });
});
