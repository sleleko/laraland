<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Статический метод для быстрого получения настроек по ключу
     */
    public static function getValue(string $key, $default = null)
    {
        $config = Cache::remember('site_config_' . $key, 3600, function () use ($key) {
            return self::where('key', $key)->first();
        });

        return $config ? $config->value : $default;
    }

    /**
     * Очистить кеш при изменении настроек
     */
    protected static function booted(): void
    {
        static::saved(function (SiteConfig $config) {
            Cache::forget('site_config_' . $config->key);
            Cache::forget('all_site_configs');
        });
    }
}
