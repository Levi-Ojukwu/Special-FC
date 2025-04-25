<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\NotificationService;

class AdminController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:api');
        $this->middleware('admin');
        $this->notificationService = $notificationService;
    }


    /**
     * Get all users.
     */
    public function getUsers()
    {
        $users = User::with('team')->get();
        
        return $this->successResponse($users);
    }

    /**
     * Verify a user.
     */
    public function verifyUser($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->is_verified) {
            return $this->errorResponse('User is already verified', 422);
        }
        
        $user->is_verified = true;
        $user->save();
        
        // Notify the user
        $this->notificationService->createNotification(
            $user->id,
            'Account Verified',
            'Your account has been verified. You can now access all features.',
            'admin'
        );
        
        return $this->successResponse($user, 'User verified successfully');
    }

    /**
     * Unverify a user.
     */
    public function unverifyUser($id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->is_verified) {
            return $this->errorResponse('User is already unverified', 422);
        }
        
        $user->is_verified = false;
        $user->save();
        
        // Notify the user
        $this->notificationService->createNotification(
            $user->id,
            'Account Unverified',
            'Your account has been unverified. Please contact the admin for more information.',
            'admin'
        );
        
        return $this->successResponse($user, 'User unverified successfully');
    }

    /**
     * Update user team.
     */
    public function updateUserTeam(Request $request, $id)
    {
        $validated = $this->validateRequest($request, [
            'team_id' => 'required|exists:teams,id',
        ]);
        
        $user = User::findOrFail($id);
        
        $user->team_id = $validated['team_id'];
        $user->save();
        
        // Notify the user
        $this->notificationService->createNotification(
            $user->id,
            'Team Assignment',
            'You have been assigned to a new team.',
            'team'
        );
        
        return $this->successResponse($user, 'User team updated successfully');
    }
 
}
