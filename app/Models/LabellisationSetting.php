<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LabellisationSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'label',
        'description',
    ];

    /**
     * Récupère une valeur de paramètre par sa clé.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        return Cache::remember("labellisation_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            return $setting?->value ?? $default;
        });
    }

    /**
     * Définit une valeur de paramètre.
     */
    public static function setValue(string $key, string $value): void
    {
        self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("labellisation_setting_{$key}");
    }

    /**
     * Récupère l'échelle de notation configurée.
     */
    public static function getNoteScale(): int
    {
        return (int) self::getValue('note_scale', 20);
    }
}
