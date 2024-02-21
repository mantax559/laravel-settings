<?php

namespace Mantax559\LaravelSettings\Providers;

use App\Providers\EventServiceProvider;
use Illuminate\Support\ServiceProvider;
use Mantax559\LaravelSettings\Models\Setting;
use Mantax559\LaravelSettings\Observers\SettingObserver;

class AppServiceProvider extends ServiceProvider
{
    private const CONFIG_FILE = __DIR__.'/../../config/laravel-settings.php';

    private const MIGRATION_FILES = __DIR__.'/../../database/migrations';

    public function boot(): void
    {
        $this->publishes([
            self::CONFIG_FILE => config_path('laravel-settings.php'),
        ], 'config');

        $this->loadMigrationsFrom(self::MIGRATION_FILES);

        Setting::observe(SettingObserver::class);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_FILE, 'laravel-settings');
    }
}
