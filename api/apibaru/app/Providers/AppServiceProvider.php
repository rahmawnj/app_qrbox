<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Providers\ConfigUserProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::provider('config_user', function ($app, array $config) {
        return new ConfigUserProvider($config['config']);
    });
        Paginator::useBootstrap();
    }
}
