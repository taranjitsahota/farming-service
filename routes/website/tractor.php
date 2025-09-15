<?php

use App\Http\Controllers\TractorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('tractors', TractorController::class);
        Route::get('/tractor-by-partner/{id}', [TractorController::class, 'tractorByPartnerId']);
    });
});
