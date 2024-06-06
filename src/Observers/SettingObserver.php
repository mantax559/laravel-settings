<?php

namespace Mantax559\LaravelSettings\Observers;

use Illuminate\Support\Facades\Cache;
use Mantax559\LaravelSettings\Models\Setting;

class SettingObserver
{
    public function updated(Setting $setting): void
    {
        Cache::forget(Setting::formatCacheKey($setting->key));
    }

    public function deleted(Setting $setting): void
    {
        Cache::forget(Setting::formatCacheKey($setting->key));
    }
}
