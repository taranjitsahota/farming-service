<?php

use App\Http\Controllers\SupportController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:farmer')->group(function () {
        Route::get('/issues/types', [SupportController::class, 'issueTypes']);
        Route::get('/issues/types/{id}', [SupportController::class, 'issueTypeDetail']);
        Route::post('/issues/report', [SupportController::class, 'reportIssue']);
        Route::get('/faqs', [SupportController::class, 'faqs']);
        Route::get('/support-contacts', [SupportController::class, 'contacts']);
        Route::get('/support-hours', [SupportController::class, 'getSupportHours']);
    });
});
