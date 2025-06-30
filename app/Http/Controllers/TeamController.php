<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
// use App\Services\FileUploadService;

class TeamController extends BaseController
{
    protected $fileUploadService;

    public function __construct()
    {
        // $this->middleware('auth:api');
        // $this->middleware('admin')->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the teams.
     */
    public function index()
    {
        $teams = Team::orderBy('points', 'desc')
            ->orderBy('goal_difference', 'desc')
            ->get();
            
        return $this->successResponse($teams);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateRequest($request, [
            'name' => 'required|string|max:255|unique:teams',
        ]);


        
        $team = Team::create([
            'name' => $request->name,
            'matches_played' => 0,
            'matches_won' => 0,
            'matches_drawn' => 0,
            'matches_lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goal_difference' => 0,
            'points' => 0,
        ]);
                
        return $this->successResponse($team, 'Team created successfully', 201);
    }

    /**
     * Display the specified team.
     */
    public function show($id)
    {
        $team = Team::with('players')->findOrFail($id);
        
        return $this->successResponse($team);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        
        $validated = $this->validateRequest($request, [
            'name' => 'sometimes|required|string|max:255|unique:teams,name,' . $id,
            'matches_played' => 'sometimes|integer|min:0',
            'matches_won' => 'sometimes|integer|min:0',
            'matches_drawn' => 'sometimes|integer|min:0',
            'matches_lost' => 'sometimes|integer|min:0',
            'goals_for' => 'sometimes|integer|min:0',
            'goals_against' => 'sometimes|integer|min:0',
            'goal_difference' => 'sometimes|integer',
            'points' => 'sometimes|integer|min:0',
        ]);
            
        
        $team->update($validated);
        
        return $this->successResponse($team, 'Team updated successfully');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy($id)
    {
        $team = Team::findOrFail($id);
        
        // Check if team has players
        if ($team->players()->count() > 0) {
            return $this->errorResponse('Cannot delete team with players. Please reassign players first.', 422);
        }
        
        $team->delete();
        
        return $this->successResponse(null, 'Team deleted successfully');
    }
}