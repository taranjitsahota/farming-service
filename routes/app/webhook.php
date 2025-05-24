<?php

use App\Http\Controllers\RazorpayWebhookController;
use Illuminate\Support\Facades\Route;


Route::post('/webhooks/razorpay', [RazorpayWebhookController::class, 'handle']);
