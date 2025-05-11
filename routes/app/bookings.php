<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:user')->group(function () {
        Route::post('/bookings/available-slots', [BookingController::class, 'getAvailableSlots']);
        Route::post('/bookings/book', [BookingController::class, 'bookSlot']);
        Route::post('/bookings/getEstimatedPayment', [BookingController::class, 'getEstimatedPayment']);
        Route::post('/cancel-booking', [BookingController::class, 'cancelBooking']);
        Route::apiResource('bookings', BookingController::class);
        
    });

});