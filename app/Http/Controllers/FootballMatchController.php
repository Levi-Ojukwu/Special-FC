<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\NotificationService;


class FootballMatchController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy', 'startMatch', 'updateScore']);
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of all matches.
     */
    public function index()
    {
        $matches = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->orderBy('match_date', 'desc')
            ->get();
            
        return $this->successResponse($matches);
    }

    /**
     * Start a match.
     */
    public function startMatch(FootballMatch $match)
    {
        if ($match->is_played) {
            return response()->json(['message' => 'Match already played'], 400);
        }

        if ($match->is_live) {
            return response()->json(['message' => 'Match is already live'], 400);
        }

        $match->is_live = true; // explicitly set
        $match->save(); 

        // $match->update([
        //     'is_live' => true,
        // ]);

        // Load relationships for response
        $match->load(['homeTeam', 'awayTeam']);

        // Notify users about match start
        $this->notificationService->notifyAllUsers(
            'Match Started',
            "The match between {$match->homeTeam->name} and {$match->awayTeam->name} has started!",
            'match'
        );

        return response()->json([
            'message' => 'Match started successfully',
            'data' => $match,
        ]);
    }

    /**
     * Update match score - NEW METHOD
     */
    public function updateScore(Request $request, FootballMatch $match)
    {
        $validated = $this->validateRequest($request, [
            'home_team_score' => 'required|integer|min:0',
            'away_team_score' => 'required|integer|min:0',
            'is_played' => 'sometimes|boolean',
            'statistics' => 'sometimes|array',
        ]);

        // Update match scores
        $match->update([
            'home_team_score' => $validated['home_team_score'],
            'away_team_score' => $validated['away_team_score'],
            'is_played' => $validated['is_played'] ?? $match->is_played,
            'is_live' => $validated['is_played'] ?? false ? false : $match->is_live, // End live status if match is completed
        ]);

        // Update team statistics if match is completed
        if ($validated['is_played'] ?? false) {
            $match->updateTeamStats();
            
            // Load relationships for notification
            $match->load(['homeTeam', 'awayTeam']);
            
            // Notify all users about the match result
            $this->notificationService->notifyAllUsers(
                'Match Result',
                "Final Score: {$match->homeTeam->name} {$match->home_team_score} - {$match->away_team_score} {$match->awayTeam->name}",
                'match'
            );
        }

        return $this->successResponse($match, 'Match score updated successfully');
    }


    /**
     * Display a listing of upcoming matches (fixtures).
     */
    public function fixtures()
    {
        $fixtures = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('is_played', false)
            ->orderBy('match_date', 'asc')
            ->get();
            
        return $this->successResponse($fixtures);
    }

    /**
     * Display a listing of played matches (results).
     */
    public function results()
    {
        $results = FootballMatch::with(['homeTeam', 'awayTeam'])
            ->where('is_played', true)
            ->orderBy('match_date', 'desc')
            ->get();
            
        return $this->successResponse($results);
    }

    /**
     * Store a newly created match in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request, [
            'home_team_id' => 'required|exists:teams,id',
            'away_team_id' => 'required|exists:teams,id|different:home_team_id',
            'match_date' => 'required|date',
        ]);
        
        $match = FootballMatch::create([
            'home_team_id' => $validated['home_team_id'],
            'away_team_id' => $validated['away_team_id'],
            'match_date' => $validated['match_date'],
            'home_team_score' => 0,
            'away_team_score' => 0,
            'is_played' => false,
            'is_live' => false,
        ]);

        // Load relationships
        $match->load(['homeTeam', 'awayTeam']);
        
        // Notify all users about the new match
        $this->notificationService->notifyAllUsers(
            'New Match Scheduled',
            "A new match between {$match->homeTeam->name} and {$match->awayTeam->name} has been scheduled for " . date('d M Y H:i', strtotime($match->match_date)),
            'match'
        );
        
        return $this->successResponse($match, 'Match created successfully', 201);
    }

    /**
     * Display the specified match.
     */
    public function show($id)
    {
        $match = FootballMatch::with(['homeTeam', 'awayTeam', 'statistics.user'])
            ->findOrFail($id);
            
        return $this->successResponse($match);
    }

    /**
     * Update the specified match in storage.
     */
    public function update(Request $request, $id)
    {
        $match = FootballMatch::findOrFail($id);
        
        $validated = $this->validateRequest($request, [
            'home_team_id' => 'sometimes|required|exists:teams,id',
            'away_team_id' => 'sometimes|required|exists:teams,id|different:home_team_id',
            'home_team_score' => 'sometimes|required|integer|min:0',
            'away_team_score' => 'sometimes|required|integer|min:0',
            'match_date' => 'sometimes|required|date',
            'is_played' => 'sometimes|boolean',
            'is_live' => 'sometimes|boolean',
        ]);
        
        // Check if match result is being updated
        $resultUpdated = false;
        if (isset($validated['is_played']) && $validated['is_played'] && !$match->is_played) {
            $resultUpdated = true;
        }
        
        $match->update($validated);
        
        // Update team statistics if match result is updated
        if ($resultUpdated) {
            $match->updateTeamStats();
            
            // Notify all users about the match result
            $this->notificationService->notifyAllUsers(
                'Match Result',
                "Match result: {$match->home_team->name} {$match->home_team_score} - {$match->away_team_score} {$match->away_team->name}",
                'match'
            );
        }
        
        return $this->successResponse($match, 'Match updated successfully');
    }

    /**
     * Remove the specified match from storage.
     */
    public function destroy(string $id)
    {
        $match = FootballMatch::findOrFail($id);
        
        // Check if match has statistics
        if ($match->statistics()->count() > 0) {
            return $this->errorResponse('Cannot delete match with statistics. Please delete statistics first.', 422);
        }
        
        $match->delete();
        
        return $this->successResponse(null, 'Match deleted successfully');
    }
}
