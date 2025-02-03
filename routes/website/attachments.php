<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('attachments', AttachmentController::class);
    });
});
