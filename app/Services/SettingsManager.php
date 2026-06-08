<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsManager
{
    public static function siteName(string $default = 'BingLOGy'): string
    {
        $siteName = self::get('site_name', config('app.name', $default));

        return is_string($siteName) && trim($siteName) !== ''
            ? trim($siteName)
            : $default;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            return $default;
        }

        $decoded = json_decode($setting->value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $setting->value;
    }

    public static function set(string $key, mixed $value): void
    {
        if (! is_string($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
