<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::where('role', 'player')->delete();
        User::where('username', 'admin')->delete();

        // Create admin user
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'is_verified' => true,
            ]
        );
        
        $this->command->info('Admin user created successfully.');

        // Get all teams
        $teams = Team::all();
        $playerCount = 0;

        // Create some players for each team
        foreach ($teams as $team) {
            for ($i = 1; $i <= 5; $i++) {
                $username = strtolower("player{$i}_" . str_replace(' ', '', $team->name));
                $email = strtolower("player{$i}_" . str_replace(' ', '', $team->name) . "@example.com");

                User::firstOrCreate(
                    ['username' => $username],
                    [
                        'first_name' => "Player{$i}",
                        'last_name' => $team->name,
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'team_id' => $team->id,
                        'role' => 'player',
                        'is_verified' => true,
                    ]
                );
                $playerCount++;
            }
        }
        
        $this->command->info($playerCount . ' players created successfully.');
    }
}