<?php

namespace App\Services;

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\Evaluation;
use App\Models\LabellisationStep;

class EvaluationCalculationService
{
    /**
     * Calculate weighted score from raw score and weight.
     */
    public function calculateWeightedScore(float $rawScore, float $weight): float
    {
        return $rawScore * ($weight / 100);
    }

    /**
     * Calculate total score for a member on a step (sum of all weighted scores).
     */
    public function calculateMemberTotalScore(Evaluation $evaluation): float
    {
        return $evaluation->scores()->sum('weighted_score');
    }

    /**
     * Calculate average score for a step across all members.
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

        $totalScores = 0;
        $count = 0;

        foreach ($evaluations as $evaluation) {
            $memberTotal = $this->calculateMemberTotalScore($evaluation);
            $evaluation->member_total_score = $memberTotal;
            $evaluation->save();

            $totalScores += $memberTotal;
            $count++;
        }

        $average = $count > 0 ? $totalScores / $count : 0.0;

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

        return round($finalScore, 3);
    }

    /**
     * Determine which badge should be awarded based on final score.
     */
    public function determineBadge(Candidature $candidature): ?Badge
    {
        $finalScore = $this->calculateFinalScore($candidature);

        if ($finalScore < 10.0) {
            return null;
        }

        // Get the badge requested by the formateur (from candidature.badge_id if set during submission)
        $requestedBadge = $candidature->badge;

        // If no specific badge was requested, determine based on score
        if (! $requestedBadge) {
            return Badge::where('min_score', '<=', $finalScore)
                ->where('max_score', '>=', $finalScore)
                ->first();
        }

        // Check if the final score meets the requirements for the requested badge
        if ($finalScore >= $requestedBadge->min_score && $finalScore <= $requestedBadge->max_score) {
            return $requestedBadge;
        }

        // If score doesn't match requested badge, find the appropriate badge
        return Badge::where('min_score', '<=', $finalScore)
            ->where('max_score', '>=', $finalScore)
            ->first();
    }
}
