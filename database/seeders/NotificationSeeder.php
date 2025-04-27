<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();
        
        if ($users->count() === 0) {
            $this->command->error('No users found. Please run user seeder first.');
            return;
        }

        $notificationTypes = ['general', 'payment', 'match', 'team', 'admin'];
        $notificationCount = 0;
        $clubName = config('football.club_name', 'Special Football FC');
        
        foreach ($users as $user) {
            // Create some notifications for each user
            for ($i = 0; $i < 5; $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                $isRead = rand(0, 1) > 0.5;
                
                $title = match($type) {
                    'payment' => 'Payment Reminder',
                    'match' => 'Upcoming Match',
                    'team' => 'Team Update',
                    'admin' => 'Admin Announcement',
                    default => 'General Notification',
                };
                
                $message = match($type) {
                    'payment' => "Please remember to pay your monthly dues for {$clubName}.",
                    'match' => "You have an upcoming match this weekend. Be prepared!",
                    'team' => "Your team has been updated with new players.",
                    'admin' => "Important announcement from {$clubName} administration.",
                    default => "This is a general notification from {$clubName}.",
                };
                
                Notification::create([
                    'user_id' => $user->id,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => $isRead,
                    'type' => $type,
                ]);
                
                $notificationCount++;
            }
        }
        
        $this->command->info($notificationCount . ' notifications created successfully.');
    }
}