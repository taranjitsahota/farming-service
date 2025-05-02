<?php

use App\Http\Controllers\RazorPayController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:user')->group(function () {
        Route::post('/verify-payment', [RazorPayController::class, 'verifyPayment']);
        Route::post('/create-payment-order', [RazorPayController::class, 'createRazorPayOrder']);
        Route::post('/payment-success', [RazorPayController::class, 'handleSuccess']);
    });
});
