<?php

namespace App\Providers;

use App\Models\FootballMatch;
use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;
use App\Services\FileUploadService;
use App\Services\NotificationService;
use App\Repositories\UserRepository;
use App\Repositories\TeamRepository;
use App\Repositories\MatchRepository;
use App\Models\User;
use App\Models\Team;
use App\Models\Match;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(FileUploadService::class, function ($app) {
            return new FileUploadService();
        });
        
        $this->app->singleton(NotificationService::class, function ($app) {
            return new NotificationService();
        });
        
        $this->app->singleton(UserRepository::class, function ($app) {
            return new UserRepository(new User());
        });
        
        $this->app->singleton(TeamRepository::class, function ($app) {
            return new TeamRepository(new Team());
        });
        
        $this->app->singleton(MatchRepository::class, function ($app) {
            return new MatchRepository(new FootballMatch());
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
