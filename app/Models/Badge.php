<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Badge extends Model
{
    use HasUuids;

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'label',
        'min_score',
        'max_score',
    ];

    /**
     * Get the candidatures that received this badge.
     */
    public function candidatures(): HasMany
    {
        return $this->hasMany(Candidature::class);
    }

    /**
     * Get the configuration for this badge.
     */
    public function configuration(): HasOne
    {
        return $this->hasOne(BadgeConfiguration::class);
    }

    /**
     * Get the emoji for this badge.
     */
    public function getEmoji(): string
    {
        return match ($this->name) {
            'junior' => '🥉',
            'intermediaire' => '🥈',
            'senior' => '🥇',
            default => '🏅',
        };
    }

    /**
     * Get the badge image URL.
     */
    public function getImageUrl(): ?string
    {
        if ($this->configuration && $this->configuration->image_path) {
            return asset('storage/'.$this->configuration->image_path);
        }

        return null;
    }
}
