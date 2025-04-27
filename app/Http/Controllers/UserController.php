<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Payment;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class UserController extends BaseController
{
    protected $notificationService;
    protected $fileUploadService;


    public function __construct(NotificationService $notificationService, FileUploadService $fileUploadService)
    {
        $this->middleware('auth:api');
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Get all players
     */
    public function getPlayers()
    {
        $players = User::where('role', 'player')
            ->with('team')
            ->get();
            
        return $this->successResponse($players);
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $validated = $this->validateRequest($request, [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
        ]);
        
        
        $user->update($validated);
        
        return $this->successResponse($user, 'Profile updated successfully');
    }

    /**
     * Upload payment proof
     */
    public function uploadPaymentProof(Request $request)
    {
        $user = Auth::user();
        
        $validated = $this->validateRequest($request, [
            'amount' => 'required|numeric|min:1',
            'payment_type' => 'required|in:registration,monthly_dues,other',
            'payment_proof' => 'required|image|max:2048',
            'payment_date' => 'required|date',
        ]);
        
        // Upload file and get the result
        $uploadResult = $this->fileUploadService->uploadFile($request->file('payment_proof'), 'receipts');

        // Extract the filename from the upload result
        $imageName = $uploadResult['filename'] ?? null;  // Directly access the 'filename' key 
        
          // If upload failed, return error
        if (!$imageName) {
            return $this->errorResponse('Failed to upload payment proof. Please try again.', 500);
        }

        // Calculate expiry date for monthly dues
        $expiryDate = null;
        if ($validated['payment_type'] === 'monthly_dues') {
            $expiryDate = Carbon::parse($validated['payment_date'])->addDays(30);
        }
        
        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'type' => $validated['payment_type'],
            'receipt_image' => 'receipts/' . $imageName,
            'payment_date' => $validated['payment_date'],
            'expiry_date' => $expiryDate,
            'is_verified' => false,
            'progress_percentage' => 0,
        ]);
        
        // Notify admins
        $this->notificationService->notifyAdmins(
            'New Payment Uploaded',
            "User {$user->first_name} {$user->last_name} has uploaded a payment proof.",
            'payment'
        );
        
        return $this->successResponse($payment, 'Payment proof uploaded successfully. Waiting for verification.');
    }
}
