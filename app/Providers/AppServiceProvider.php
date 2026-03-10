<?php

namespace App\Providers;

use Illuminate\Foundation\Console\ServeCommand;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

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
        if (PHP_OS_FAMILY === 'Windows' && ! in_array('SystemRoot', ServeCommand::$passthroughVariables, true)) {
            ServeCommand::$passthroughVariables[] = 'SystemRoot';
        }

        // Keep Vite's hot marker out of /public to avoid stale /public/hot forcing missing dev-server assets.
        Vite::useHotFile(storage_path('vite.hot'));
    }
}
