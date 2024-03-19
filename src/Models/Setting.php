<?php

namespace Mantax559\LaravelSettings\Models;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Mantax559\LaravelSettings\Enums\SettingTypeEnum;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'type' => SettingTypeEnum::class,
    ];

    public $timestamps = true;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('laravel-settings.table'));
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
    public static function get(string $key, bool $cache = true): mixed
    {
        $cacheKey = self::formatCacheKey($key);
        $value = Cache::get($cacheKey);

        if (Cache::missing($cacheKey) || empty($value) || ! $cache) {
            $setting = self::retrieveSettingByKey($key);

            $value = match ($setting->type) {
                SettingTypeEnum::Array => self::decodeJson($key, $value),
                SettingTypeEnum::String => (string) $value,
                SettingTypeEnum::Float => (float) $value,
                SettingTypeEnum::Integer => (int) $value,
                SettingTypeEnum::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
                default => $value,
            };

            Cache::forever($cacheKey, $value);
        }

        return $value;
    }

    /**
     * @throws Exception
     */
    public static function set(string $key, mixed $value): mixed
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);

        return self::get($key);
    }

    public static function formatCacheKey(string $key): string
    {
        $key = implode('.', [config('laravel-settings.cache_key_prefix'), $key]);

        if (config('laravel-settings.encryption')) {
            $key = md5($key);
        }

        return $key;
    }

    private static function retrieveSettingByKey(string $key): Setting
    {
        $setting = self::where('key', $key)->first();

        if (! $setting) {
            throw new Exception("Setting key '$key' doesn\'t exist!");
        }

        return $setting;
    }

    private static function decodeJson(string $key, string $value): mixed
    {
        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decoding error for setting key '$key': ".json_last_error_msg());
        }

        return $decoded;
    }
}
