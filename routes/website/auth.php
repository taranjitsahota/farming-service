<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

//----------------------------------------------Without middlware routes--------------------------------------------------------------
use Illuminate\Support\Facades\Cache;

Route::post('/register-superadmin-admin', [AuthController::class, 'registerSuperadminAdmin']);
Route::get('/country-codes', function () {
    return response()->json(config('country_codes'));
});

Route::post('/login', [AuthController::class, 'loginSuperadminAdmin']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->middleware('throttle:5,1');;
Route::post('/resend-otp', [AuthController::class, 'resendOtp']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::post('/send-otp', [AuthController::class, 'sendOtpForPasswordReset'])
    ->middleware('throttle:5,1'); // Max 5 requests per minute




//------------------------------------------sanctum routes without role based----------------------------------------------------------

Route::middleware('auth:sanctum')->group(function () {




    Route::post('/logout', [AuthController::class, 'logout']);



    //-------------------------------------------superadmin routes--------------------------------------------------------------------------



    Route::middleware('role:superadmin')->group(function () {});


    //-------------------------------------------Driver routes--------------------------------------------------------------------------



    Route::middleware('role:driver')->get('/driver-dashboard', function () {
        return response()->json(['message' => 'Welcome to the driver dashboard']);
    });



    //----------------------------------------------admin routes------------------------------------------------------------------------


    Route::middleware('role:admin')->get('/admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the admin dashboard']);
    });



    //-------------------------------------user and registration process complete routes-------------------------------------------------------------



    Route::middleware('role:user')->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard']);
    });



    //----------------------------------------------user routes--------------------------------------------------------------------------



    Route::middleware(['role:user'])->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard']);
    });



    //---------------------------------------superadmin and admin routes----------------------------------------------------------------



    Route::middleware(['auth:sanctum', 'role:admin,superadmin'])->get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the dashboard for admin and superadmin']);
    });
});
