<?php

use App\Http\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('subscriptions', SubscriptionController::class);
    });
});