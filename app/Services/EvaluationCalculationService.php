<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\Evaluation;
use App\Models\LabellisationSetting;
use App\Models\LabellisationStep;
use Illuminate\Support\Collection;

class EvaluationCalculationService
{
    /**
     * Get the configured note scale.
     */
    public function getNoteScale(): int
    {
        return LabellisationSetting::getNoteScale();
    }

    /**
     * Normalize a raw score to a 20-point scale.
     */
    public function normalizeScore(float $rawScore): float
    {
        $noteScale = $this->getNoteScale();

        if ($noteScale === 20) {
            return $rawScore;
        }

        // Normaliser sur 20
        return ($rawScore / $noteScale) * 20;
    }

    /**
     * Calculate weighted score from raw score and weight.
     * The raw score is first normalized to 20, then weighted.
     */
    public function calculateWeightedScore(float $rawScore, float $weight): float
    {
        $normalizedScore = $this->normalizeScore($rawScore);

        return $normalizedScore * ($weight / 100);
    }

    /**
     * Calculate total score for a member on a step (sum of all weighted scores).
     * This returns the raw sum of weighted scores.
     */
    public function calculateMemberTotalScore(Evaluation $evaluation): float
    {
        return $evaluation->scores()->sum('weighted_score');
    }

    /**
     * Calculate the score for a single category.
     * Since the sum of weights in a category = 100%, the result is directly on 20.
     *
     * @param  Collection  $scores  The scores for criteria in this category
     * @return float The category score (on 20)
     */
    public function calculateCategoryScore(Collection $scores): float
    {
        if ($scores->isEmpty()) {
            return 0.0;
        }

        // La somme des notes pondérées donne directement la note de la catégorie sur 20
        // car les poids totalisent 100%
        return $scores->sum('weighted_score');
    }

    /**
     * Calculate the normalized average score (on 20) for a member's evaluation.
     * Groups scores by category and calculates the average of all category scores.
     *
     * Algorithm:
     * 1. For each category: sum weighted scores (= category score on 20)
     * 2. Final average = sum of category scores / number of categories
     */
    public function calculateNormalizedAverage(Evaluation $evaluation): float
    {
        // Charger les scores avec leurs critères et catégories
        $scores = $evaluation->scores()
            ->with(['criterion.category'])
            ->get();

        if ($scores->isEmpty()) {
            return 0.0;
        }

        // Regrouper les scores par catégorie
        $scoresByCategory = $scores->groupBy(function ($score) {
            return $score->criterion?->category?->id;
        })->filter(function ($group, $key) {
            return $key !== null; // Exclure les scores sans catégorie
        });

        if ($scoresByCategory->isEmpty()) {
            return 0.0;
        }

        // Calculer la note de chaque catégorie
        $categoryScores = $scoresByCategory->map(function ($categoryScores) {
            return $this->calculateCategoryScore($categoryScores);
        });

        // La moyenne finale = somme des notes de catégories / nombre de catégories
        $totalCategoryScores = $categoryScores->sum();
        $categoryCount = $categoryScores->count();

        return $categoryCount > 0 ? ($totalCategoryScores / $categoryCount) : 0.0;
    }

    /**
     * Get detailed scores by category for display purposes.
     *
     * @return array Array of category scores with details
     */
    public function getScoresByCategory(Evaluation $evaluation): array
    {
        $scores = $evaluation->scores()
            ->with(['criterion.category'])
            ->get();

        if ($scores->isEmpty()) {
            return [];
        }

        $scoresByCategory = $scores->groupBy(function ($score) {
            return $score->criterion?->category?->id;
        })->filter(function ($group, $key) {
            return $key !== null;
        });

        $result = [];
        foreach ($scoresByCategory as $categoryId => $categoryScores) {
            $category = $categoryScores->first()->criterion->category;
            $categoryScore = $this->calculateCategoryScore($categoryScores);
            $totalWeight = $categoryScores->sum(fn ($s) => $s->criterion->weight ?? 0);

            $result[] = [
                'category_id' => $categoryId,
                'category_name' => $category->name ?? 'Sans catégorie',
                'score' => round($categoryScore, 2),
                'max_score' => 20,
                'total_weight' => $totalWeight,
                'criteria_count' => $categoryScores->count(),
            ];
        }

        return $result;
    }

    /**
     * Calculate average score for a step across all members.
     * Returns the average of all members' normalized scores (on 20).
     */
    public function calculateStepAverage(Candidature $candidature, LabellisationStep $step): float
    {
        $evaluations = Evaluation::where('candidature_id', $candidature->id)
            ->where('labellisation_step_id', $step->id)
            ->where('status', 'submitted')
            ->get();

        if ($evaluations->isEmpty()) {
            return 0.0;
        }

        $totalNormalizedScores = 0;
        $count = 0;

        foreach ($evaluations as $evaluation) {
            // Calculer le score total pondéré (somme brute)
            $memberTotal = $this->calculateMemberTotalScore($evaluation);
            $evaluation->member_total_score = $memberTotal;

            // Calculer la moyenne normalisée sur 20
            $normalizedAverage = $this->calculateNormalizedAverage($evaluation);
            $totalNormalizedScores += $normalizedAverage;
            $count++;
        }

        $average = $count > 0 ? $totalNormalizedScores / $count : 0.0;

        // Update average_score for all evaluations of this step
        foreach ($evaluations as $evaluation) {
            $evaluation->average_score = $average;
            $evaluation->save();
        }

        return $average;
    }

    /**
     * Calculate final score for a candidature (average of all step averages).
     */
    public function calculateFinalScore(Candidature $candidature): float
    {
        $steps = $candidature->steps()
            ->where('status', 'completed')
            ->with('labellisationStep')
            ->get();

        if ($steps->isEmpty()) {
            return 0.0;
        }

        $totalAverage = 0;
        $count = 0;

        foreach ($steps as $candidatureStep) {
            $step = $candidatureStep->labellisationStep;

            if (! $step) {
                continue;
            }

            // Get or calculate average for this step
            $evaluation = Evaluation::where('candidature_id', $candidature->id)
                ->where('labellisation_step_id', $step->id)
                ->where('status', 'submitted')
                ->first();

            if ($evaluation && $evaluation->average_score !== null) {
                $totalAverage += $evaluation->average_score;
            } else {
                $stepAverage = $this->calculateStepAverage($candidature, $step);
                $totalAverage += $stepAverage;
            }

            $count++;
        }

        if ($count === 0) {
            return 0.0;
        }

        $finalScore = $totalAverage / $count;

        // Optionally integrate admin global score if it exists
        if ($candidature->admin_global_score !== null) {
            // 80% jury score + 20% admin score
            $finalScore = ($finalScore * 0.8) + ($candidature->admin_global_score * 0.2);
        }

        return round($finalScore, 2);
    }

    /**
     * Determine which badge should be awarded based on final score.
     * The badge is automatically determined based on the normalized score (on 20).
     */
    public function determineBadge(Candidature $candidature): ?Badge
    {
        $finalScore = $this->calculateFinalScore($candidature);

        // Get the minimum threshold from badges (Junior badge min_score)
        $juniorBadge = Badge::where('name', 'junior')->first();
        $minThreshold = $juniorBadge?->min_score ?? 10.0;

        if ($finalScore < $minThreshold) {
            return null;
        }

        // Find the appropriate badge based on the final score
        return Badge::where('min_score', '<=', $finalScore)
            ->where('max_score', '>=', $finalScore)
            ->orderBy('min_score', 'desc')
            ->first();
    }
}
