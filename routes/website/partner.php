<?php

use App\Http\Controllers\PartnerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('role:superadmin,admin')->group(function () {
        Route::apiResource('partners', PartnerController::class);
        Route::post('/assign-partner', [PartnerController::class, 'AssignPartner']);
    });
});
