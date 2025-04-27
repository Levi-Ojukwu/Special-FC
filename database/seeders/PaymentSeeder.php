<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\User;
use Carbon\Carbon;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all players
        $players = User::where('role', 'player')->get();
        
        if ($players->count() === 0) {
            $this->command->error('No players found. Please run user seeder first.');
            return;
        }

        $verifiedPaymentCount = 0;
        $pendingPaymentCount = 0;
        $monthlyDues = config('football.monthly_dues', 1000);
        $expiryDays = config('football.payment_expiry_days', 30);

        foreach ($players as $player) {
            // Create a verified payment for each player
            $paymentDate = Carbon::now()->subDays(rand(1, $expiryDays));
            $expiryDate = Carbon::parse($paymentDate)->addDays($expiryDays);
            
            Payment::create([
                'user_id' => $player->id,
                'type' => 'monthly_dues',
                'amount' => $monthlyDues,
                'receipt_image' => null, // No actual image in seeder
                'is_verified' => true,
                'payment_date' => $paymentDate,
                'expiry_date' => $expiryDate,
                'progress_percentage' => min(100, round(($paymentDate->diffInDays(Carbon::now()) / $expiryDays) * 100)),
            ]);
            
            $verifiedPaymentCount++;
            
            // Create some pending payments for some players
            if (rand(0, 1) > 0.7) {
                Payment::create([
                    'user_id' => $player->id,
                    'type' => 'monthly_dues',
                    'amount' => $monthlyDues,
                    'receipt_image' => null, // No actual image in seeder
                    'is_verified' => false,
                    'payment_date' => Carbon::now(),
                    'expiry_date' => null,
                    'progress_percentage' => 0,
                ]);
                
                $pendingPaymentCount++;
            }
        }
        
        $this->command->info($verifiedPaymentCount . ' verified payments and ' . $pendingPaymentCount . ' pending payments created successfully.');
    }
}