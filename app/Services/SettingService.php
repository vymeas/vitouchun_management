<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService
{
    /**
     * Get a setting value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting_{$key}", 3600, function () use ($key, $default) {
            return Setting::get($key, $default);
        });
    }

    /**
     * Set a setting value and clear cache
     */
    public static function set(string $key, mixed $value, string $type = 'string'): void
    {
        Setting::set($key, $value, $type);
        Cache::forget("setting_{$key}");
    }

    /**
     * Get all settings as an array (for forms)
     */
    public static function all(): array
    {
        return Setting::all()->mapWithKeys(fn($s) => [$s->key => $s->value])->toArray();
    }

    /**
     * Update multiple settings at once
     */
    public static function bulkUpdate(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $existing = Setting::where('key', $key)->first();
            if ($existing) {
                $existing->update(['value' => $value]);
                Cache::forget("setting_{$key}");
            }
        }
    }
}
