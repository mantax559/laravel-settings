<?php

use Mantax559\LaravelSettings\Models\Setting;

if (! function_exists('setting')) {
    function setting(string $key, bool $cache = true): ?string
    {
        return Setting::get($key, $cache);
    }
}
