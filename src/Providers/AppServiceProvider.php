<?php

namespace Mantax559\LaravelSettings\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    private const CONFIG_FILE = __DIR__.'/../../config/laravel-settings.php';

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_FILE => config_path('laravel-settings.php'),
        ], 'config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_FILE, 'laravel-settings');
    }
}
