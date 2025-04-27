<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Football Club Settings
    |--------------------------------------------------------------------------
    */
    
    'club_name' => env('CLUB_NAME', 'Special Football FC'),
    
    'registration_fee' => env('REGISTRATION_FEE', 2000),
    
    'monthly_dues' => env('MONTHLY_DUES', 1000),
    
    'payment_expiry_days' => env('PAYMENT_EXPIRY_DAYS', 30),
    
    'bank_details' => [
        'bank_name' => env('BANK_NAME', 'Special Bank'),
        'account_name' => env('ACCOUNT_NAME', 'Special Football FC'),
        'account_number' => env('ACCOUNT_NUMBER', '1234567890'),
    ],
    
    'teams' => [
        'Team Red',
        'Team Black',
        'Team Pink',
        'Team Yellow',
    ],
];