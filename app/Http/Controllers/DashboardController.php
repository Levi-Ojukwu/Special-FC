<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\FootballMatch;
use App\Models\Payment;
use App\Models\PlayerStatistic;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Get the appropriate dashboard based on user role
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return $this->adminDashboard();
        } else {
            return $this->userDashboard();
        }
    }

    /**
     * Get the user dashboard data
     */
    public function userDashboard()
    {
        $user = Auth::user();
        $user->load('team');
        
        // Get team position
        $teams = Team::orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->get();
            
        $teamPosition = 0;
        foreach ($teams as $index => $team) {
            if ($team->id === $user->team_id) {
                $teamPosition = $index + 1;
                break;
            }
        }
        
        // Get player statistics
        $totalGoals = $user->getTotalGoals();
        $totalAssists = $user->getTotalAssists();
        $totalYellowCards = $user->getTotalYellowCards();
        $totalRedCards = $user->getTotalRedCards();
        $totalHandballs = $user->getTotalHandballs();
        
        // Get payment status
        $latestPayment = $user->getLatestPayment();
        $paymentStatus = null;
        
        if ($latestPayment) {
            $latestPayment->updateProgressPercentage();
            $paymentStatus = [
                'payment_date' => $latestPayment->payment_date,
                'expiry_date' => $latestPayment->expiry_date,
                'progress_percentage' => $latestPayment->progress_percentage,
                'is_expired' => $latestPayment->progress_percentage >= 100,
            ];
        }
        
        // Get recent matches
        $recentMatches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('is_played', true)
            ->orderBy('match_date', 'desc')
            ->take(5)
            ->get();
            
        // Get upcoming matches
        $upcomingMatches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('is_played', false)
            ->orderBy('match_date', 'asc')
            ->take(5)
            ->get();
            
        // Get unread notifications count
        $unreadNotificationsCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        
        return $this->successResponse([
            'user' => $user,
            'team_position' => $teamPosition,
            'statistics' => [
                'goals' => $totalGoals,
                'assists' => $totalAssists,
                'yellow_cards' => $totalYellowCards,
                'red_cards' => $totalRedCards,
                'handballs' => $totalHandballs,
            ],
            'payment_status' => $paymentStatus,
            'is_verified' => $user->is_verified,
            'recent_matches' => $recentMatches,
            'upcoming_matches' => $upcomingMatches,
            'unread_notifications_count' => $unreadNotificationsCount,
        ]);
    }

    /**
     * Get the admin dashboard data
     */
    public function adminDashboard()
    {
        // Check if user is admin

        if (!auth()->user()->isAdmin()) {
            return $this->errorResponse('Unauthorized. Admin access required.', 403);
        }
        
        // Get counts for dashboard
        $userCount = User::where('role', 'player')->count();
        $verifiedUserCount = User::where('role', 'player')->where('is_verified', true)->count();
        $teamCount = Team::count();
        $matchCount = FootballMatch::count();
        $pendingPaymentsCount = Payment::where('is_verified', false)->count();
        
        // Get recent registrations
        $recentRegistrations = User::where('role', 'player')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get pending payments
        $pendingPayments = Payment::with('user')
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
            
        // Get upcoming matches
        $upcomingMatches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('is_played', false)
            ->orderBy('match_date', 'asc')
            ->take(5)
            ->get();
            
        // Get recent matches
        $recentMatches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('is_played', true)
            ->orderBy('match_date', 'desc')
            ->take(5)
            ->get();
            
        // Get unread notifications count
        // $auth = auth();

        $unreadNotificationsCount = Notification::where('user_id', auth()->id())
            ->where('is_read', false)
            ->count();
            
        // Get top scorers
        $topScorers = User::where('role', 'player')
            ->where('is_verified', true)
            ->with('team')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->first_name . ' ' . $user->last_name,
                    'username' => $user->username,
                    'team' => $user->team ? $user->team->name : null,
                    'goals' => $user->getTotalGoals(),
                ];
            })
            ->sortByDesc('goals')
            ->take(5)
            ->values();
            
        return $this->successResponse([
            'counts' => [
                'users' => $userCount,
                'verified_users' => $verifiedUserCount,
                'teams' => $teamCount,
                'matches' => $matchCount,
                'pending_payments' => $pendingPaymentsCount,
            ],
            'recent_registrations' => $recentRegistrations,
            'pending_payments' => $pendingPayments,
            'upcoming_matches' => $upcomingMatches,
            'recent_matches' => $recentMatches,
            'unread_notifications_count' => $unreadNotificationsCount,
            'top_scorers' => $topScorers,
        ]);
    }

    /**
     * Get summary statistics for the dashboard
     */
    public function statistics()
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            // Admin sees global statistics
            $totalMatches = FootballMatch::count();
            $playedMatches = FootballMatch::where('is_played', true)->count();
            $totalGoals = PlayerStatistic::sum('goals');
            $totalYellowCards = PlayerStatistic::sum('yellow_cards');
            $totalRedCards = PlayerStatistic::sum('red_cards');
            
            return $this->successResponse([
                'total_matches' => $totalMatches,
                'played_matches' => $playedMatches,
                'upcoming_matches' => $totalMatches - $playedMatches,
                'total_goals' => $totalGoals,
                'total_yellow_cards' => $totalYellowCards,
                'total_red_cards' => $totalRedCards,
            ]);
        } else {
            // Player sees personal statistics
            $totalGoals = $user->getTotalGoals();
            $totalAssists = $user->getTotalAssists();
            $totalYellowCards = $user->getTotalYellowCards();
            $totalRedCards = $user->getTotalRedCards();
            $totalHandballs = $user->getTotalHandballs();
            
            // Get matches played
            $matchesPlayed = PlayerStatistic::where('user_id', $user->id)
                ->distinct('match_id')
                ->count();
                
            return $this->successResponse([
                'matches_played' => $matchesPlayed,
                'goals' => $totalGoals,
                'assists' => $totalAssists,
                'yellow_cards' => $totalYellowCards,
                'red_cards' => $totalRedCards,
                'handballs' => $totalHandballs,
            ]);
        }
    }

    /**
     * Get recent activities for the dashboard
     */
    public function activities()
    {
        $user = Auth::user();
        
        // Get recent notifications
        $notifications = Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
            
        // Get recent matches the user participated in (if player)
        $recentMatches = [];
        if (!$user->isAdmin()) {
            $matchIds = PlayerStatistic::where('user_id', $user->id)
                ->pluck('match_id');
                
            $recentMatches = FootballMatch::with(['homeTeam', 'awayTeam'])
                ->whereIn('id', $matchIds)
                ->orderBy('match_date', 'desc')
                ->take(5)
                ->get();
        }
        
        return $this->successResponse([
            'notifications' => $notifications,
            'recent_matches' => $recentMatches,
        ]);
    }
}