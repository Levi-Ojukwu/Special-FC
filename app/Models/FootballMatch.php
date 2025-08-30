<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    use HasFactory;

    // Specify the table name if it's different from the default
    protected $table = 'matches';

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'match_date',
        'home_team_score',
        'away_team_score',
        'is_played',
        'is_live',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'is_played' => 'boolean',
    ];

    /**
     * Get the home team of the match
     */
    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team of the match
     */
    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the statistics of the match
     */
    public function statistics()
    {
        return $this->hasMany(PlayerStatistic::class, 'match_id');
    }

    /**
     * Update team statistics after match result
     */
    public function updateTeamStats()
    {
        $this->homeTeam->updateStats();
        $this->awayTeam->updateStats();
    }
}
