<?php

use App\Http\Controllers\SubscriptionPlanController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('subscription-plan', SubscriptionPlanController::class);
    });
});