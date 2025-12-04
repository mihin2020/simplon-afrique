<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidature extends Model
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
        'user_id',
        'current_step_id',
        'badge_id',
        'status',
        'admin_global_score',
        'cv_path',
        'motivation_letter_path',
        'portfolio_url',
        'attachments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachments' => 'array',
        'admin_global_score' => 'float',
    ];

    /**
     * Get the formateur (user) who owns this candidature.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the current labellisation step of this candidature.
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(LabellisationStep::class, 'current_step_id');
    }

    /**
     * Get the badge eventually attributed to this candidature.
     */
    public function badge(): BelongsTo
    {
        return $this->belongsTo(Badge::class);
    }

    /**
     * Get all step records for this candidature.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(CandidatureStep::class);
    }

    /**
     * Get the jury assigned to this candidature (legacy, one-to-one).
     */
    public function jury(): BelongsTo
    {
        return $this->belongsTo(Jury::class);
    }

    /**
     * Get all juries assigned to this candidature (many-to-many).
     */
    public function juries(): BelongsToMany
    {
        return $this->belongsToMany(Jury::class, 'jury_candidature');
    }

    /**
     * Get all evaluations for this candidature.
     */
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    /**
     * Get the jury assigned to this candidature.
     */
    public function getJury(): ?Jury
    {
        return $this->juries()->first() ?? $this->jury;
    }

    /**
     * Get the evaluation grid via the jury.
     */
    public function getEvaluationGrid(): ?EvaluationGrid
    {
        $jury = $this->getJury();

        return $jury?->evaluationGrid;
    }

    /**
     * Get categories for the current step.
     */
    public function getCurrentStepCategories()
    {
        if (! $this->current_step_id) {
            return collect();
        }

        $grid = $this->getEvaluationGrid();

        if (! $grid) {
            return collect();
        }

        return $grid->categories()
            ->where('labellisation_step_id', $this->current_step_id)
            ->orderBy('display_order')
            ->get();
    }

    /**
     * Get evaluations for a specific step.
     */
    public function getEvaluationsForStep(string $stepId)
    {
        return $this->evaluations()
            ->where('labellisation_step_id', $stepId)
            ->get();
    }
}
