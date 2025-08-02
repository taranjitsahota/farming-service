<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin')->group(function () {
        Route::get('/dashboard-metrics', [DashboardController::class, 'getDashboardMetrics']);
        Route::get('/bookings-trend', [DashboardController::class, 'getBookingsTrend']);
    });
});
