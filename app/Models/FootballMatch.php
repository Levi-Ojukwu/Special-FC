<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FootballMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'home_team_id',
        'away_team_id',
        'match_date',
        'home_team_score',
        'away_team_score',
        'is_played',
    ];

    protected $casts = [
        'match_date' => 'datetime',
        'is_played' => 'boolean',
    ];

    public function homeTeam()
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function awayTeam()
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function statistics()
    {
        return $this->hasMany(PlayerStatistic::class);
    }

    public function updateTeamStats()
    {
        $this->homeTeam->updateStats();
        $this->awayTeam->updateStats();
    }
}
