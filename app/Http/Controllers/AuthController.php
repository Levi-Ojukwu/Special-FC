<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Services\NotificationService;

class AuthController extends BaseController
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'resetPassword']]);
    }

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validated = $this->validateRequest($request, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);
        
        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'player',
            'is_verified' => false,
        ]);
        
        // Create notification for admins
        $notificationService = app(NotificationService::class);
        $notificationService->notifyAdmins(
            'New User Registration',
            "A new user {$user->first_name} {$user->last_name} has registered.",
            'admin'
        );
        
        return $this->successResponse([
            'user' => $user,
            'message' => 'User registered successfully. Please wait for admin verification.',
        ], 'Registration successful', 201);
    }

    /**
     * Get a JWT via given credentials.
     */
    public function login(Request $request)
    {
        $validated = $this->validateRequest($request, [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $credentials = [
            'username' => $validated['username'],
            'password' => $validated['password'],
        ];
        
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth();

        if (!$token = $auth->attempt($credentials)) {
            return $this->errorResponse('Invalid credentials', 401);
        }
        
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        // /** @var \Tymon\JWTAuth\JWTGuard $auth */
        // $auth = auth();

        $user = auth()->user();
        $user->load('team');
        
        // Get payment status
        $latestPayment = $user->getLatestPayment();
        $paymentStatus = null;
        
        if ($latestPayment) {
            $latestPayment->updateProgressPercentage();
            $paymentStatus = [
                'last_payment_date' => $latestPayment->payment_date,
                'expiry_date' => $latestPayment->expiry_date,
                'progress_percentage' => $latestPayment->progress_percentage,
                'is_expired' => $latestPayment->progress_percentage >= 100,
            ];
        }
        
        return $this->successResponse([
            'user' => $user,
            'payment_status' => $paymentStatus,
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth();

        $auth->logout();
        
        return $this->successResponse(null, 'Successfully logged out');
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth();

        return $this->respondWithToken($auth->refresh());
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request)
    {
        $validated = $this->validateRequest($request, [
            'email' => 'required|email|exists:users,email',
        ]);
        
        // In a real application, you would send a password reset email
        // For this example, we'll just return a success message
        
        return $this->successResponse(null, 'Password reset link sent to your email');
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token)
    {
        /** @var \Tymon\JWTAuth\JWTGuard $auth */
        $auth = auth();
        
        return $this->successResponse([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $auth->factory()->getTTL() * 60,
            'user' => $auth->user(),
        ]);
    }
}