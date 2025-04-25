<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Create a notification for a user
     */
    public function createNotification($userId, $title, $message, $type = 'general')
    {
        return Notification::create([
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => false,
        ]);
    }

    /**
     * Create a notification for all admins
     */
    public function notifyAdmins($title, $message, $type = 'general')
    {
        $admins = User::where('role', 'admin')->get();
        $notifications = [];
        
        foreach ($admins as $admin) {
            $notifications[] = $this->createNotification($admin->id, $title, $message, $type);
        }
        
        return $notifications;
    }

    /**
     * Create a notification for all users
     */
    public function notifyAllUsers($title, $message, $type = 'general')
    {
        $users = User::all();
        $notifications = [];
        
        foreach ($users as $user) {
            $notifications[] = $this->createNotification($user->id, $title, $message, $type);
        }
        
        return $notifications;
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            $notification->is_read = true;
            $notification->save();
            return true;
        }
        
        return false;
    }
}