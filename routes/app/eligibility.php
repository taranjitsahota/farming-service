<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EligibilityController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware(['role:farmer'])->group(function () {
        Route::post('/check-eligibility', [EligibilityController::class, 'checkServiceAvailability']);
    });
});