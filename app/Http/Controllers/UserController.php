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

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:api');
        $this->notificationService = $notificationService;
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
        
        // Handle payment proof upload
        $receiptImage = $request->file('receipt_image');
        $imageName = time() . '.' . $receiptImage->extension();
        $receiptImage->storeAs('public/receipts', $imageName);
        
        // Calculate expiry date for monthly dues
        $expiryDate = null;
        if ($validated['type'] === 'monthly_dues') {
            $expiryDate = Carbon::parse($validated['payment_date'])->addDays(30);
        }
        
        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'type' => $validated['type'],
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
