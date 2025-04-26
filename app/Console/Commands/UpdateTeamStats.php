<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Team;

class UpdateTeamStats extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teams:update-stats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update statistics for all teams';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $teams = Team::all();
        $count = 0;
        
        foreach ($teams as $team) {
            $team->updateStats();
            $count++;
        }
        
        $this->info("Updated statistics for {$count} teams.");
        
        return 0;
    }
}
