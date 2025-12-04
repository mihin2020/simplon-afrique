<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jury extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'candidature_id',
        'status',
        'evaluation_grid_id',
    ];

    public function candidature(): BelongsTo
    {
        return $this->belongsTo(Candidature::class);
    }

    public function candidatures(): BelongsToMany
    {
        return $this->belongsToMany(Candidature::class, 'jury_candidature');
    }

    public function members(): HasMany
    {
        return $this->hasMany(JuryMember::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function evaluationGrid(): BelongsTo
    {
        return $this->belongsTo(EvaluationGrid::class);
    }

    public function hasEvaluationGrid(): bool
    {
        return $this->evaluation_grid_id !== null;
    }

    /**
     * Get categories linked to a specific step.
     */
    public function getCategoriesForStep(string $stepId)
    {
        if (! $this->evaluationGrid) {
            return collect();
        }

        return $this->evaluationGrid->categories()
            ->where('labellisation_step_id', $stepId)
            ->orderBy('display_order')
            ->get();
    }
}
