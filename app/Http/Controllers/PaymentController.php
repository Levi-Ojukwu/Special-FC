<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Auth;

class PaymentController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['index', 'verifyPayment', 'rejectPayment']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of all payments.
     */
    public function index()
    {
        $payments = Payment::with('user')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $this->successResponse($payments);
    }

    /**
     * Display a listing of the user's payments.
     */
    public function userPayments()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return $this->successResponse($payments);
    }

    /**
     * Verify a payment.
     */
    public function verifyPayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->is_verified) {
            return $this->errorResponse('Payment is already verified', 422);
        }
        
        $payment->is_verified = true;
        $payment->save();
        
        // Notify the user
        $this->notificationService->createNotification(
            $payment->user_id,
            'Payment Verified',
            "Your payment of {$payment->amount} has been verified.",
            'payment'
        );
        
        return $this->successResponse($payment, 'Payment verified successfully');
    }

    /**
     * Reject a payment.
     */
    public function rejectPayment(Request $request, $id)
    {
        $payment = Payment::findOrFail($id);
        
        if ($payment->is_verified) {
            return $this->errorResponse('Cannot reject a verified payment', 422);
        }
        
        // Notify the user
        $this->notificationService->createNotification(
            $payment->user_id,
            'Payment Rejected',
            "Your payment of {$payment->amount} has been rejected.",
            'payment'
        );
        
        // Delete the payment
        $payment->delete();
        
        return $this->successResponse(null, 'Payment rejected successfully');
    }
}
