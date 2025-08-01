<?php

namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use App\Filament\Widgets\DashboardStats;
use Illuminate\Support\ServiceProvider;
use Filament\Facades\Filament;
use Illuminate\Support\Str;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', function (User $user) {
            return $user->hasRole('super_admin');
        });
        Scramble::routes(function (Route $route) {
            return Str::startsWith($route->uri, 'api/');
        });
        
    }
}
