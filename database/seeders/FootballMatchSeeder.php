<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FootballMatch;
use App\Models\Team;
use Carbon\Carbon;

class FootballMatchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = Team::all();
        
        if ($teams->count() < 2) {
            $this->command->error('Not enough teams to create matches. Please run team seeder first.');
            return;
        }

        $pastMatchCount = 0;
        $futureMatchCount = 0;

        // Create some past matches (played)
        for ($i = 0; $i < 5; $i++) {
            $homeTeam = $teams->random();
            $awayTeam = $teams->except($homeTeam->id)->random();
            $homeScore = rand(0, 5);
            $awayScore = rand(0, 5);
            
            FootballMatch::create([
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'match_date' => Carbon::now()->subDays(rand(1, 30)),
                'home_team_score' => $homeScore,
                'away_team_score' => $awayScore,
                'is_played' => true,
            ]);
            
            $pastMatchCount++;
        }

        // Create some future matches (not played)
        for ($i = 0; $i < 5; $i++) {
            $homeTeam = $teams->random();
            $awayTeam = $teams->except($homeTeam->id)->random();
            
            FootballMatch::create([
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'match_date' => Carbon::now()->addDays(rand(1, 30)),
                'home_team_score' => 0,
                'away_team_score' => 0,
                'is_played' => false,
            ]);
            
            $futureMatchCount++;
        }
        
        $this->command->info($pastMatchCount . ' past matches and ' . $futureMatchCount . ' future matches created successfully.');
        
        // Update team statistics based on match results
        $this->command->info('Updating team statistics...');
        foreach (FootballMatch::where('is_played', true)->get() as $match) {
            $match->updateTeamStats();
        }
        $this->command->info('Team statistics updated successfully.');
    }
}