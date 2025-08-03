<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;



//----------------------------------------------Without middlware routes--------------------------------------------------------------

Route::post('/register-user', [AuthController::class, 'registerUser']);
Route::post('/send-otp-user', [AuthController::class, 'sendOtpUser']);
Route::post('/verify-otp-user', [AuthController::class, 'verifyOtpUser']);
Route::post('/login-user', [AuthController::class, 'loginUser']);
Route::get('/country-codes', function () {
    return response()->json(config('country_codes'));
});
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');;
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/send-otp', [AuthController::class, 'sendOtpForPasswordReset'])->middleware('throttle:5,1'); // Max 5 requests per minute
Route::post('/change-password', [AuthController::class, 'changePassword']);



//------------------------------------------sanctum routes without role based----------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {


    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/complete-profile', [AuthController::class, 'completeUserProfile']);



    //--------------------------------------------user routes---------------------------------------------------------------------------



    Route::middleware(['role:user'])->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard']);
    });




    //-------------------------------------user and registration process complete routes-------------------------------------------------------------



    Route::middleware(['role:user', 'check.process.complete'])->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard with complete registration process']);
    });

    //-------------------------------------------Driver routes--------------------------------------------------------------------------



    Route::middleware('role:driver')->get('/driver-dashboard', function () {
        return response()->json(['message' => 'Welcome to the driver dashboard']);
    });
});
