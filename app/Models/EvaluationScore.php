<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationScore extends Model
{
    use HasUuids;

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'evaluation_id',
        'evaluation_criterion_id',
        'raw_score',
        'weighted_score',
        'comment',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'raw_score' => 'float',
        'weighted_score' => 'float',
    ];

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriterion::class, 'evaluation_criterion_id');
    }

    /**
     * Calcule la note pondérée à partir de la note brute.
     * La note brute est d'abord normalisée sur 20, puis multipliée par le poids.
     */
    protected static function booted(): void
    {
        static::saving(function (EvaluationScore $score) {
            if ($score->isDirty('raw_score') && $score->criterion) {
                $weight = $score->criterion->weight ?? 0;

                // Récupérer l'échelle de notation configurée
                $noteScale = LabellisationSetting::getNoteScale();

                // Normaliser la note brute sur 20
                $normalizedScore = ($score->raw_score / $noteScale) * 20;

                // Calculer la note pondérée
                $score->weighted_score = $normalizedScore * ($weight / 100);
            }
        });
    }
}
