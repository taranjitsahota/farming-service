<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/register', [AuthController::class, 'registeradmin']);


Route::middleware('auth:sanctum')->group(function () {

    //sanctum routes without role based

    Route::post('/logout', [AuthController::class, 'logout']);

    //superadmin routes
    
    Route::middleware('role:superadmin')->group(function () {
        // Route::post('/register', [AuthController::class, 'registeradmin']);
    });

    //admin routes
    Route::middleware('role:admin')->get('/admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the admin dashboard']);
    });

    //user routes
    Route::middleware('role:user')->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard']);
    });

    //superadmin and admin routes
    
    Route::middleware(['auth:sanctum', 'role:admin,superadmin'])->get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the dashboard for admin and superadmin']);
    });
    
});