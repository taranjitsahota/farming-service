<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:user')->group(function () {
        Route::apiResource('subscription', SubscriptionController::class);
        Route::get('/verify-subscription/{id}', [SubscriptionController::class, 'verifySubscription']);
    });
});