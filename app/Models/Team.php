<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'matches_played',
        'matches_won',
        'matches_drawn',
        'matches_lost',
        'goals_for',
        'goals_against',
        'goal_difference',
        'points',
    ];

    public function players()
    {
        return $this->hasMany(User::class);
    }

    public function homeMatches()
    {
        return $this->hasMany(FootballMatch::class, 'home_team_id');
    }

    public function awayMatches()
    {
        return $this->hasMany(FootballMatch::class, 'away_team_id');
    }

    public function updateStats()
    {
        $this->matches_played = $this->homeMatches()->where('is_played', true)->count() + 
                               $this->awayMatches()->where('is_played', true)->count();
        
        $this->matches_won = $this->homeMatches()
            ->where('is_played', true)
            ->where('home_team_score', '>', 'away_team_score')
            ->count() + 
            $this->awayMatches()
            ->where('is_played', true)
            ->where('away_team_score', '>', 'home_team_score')
            ->count();
            
        $this->matches_drawn = $this->homeMatches()
            ->where('is_played', true)
            ->whereColumn('home_team_score', 'away_team_score')
            ->count() + 
            $this->awayMatches()
            ->where('is_played', true)
            ->whereColumn('away_team_score', 'home_team_score')
            ->count();
            
        $this->matches_lost = $this->homeMatches()
            ->where('is_played', true)
            ->where('home_team_score', '<', 'away_team_score')
            ->count() + 
            $this->awayMatches()
            ->where('is_played', true)
            ->where('away_team_score', '<', 'home_team_score')
            ->count();
            
        $this->goals_for = $this->homeMatches()->where('is_played', true)->sum('home_team_score') + 
                          $this->awayMatches()->where('is_played', true)->sum('away_team_score');
                          
        $this->goals_against = $this->homeMatches()->where('is_played', true)->sum('away_team_score') + 
                              $this->awayMatches()->where('is_played', true)->sum('home_team_score');
                              
        $this->goal_difference = $this->goals_for - $this->goals_against;
        
        $this->points = ($this->matches_won * 3) + $this->matches_drawn;
        
        $this->save();
    }
}
