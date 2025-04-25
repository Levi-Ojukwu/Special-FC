<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
// use App\Helpers\Helper;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'receipt_image',
        'is_verified',
        'payment_date',
        'expiry_date',
        'progress_percentage',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'expiry_date' => 'datetime',
        'is_verified' => 'boolean',
    ];

    /**
     * Get the user that owns the payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update the progress percentage of the payment
     */
    public function updateProgressPercentage()
    {
        if (!$this->is_verified || !$this->expiry_date) {
            return;
        }

        $now = Carbon::now();
        $paymentDate = $this->payment_date;
        $expiryDate = $this->expiry_date;
        
        if ($now > $expiryDate) {
            $this->progress_percentage = 100;
        } else {
            $totalDays = $paymentDate->diffInDays($expiryDate);
            $daysElapsed = $paymentDate->diffInDays($now);
            
            if ($totalDays > 0) {
                $this->progress_percentage = min(100, round(($daysElapsed / $totalDays) * 100));
            } else {
                $this->progress_percentage = 100;
            }
        }
        
        $this->save();
    }
}
