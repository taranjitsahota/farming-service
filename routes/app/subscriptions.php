<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:farmer')->group(function () {
        Route::apiResource('subscription', SubscriptionController::class);
        Route::get('/verify-subscription/{id}', [SubscriptionController::class, 'verifySubscription']);
        Route::post('/verify-payment-subscription', [SubscriptionController::class, 'verifyPayment']);
    });
});