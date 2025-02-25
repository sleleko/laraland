<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SectionComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'type',
        'title',
        'subtitle',
        'content',
        'image_path',
        'button_text',
        'button_link',
        'is_active',
        'sort_order',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'settings' => 'array',
    ];

    /**
     * Связь с секцией
     */
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * Очистить кеш при изменении компонента
     */
    protected static function booted(): void
    {
        static::saved(function (SectionComponent $component) {
            Cache::forget('section_' . $component->section_id);
            Cache::forget('landing_sections');
        });

        static::deleted(function (SectionComponent $component) {
            Cache::forget('section_' . $component->section_id);
            Cache::forget('landing_sections');
        });
    }
}
