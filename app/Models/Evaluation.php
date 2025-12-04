<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluation extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'candidature_id',
        'jury_id',
        'jury_member_id',
        'evaluation_grid_id',
        'labellisation_step_id',
        'status',
        'general_comment',
        'president_comment',
        'president_decision',
        'president_validated_at',
        'final_score',
        'average_score',
        'member_total_score',
        'submitted_at',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'president_validated_at' => 'datetime',
        'final_score' => 'float',
        'average_score' => 'float',
        'member_total_score' => 'float',
    ];

    public function candidature(): BelongsTo
    {
        return $this->belongsTo(Candidature::class);
    }

    public function jury(): BelongsTo
    {
        return $this->belongsTo(Jury::class);
    }

    public function juryMember(): BelongsTo
    {
        return $this->belongsTo(JuryMember::class);
    }

    public function grid(): BelongsTo
    {
        return $this->belongsTo(EvaluationGrid::class, 'evaluation_grid_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    public function labellisationStep(): BelongsTo
    {
        return $this->belongsTo(LabellisationStep::class);
    }

    public function calculateMemberTotalScore(): float
    {
        return $this->scores()->sum('weighted_score');
    }

    public function scopeForStep($query, string $stepId)
    {
        return $query->where('labellisation_step_id', $stepId);
    }

    public function scopeForCandidature($query, string $candidatureId)
    {
        return $query->where('candidature_id', $candidatureId);
    }
}
