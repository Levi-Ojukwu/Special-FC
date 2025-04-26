<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create {name?} {email?} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $firstName = $this->argument('name') ?? $this->ask('Enter first name');
        $lastName = $this->ask('Enter last name');
        $email = $this->argument('email') ?? $this->ask('Enter email');
        $password = $this->argument('password') ?? $this->secret('Enter password');
        
        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            $this->error('Email already exists!');
            return 1;
        }
        
        // Create admin user
        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => strtolower($firstName) . '.' . strtolower($lastName),
            'email' => $email,
            'password' => Hash::make($password),
            'role' => 'admin',
            'is_verified' => true,
        ]);
        
        $this->info('Admin user created successfully!');
        $this->info('Username: ' . $user->username);
        
        return 0;
    }
}
