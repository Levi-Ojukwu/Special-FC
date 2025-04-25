<?php

namespace App\Repositories;

use App\Models\FootballMatch;

class MatchRepository
{
    protected $match;

    public function __construct(FootballMatch $match)
    {
        $this->match = $match;
    }

    public function getAll()
    {
        return $this->match->all();
    }

    public function getById($id)
    {
        return $this->match->find($id);
    }

    public function create(array $data)
    {
        return $this->match->create($data);
    }

    public function update($id, array $data)
    {
        $match = $this->getById($id);
        if ($match) {
            $match->update($data);
            return $match;
        }
        return null;
    }

    public function delete($id)
    {
        $match = $this->getById($id);
        if ($match) {
            return $match->delete();
        }
        return false;
    }

    public function getFixtures()
    {
        return $this->match->with(['homeTeam', 'awayTeam'])
            ->where('is_played', false)
            ->orderBy('match_date', 'asc')
            ->get();
    }

    public function getResults()
    {
        return $this->match->with(['homeTeam', 'awayTeam'])
            ->where('is_played', true)
            ->orderBy('match_date', 'desc')
            ->get();
    }

    public function updateScore($id, $homeScore, $awayScore)
    {
        $match = $this->getById($id);
        if ($match) {
            $match->home_team_score = $homeScore;
            $match->away_team_score = $awayScore;
            $match->is_played = true;
            $match->save();
            
            // Update team statistics
            $match->updateTeamStats();
            
            return $match;
        }
        return null;
    }
}