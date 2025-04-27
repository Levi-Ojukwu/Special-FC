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

// Protected routes (auth:api middleware)
Route::group(['middleware' => 'auth:api'], function () {

    // Auth routes
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    
    // Dashboard routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('statistics', [DashboardController::class, 'statistics']);
        Route::get('activities', [DashboardController::class, 'activities']);
    });

    // User routes
    Route::prefix('user')->group(function () {
        Route::post('upload-payment', [UserController::class, 'uploadPaymentProof']);
        Route::get('players', [UserController::class, 'getPlayers']);
        Route::put('profile', [UserController::class, 'updateProfile']);
    });

    // Team routes
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index']);
        Route::get('{team}', [TeamController::class, 'show']);  // Implicit binding
    });

    // Match routes
    Route::prefix('matches')->group(function () {
        Route::get('/', [FootballMatchController::class, 'index']);
        Route::get('fixtures', [FootballMatchController::class, 'fixtures']);
        Route::get('results', [FootballMatchController::class, 'results']);
        Route::get('{match}', [FootballMatchController::class, 'show']);  // Implicit binding
    });

    // Statistics routes
    Route::prefix('statistics')->group(function () {
        Route::get('/', [StatisticsController::class, 'index']);
    });

    // Payment routes
    Route::prefix('payments')->group(function () {
        Route::get('user', [PaymentController::class, 'userPayments']);
    });

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::put('{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::put('read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // Admin routes (admin middleware and prefix 'admin')
    Route::group(['middleware' => 'admin', 'prefix' => 'admin'], function () {

        // User management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'getUsers']);
            Route::put('{user}/verify', [AdminController::class, 'verifyUser']);
            Route::put('{user}/unverify', [AdminController::class, 'unverifyUser']);
            Route::put('{user}/team', [AdminController::class, 'updateUserTeam']);
        });

        // Team management
        Route::prefix('teams')->group(function () {
            Route::post('/', [TeamController::class, 'store']);
            Route::put('{team}', [TeamController::class, 'update']);
            Route::delete('{team}', [TeamController::class, 'destroy']);
        });

        // Match management
        Route::prefix('matches')->group(function () {
            Route::post('/', [FootballMatchController::class, 'store']);
            Route::put('{match}', [FootballMatchController::class, 'update']);
            Route::delete('{match}', [FootballMatchController::class, 'destroy']);
        });

        // Statistics management
        Route::prefix('statistics')->group(function () {
            Route::post('/', [StatisticsController::class, 'store']);
            Route::put('{statistic}', [StatisticsController::class, 'update']);
            Route::delete('{statistic}', [StatisticsController::class, 'destroy']);
        });

        // Payment management
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentController::class, 'index']);
            Route::put('{payment}/verify', [PaymentController::class, 'verifyPayment']);
            Route::post('{payment}/reject', [PaymentController::class, 'rejectPayment']);
        });
    });
});
