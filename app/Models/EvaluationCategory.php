<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EvaluationCategory extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'evaluation_grid_id',
        'name',
        'description',
        'display_order',
    ];

    public function grid(): BelongsTo
    {
        return $this->belongsTo(EvaluationGrid::class, 'evaluation_grid_id');
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriterion::class, 'evaluation_category_id');
    }
}
