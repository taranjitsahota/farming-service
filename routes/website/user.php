<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
            Route::get('/admin-list', [UserController::class, 'adminList']);
            Route::get('/partner-list', [UserController::class, 'partnerList']);
    Route::post('/upload-profile-pic', [UserController::class, 'uploadProfilePic']);
    Route::delete('/delete-upload-profile-pic/{id}', [UserController::class, 'deleteUploadProfilePic']);
    Route::apiResource('users', UserController::class);
    // Route::middleware('role:superadmin')->group(function () {
    // });
});