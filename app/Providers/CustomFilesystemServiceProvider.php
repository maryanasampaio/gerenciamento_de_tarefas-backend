<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Filesystem\Filesystem;

class CustomFilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        // Registra o binding 'files' no container
        $this->app->singleton('files', function ($app) {
            return new Filesystem();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        //
    }
}
