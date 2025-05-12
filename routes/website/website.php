<?php

use App\Http\Controllers\WebsiteController;
use Illuminate\Support\Facades\Route;

Route::post('/contact-form', [WebsiteController::class, 'contactForm']);