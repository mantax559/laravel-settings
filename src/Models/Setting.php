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
        return self::isValueEmpty(self::get($key));
    }

    public static function get(string $key, bool $cache = true): mixed
    {
        $cacheKey = self::formatCacheKey($key);
        $cacheValue = Cache::get($cacheKey);

        if (self::isValueEmpty($cacheValue) || ! $cache) {
            $setting = self::retrieveSettingByKey($key);
            $cacheValue = ['value' => $setting->value, 'type' => $setting->type];
            Cache::forever($cacheKey, $cacheValue);
        }

        $value = match ($cacheValue['type']) {
            SettingTypeEnum::Json => self::validateJson($cacheValue['value']),
            SettingTypeEnum::String => (string) $cacheValue['value'],
            SettingTypeEnum::Float => (float) $cacheValue['value'],
            SettingTypeEnum::Integer => (int) $cacheValue['value'],
            SettingTypeEnum::Boolean => filter_var($cacheValue['value'], FILTER_VALIDATE_BOOLEAN),
            default => throw new Exception("Value type '{$cacheValue['type']}' is not specified!"),
        };

        return $value;
    }

    public static function set(string $key, string|array|null $value, string|SettingTypeEnum $settingTypeEnum): mixed
    {
        if (is_string($settingTypeEnum)) {
            $settingTypeEnum = SettingTypeEnum::getEnumByString($settingTypeEnum);
        }

        $key = self::formatKey($key);
        $value = self::formatValue($value, $settingTypeEnum);

        self::updateOrCreate(['key' => $key], ['value' => $value, 'type' => $settingTypeEnum]);

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
        }

        return $setting;
    }

    private static function formatKey(string $key): string
    {
        $key = trim($key);
        $key = mb_strtolower($key);
        $key = preg_replace('/\s+/', ' ', $key);
        $key = str_replace(' ', '_', $key);

        if (empty($key)) {
            throw new Exception('Setting key cannot be empty!');
        }

        return $key;
    }

    private static function formatValue(string|array|null $value, SettingTypeEnum $settingTypeEnum): ?string
    {
        if (cmprenum($settingTypeEnum, SettingTypeEnum::Json)) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            self::validateJson($value);
        } else {
            $value = trim($value);
            $value = preg_replace('/\s+/', ' ', $value);

            if (self::isValueEmpty($value)) {
                return null;
            }
        }

        return $value;
    }

    private static function isValueEmpty(string|array|null $value): bool
    {
        return empty($value) && ! cmprstr($value, '0');
    }

    private static function validateJson(?string $value): array
    {
        $decodedJson = json_decode($value, true);

        if (! cmprint(json_last_error(), JSON_ERROR_NONE)) {
            throw new Exception('The provided value is in the wrong JSON format. Error: '.json_last_error_msg());
        }

        return $decodedJson;
    }
}
