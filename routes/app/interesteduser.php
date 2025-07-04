<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InterestedUserController;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:user')->group(function () {
        Route::apiResource('interested-users', InterestedUserController::class);
    });
});
