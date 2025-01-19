<?php

use Illuminate\Support\Facades\Route;

// Example routes
Route::get('/example', function () {
    return response()->json(['message' => 'Example route']);
});
