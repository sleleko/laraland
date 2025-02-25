<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'sort_order',
        'description',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Получить компоненты, связанные с этой секцией
     */
    public function components(): HasMany
    {
        return $this->hasMany(SectionComponent::class)
            ->orderBy('sort_order');
    }

    /**
     * Очистить кеш связанный с секциями при изменении
     */
    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('landing_sections');
        });

        static::deleted(function () {
            Cache::forget('landing_sections');
        });
    }
}
