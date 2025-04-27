<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\FootballMatchController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json(['message' => 'API is working!']);
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// Protected routes
Route::group(['middleware' => 'auth:api'], function () {
    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    
    // Dashboard routes
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/statistics', [DashboardController::class, 'statistics']);
    Route::get('dashboard/activities', [DashboardController::class, 'activities']);
    
    // User routes
    Route::post('upload-payment', [UserController::class, 'uploadPaymentProof']);
    Route::get('players', [UserController::class, 'getPlayers']);
    Route::put('profile', [UserController::class, 'updateProfile']);
    
    // Team routes
    Route::get('teams', [TeamController::class, 'index']);
    Route::get('teams/{id}', [TeamController::class, 'show']);
    
    // Match routes
    Route::get('matches', [FootballMatchController::class, 'index']);
    Route::get('fixtures', [FootballMatchController::class, 'fixtures']);
    Route::get('results', [FootballMatchController::class, 'results']);
    Route::get('matches/{id}', [FootballMatchController::class, 'show']);
    
    // Statistics routes
    Route::get('statistics', [StatisticsController::class, 'index']);
    
    // Payment routes
    Route::get('payments/user', [PaymentController::class, 'userPayments']);
    
    // Notification routes
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    
    // Admin routes
    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {
        Route::get('users', [AdminController::class, 'getUsers']);
        Route::put('users/{id}/verify', [AdminController::class, 'verifyUser']);
        Route::put('users/{id}/unverify', [AdminController::class, 'unverifyUser']);
        Route::put('users/{id}/team', [AdminController::class, 'updateUserTeam']);
        
        Route::post('teams', [TeamController::class, 'store']);
        Route::put('teams/{id}', [TeamController::class, 'update']);
        Route::delete('teams/{id}', [TeamController::class, 'destroy']);
        
        Route::post('matches', [FootballMatchController::class, 'store']);
        Route::put('matches/{id}', [FootballMatchController::class, 'update']);
        Route::delete('matches/{id}', [FootballMatchController::class, 'destroy']);
        
        Route::post('statistics', [StatisticsController::class, 'store']);
        Route::put('statistics/{id}', [StatisticsController::class, 'update']);
        Route::delete('statistics/{id}', [StatisticsController::class, 'destroy']);
        
        Route::get('payments', [PaymentController::class, 'index']);
        Route::put('payments/{id}/verify', [PaymentController::class, 'verifyPayment']);
        Route::post('payments/{id}/reject', [PaymentController::class, 'rejectPayment']);
    });
});

