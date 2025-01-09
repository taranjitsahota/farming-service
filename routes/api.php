<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;


// /*
// |--------------------------------------------------------------------------
// | API Routes
// |--------------------------------------------------------------------------
// |
// | Here is where you can register API routes for your application. These
// | routes are loaded by the RouteServiceProvider and all of them will
// | be assigned to the "api" middleware group. Make something great!
// |
// */

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Role-based routes
    Route::middleware('role:admin')->get('/admin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the admin dashboard']);
    });

    Route::middleware('role:superadmin')->get('/superadmin-dashboard', function () {
        return response()->json(['message' => 'Welcome to the superadmin dashboard']);
    });

    Route::middleware('role:user')->get('/user-dashboard', function () {
        return response()->json(['message' => 'Welcome to the user dashboard']);
    });

    Route::middleware(['auth:sanctum', 'role:admin,superadmin'])->get('/dashboard', function () {
        return response()->json(['message' => 'Welcome to the dashboard for admin and superadmin']);
    });
    
});

