<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Mantax559\LaravelSettings\Models\Setting;
use Mantax559\LaravelSettings\Observers\SettingObserver;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [];

    public function boot(): void
    {
        Setting::observe(SettingObserver::class);
    }

    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
