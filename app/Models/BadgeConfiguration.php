<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeConfiguration extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'badge_id',
        'image_path',
    ];

    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }
}
