<?php

namespace App\Helpers;

use Carbon\Carbon;

class Helper
{
    /**
     * Format date to a readable format
     */
    public static function formatDate($date, $format = 'd M Y')
    {
        return Carbon::parse($date)->format($format);
    }

    /**
     * Calculate the percentage between two dates
     */
    public static function calculateDatePercentage($startDate, $endDate)
    {
        $now = Carbon::now();
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        
        if ($now > $end) {
            return 100;
        }
        
        $totalDays = $start->diffInDays($end);
        $daysElapsed = $start->diffInDays($now);
        
        if ($totalDays > 0) {
            return min(100, round(($daysElapsed / $totalDays) * 100));
        }
        
        return 100;
    }

    /**
     * Generate a random string
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}