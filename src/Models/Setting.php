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
        return self::isEmptyAndNotEqualToZero(self::get($key));
    }

    /**
     * @throws Exception
     */
    public static function get(string $key, bool $cache = true): mixed
    {
        $cacheKey = self::formatCacheKey($key);
        $cacheValue = Cache::get($cacheKey);

        if (empty($cacheValue) || ! $cache) {
            $setting = self::retrieveSettingByKey($key);
            $cacheValue = ['value' => $setting->value, 'type' => $setting->type];
            Cache::forever($cacheKey, $cacheValue);
        }

        $value = match ($cacheValue['type']) {
            SettingTypeEnum::Array => self::decodeJson($key, $cacheValue['value']),
            SettingTypeEnum::String => (string) $cacheValue['value'],
            SettingTypeEnum::Float => (float) $cacheValue['value'],
            SettingTypeEnum::Integer => (int) $cacheValue['value'],
            SettingTypeEnum::Boolean => filter_var($cacheValue['value'], FILTER_VALIDATE_BOOLEAN),
            default => $cacheValue['value'],
        };

        return $value;
    }

    /**
     * @throws Exception
     */
    public static function set(string $key, mixed $value): mixed
    {
        $key = self::formatKey($key);
        $value = self::formatValue($value);

        self::updateOrCreate(['key' => $key], ['value' => $value]);

        return self::get($key, false);
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
        } elseif (self::isEmptyAndNotEqualToZero($setting->value)) {
            throw new Exception("Setting key '$key' value is empty!");
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

    /**
     * @throws Exception
     */
    private static function formatKey(string $key): string
    {
        $key = trim($key);
        $key = strtolower($key);
        $key = str_replace(' ', '_', $key);

        if (empty($key)) {
            throw new Exception('Setting key cannot be empty!');
        }

        return $key;
    }

    private static function formatValue(string $value): string
    {
        $value = trim($value);

        if (self::isEmptyAndNotEqualToZero($value)) {
            $value = null;
        }

        return $value;
    }

    private static function isEmptyAndNotEqualToZero(?string $value): bool
    {
        return empty($value) && $value !== 0 && $value !== '0';
    }
}
