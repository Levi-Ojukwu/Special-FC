<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PlayerStatistic;
use App\Models\User;
use App\Models\FootballMatch;

class PlayerStatisticSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all played matches
        $matches = FootballMatch::where('is_played', true)->get();
        
        if ($matches->count() === 0) {
            $this->command->error('No played matches found. Please run match seeder first.');
            return;
        }

        $statisticsCount = 0;

        foreach ($matches as $match) {
            // Get players from both teams
            $homeTeamPlayers = User::where('team_id', $match->home_team_id)
                ->where('role', 'player')
                ->get();
                
            $awayTeamPlayers = User::where('team_id', $match->away_team_id)
                ->where('role', 'player')
                ->get();
                
            // Add statistics for home team players
            foreach ($homeTeamPlayers as $player) {
                // Make sure total goals match the team score
                $goals = 0;
                if ($match->home_team_score > 0) {
                    $goals = rand(0, min(2, $match->home_team_score));
                }
                
                PlayerStatistic::create([
                    'user_id' => $player->id,
                    'match_id' => $match->id,
                    'goals' => $goals,
                    'assists' => rand(0, 2),
                    'yellow_cards' => rand(0, 1),
                    'red_cards' => rand(0, 1) > 0.9 ? 1 : 0, // Less likely to get a red card
                    'handballs' => rand(0, 1),
                ]);
                
                $statisticsCount++;
            }
            
            // Add statistics for away team players
            foreach ($awayTeamPlayers as $player) {
                // Make sure total goals match the team score
                $goals = 0;
                if ($match->away_team_score > 0) {
                    $goals = rand(0, min(2, $match->away_team_score));
                }
                
                PlayerStatistic::create([
                    'user_id' => $player->id,
                    'match_id' => $match->id,
                    'goals' => $goals,
                    'assists' => rand(0, 2),
                    'yellow_cards' => rand(0, 1),
                    'red_cards' => rand(0, 1) > 0.9 ? 1 : 0, // Less likely to get a red card
                    'handballs' => rand(0, 1),
                ]);
                
                $statisticsCount++;
            }
        }
        
        $this->command->info($statisticsCount . ' player statistics created successfully.');
    }
}