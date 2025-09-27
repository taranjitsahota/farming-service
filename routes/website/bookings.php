<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;

Route::middleware('auth:sanctum')->group(function () {

    Route::middleware('role:superadmin,admin')->group(function () {
        Route::post('/bookings/available-slots', [BookingController::class, 'getAvailableSlots']);
        Route::post('/bookings/book', [BookingController::class, 'bookSlot']);
        Route::post('/bookings/getEstimatedPayment', [BookingController::class, 'getEstimatedPayment']);
        Route::post('/cancel-booking', [BookingController::class, 'cancelBooking']);
        Route::get('/get-all-bookings', [BookingController::class, 'getAllBookings']);
        Route::get('/get-pending-bookings', [BookingController::class, 'getPendingBookings']);
        Route::put('/assign-booking', [BookingController::class, 'assignBookings']);
        Route::apiResource('bookings', BookingController::class);
    });
});
