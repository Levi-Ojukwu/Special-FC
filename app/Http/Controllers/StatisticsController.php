<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PlayerStatistic;
use App\Models\User;
use App\Models\FootballMatch;

class StatisticsController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }
    /**
     * Display a listing of player statistics.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'player')
            ->where('is_verified', true)
            ->with('team');
            
        // Filter by team if provided
        if ($request->has('team_id')) {
            $query->where('team_id', $request->team_id);
        }
        
        $players = $query->get();
        
        // Calculate statistics for each player
        $playerStats = $players->map(function ($player) {
            return [
                'id' => $player->id,
                // 'name' => $player->first_name . ' ' . $player->last_name,
                'username' => $player->username,
                'team' => $player->team ? $player->team->name : null,
                'goals' => $player->getTotalGoals(),
                'assists' => $player->getTotalAssists(),
                'yellow_cards' => $player->getTotalYellowCards(),
                'red_cards' => $player->getTotalRedCards(),
                'handballs' => $player->getTotalHandballs(),
            ];
        });
        
        // Sort by goals (descending)
        $playerStats = $playerStats->sortByDesc('goals')->values();
        
        return $this->successResponse($playerStats);
    }

    /**
     * Store a newly created player statistics in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request, [
            'user_id' => 'required|exists:users,id',
            'match_id' => 'required|exists:matches,id',
            'goals' => 'required|integer|min:0',
            'assists' => 'required|integer|min:0',
            'yellow_cards' => 'required|integer|min:0|max:2',
            'red_cards' => 'required|integer|min:0|max:1',
            'handballs' => 'required|integer|min:0',
        ]);
        
        // Check if statistic already exists
        $existingStat = PlayerStatistic::where('user_id', $validated['user_id'])
            ->where('match_id', $validated['match_id'])
            ->first();
            
        if ($existingStat) {
            return $this->errorResponse('Statistic already exists for this player and match', 422);
        }
        
        // Check if match is played
        $match = FootballMatch::find($validated['match_id']);
        if (!$match->is_played) {
            return $this->errorResponse('Cannot add statistics for a match that has not been played', 422);
        }
        
        $statistic = PlayerStatistic::create($validated);
        
        return $this->successResponse($statistic, 'Statistic created successfully', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        //
    }

    /**
     * Update the specified player statistics in storage.
     */
    public function update(Request $request, $id)
    {
        $statistic = PlayerStatistic::findOrFail($id);
        
        $validated = $this->validateRequest($request, [
            'goals' => 'sometimes|required|integer|min:0',
            'assists' => 'sometimes|required|integer|min:0',
            'yellow_cards' => 'sometimes|required|integer|min:0|max:2',
            'red_cards' => 'sometimes|required|integer|min:0|max:1',
            'handballs' => 'sometimes|required|integer|min:0',
        ]);
        
        $statistic->update($validated);
        
        return $this->successResponse($statistic, 'Statistic updated successfully');
    }

    /**
     * Remove the specified player statistics from storage.
     */
    public function destroy($id)
    {
        $statistic = PlayerStatistic::findOrFail($id);
        $statistic->delete();
        
        return $this->successResponse(null, 'Statistic deleted successfully');
    }
}
