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
        'badge_awarded_at',
        'attestation_path',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'attachments' => 'array',
        'admin_global_score' => 'float',
        'badge_awarded_at' => 'datetime',
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

    /**
     * Get the real current step based on candidature steps progress.
     * Returns the step that is currently in progress, or the last completed step if all are done.
     */
    public function getRealCurrentStep(): ?LabellisationStep
    {
        // Si la candidature est validée, retourner l'étape "Certification" (étape finale)
        if ($this->status === 'validated') {
            return LabellisationStep::where('name', 'certification')->first();
        }

        // Si la candidature est rejetée, retourner aussi l'étape "Certification"
        if ($this->status === 'rejected') {
            return LabellisationStep::where('name', 'certification')->first();
        }

        // Si la candidature n'est pas encore en examen, retourner l'étape "Candidature"
        if ($this->status === 'draft' || $this->status === 'submitted') {
            return LabellisationStep::where('name', 'candidature')->first();
        }

        // Pour les candidatures en examen (in_review), déterminer l'étape réelle
        // Charger les étapes de la candidature avec leur définition
        $candidatureSteps = $this->steps()
            ->with('labellisationStep')
            ->get()
            ->sortBy(fn ($cs) => $cs->labellisationStep?->display_order ?? 999);

        // Chercher l'étape en cours (in_progress)
        $inProgressStep = $candidatureSteps->firstWhere('status', 'in_progress');
        if ($inProgressStep) {
            return $inProgressStep->labellisationStep;
        }

        // Si aucune étape n'est en cours, chercher la dernière étape complétée
        $completedSteps = $candidatureSteps->where('status', 'completed');
        if ($completedSteps->isNotEmpty()) {
            $lastCompleted = $completedSteps->sortByDesc(fn ($cs) => $cs->labellisationStep?->display_order ?? 0)->first();

            // Retourner l'étape suivante (celle qui devrait être en cours)
            $nextStep = LabellisationStep::where('display_order', '>', $lastCompleted->labellisationStep->display_order)
                ->orderBy('display_order')
                ->first();

            return $nextStep ?? $lastCompleted->labellisationStep;
        }

        // Fallback: utiliser current_step_id ou la première étape
        return $this->currentStep ?? LabellisationStep::orderBy('display_order')->first();
    }

    /**
     * Get the display label for the current step with progress indicator.
     */
    public function getCurrentStepLabel(): string
    {
        $step = $this->getRealCurrentStep();

        if (! $step) {
            return 'Non défini';
        }

        return $step->label;
    }

    /**
     * Get the step number (display_order) for progress display.
     */
    public function getCurrentStepNumber(): int
    {
        $step = $this->getRealCurrentStep();

        return $step?->display_order ?? 1;
    }

    /**
     * Get total number of steps.
     */
    public function getTotalSteps(): int
    {
        return LabellisationStep::count();
    }

    /**
     * Scope pour filtrer les candidatures selon le périmètre d'un référent pédagogique.
     * Si l'utilisateur n'est pas référent ou n'a pas de restrictions, aucun filtre n'est appliqué.
     */
    public function scopeForReferent($query, ?\App\Models\User $referent = null): void
    {
        // Si pas d'utilisateur, pas référent, ou pas de pays assigné → AUCUN FILTRE (admin classique)
        if (! $referent || ! $referent->isReferentPedagogique() || empty($referent->country)) {
            return;
        }

        // SEULEMENT pour les référents pédagogiques → appliquer le filtre
        $query->whereHas('user.formateurProfile', function ($q) use ($referent) {
            $q->where('country', $referent->country);

            $referentOrganizations = $referent->referentOrganizations()->pluck('organizations.id')->toArray();
            if (! empty($referentOrganizations)) {
                $q->whereIn('organization_id', $referentOrganizations);
            }
        });
    }
}