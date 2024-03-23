<?php

namespace Mantax559\LaravelSettings\Observers;

use Illuminate\Support\Facades\Cache;
use Mantax559\LaravelSettings\Enums\SettingTypeEnum;
use Mantax559\LaravelSettings\Models\Setting;

class SettingObserver
{
    public function creating(Setting $setting): void
    {
        $this->encodeValueIfNeeded($setting);
    }

    public function updating(Setting $setting): void
    {
        $this->encodeValueIfNeeded($setting);
    }

    public function updated(Setting $setting): void
    {
        Cache::forget(Setting::formatCacheKey($setting->key));
    }

    public function deleted(Setting $setting): void
    {
        Cache::forget(Setting::formatCacheKey($setting->key));
    }

    private function encodeValueIfNeeded(Setting $setting): void
    {
        if ($setting->type === SettingTypeEnum::Array) {
            $setting->value = json_encode($setting->value);
        }
    }
}
