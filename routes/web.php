<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Add this test API route
Route::get('/api-test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API test route is working!',
        'data' => [
            'timestamp' => now()->toDateTimeString()
        ]
    ]);
});
