<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Http\Controllers\Controller;
use App\Models\FootballMatch;
use App\Models\Team;
use App\Services\NotificationService;


class FootballMatchController extends BaseController
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
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
        ]);
        
        // Notify all users about the new match
        $homeTeam = Team::find($validated['home_team_id']);
        $awayTeam = Team::find($validated['away_team_id']);
        
        $this->notificationService->notifyAllUsers(
            'New Match Scheduled',
            "A new match between {$homeTeam->name} and {$awayTeam->name} has been scheduled for " . $match->match_date->format('d M Y H:i'),
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
                "Match result: {$match->homeTeam->name} {$match->home_team_score} - {$match->away_team_score} {$match->awayTeam->name}",
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
