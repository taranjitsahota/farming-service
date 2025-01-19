<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Example routes
Route::post('/register', [AuthController::class, 'registeruser']);

Route::post('/login', [AuthController::class, 'loginuser']);

Route::middleware('auth:sanctum')->group(function () {

    //sanctum routes without role based

    Route::post('/logout', [AuthController::class, 'logout']);

});