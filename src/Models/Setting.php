<?php

namespace Mantax559\LaravelSettings\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    protected $guarded = ['id'];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('settings.table'));
    }

    public static function isEmpty(string $key): bool
    {
        try {
            return empty(self::get($key));
        } catch (Exception $e) {
            return true;
        }
    }

    /**
     * @throws Exception
     */
    public static function get(string $key): ?string
    {
        $cachedValue = Cache::get(self::formatCacheKey($key));

        if (empty($cachedValue)) {
            $setting = self::retrieveSettingByKey($key);

            Cache::forever(self::formatCacheKey($key), $setting->value);

            return $setting->value;
        }

        return $cachedValue;
    }

    public static function set(string $key, ?string $value): ?string
    {
        $setting = Setting::updateOrCreate(['key' => $key], ['value' => $value]);

        return $setting->value;
    }

    public static function formatCacheKey(string $key): string
    {
        $key = implode('.', [config('settings.cache_key_prefix'), $key]);

        if (config('settings.encryption')) {
            $key = md5($key);
        }

        return $key;
    }

    private static function retrieveSettingByKey(string $key): Setting
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            throw new Exception(__('Setting key :key doesn\'t exist!', ['key' => $key]));
        }

        return $setting;
    }
}
