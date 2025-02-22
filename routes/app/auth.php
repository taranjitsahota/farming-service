<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// Example routes
Route::post('/register-user', [AuthController::class, 'registerUser']);

Route::post('/login-user', [AuthController::class, 'loginUser']);


Route::middleware('auth:sanctum')->group(function () {
Route::post('/complete-profile', [AuthController::class, 'completeUserProfile']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
    //sanctum routes without role based
    Route::middleware(['role:user'])->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard']);
    });
    Route::post('/logout', [AuthController::class, 'logout']);

});