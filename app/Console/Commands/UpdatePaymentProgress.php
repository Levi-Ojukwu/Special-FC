<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;

class UpdatePaymentProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:update-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update progress percentage for all payments';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $payments = Payment::where('is_verified', true)
            ->whereNotNull('expiry_date')
            ->get();
            
        $count = 0;
        
        foreach ($payments as $payment) {
            $payment->updateProgressPercentage();
            $count++;
        }
        
        $this->info("Updated progress for {$count} payments.");
        
        return 0;
    }
}
