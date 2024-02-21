<?php

namespace Mantax559\LaravelSettings\Providers;

use Illuminate\Support\ServiceProvider;
use Mantax559\LaravelSettings\Models\Setting;
use Mantax559\LaravelSettings\Observers\SettingObserver;

class AppServiceProvider extends ServiceProvider
{
    private const PATH_CONFIG = __DIR__.'/../../config/laravel-settings.php';

    private const PATH_MIGRATIONS = __DIR__.'/../../database/migrations';

    public function boot(): void
    {
        $this->publishes([
            self::PATH_CONFIG => config_path('laravel-settings.php'),
        ], 'config');

        $this->loadMigrationsFrom(self::PATH_MIGRATIONS);

        Setting::observe(SettingObserver::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::PATH_CONFIG, 'laravel-settings');
    }
}
