<?php

use App\Http\Controllers\SaveAddressController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:user')->group(function () {
        Route::post('/location/save', [SaveAddressController::class, 'store']);
        // Route::apiResource('/location/save', SaveAddressController::class);

    });
});
