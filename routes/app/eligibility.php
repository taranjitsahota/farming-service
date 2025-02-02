<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EligibilityController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware(['profile.completed','role:user'])->group(function () {
        Route::post('/check-eligibility', [EligibilityController::class, 'checkServiceAvailability']);
    });
});