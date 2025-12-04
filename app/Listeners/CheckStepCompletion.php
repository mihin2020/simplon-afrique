<?php

namespace App\Listeners;

use App\Events\EvaluationSubmitted;
use App\Models\CandidatureStep;
use App\Models\Evaluation;
use App\Models\LabellisationStep;
use App\Services\EvaluationCalculationService;
use Illuminate\Support\Facades\DB;

class CheckStepCompletion
{
    public function handle(EvaluationSubmitted $event): void
    {
        $candidature = $event->candidature;
        $step = $event->step;
        $jury = $candidature->getJury();

        if (! $jury) {
            return;
        }

        // Récupérer tous les membres du jury
        $members = $jury->members;

        // Vérifier si tous les membres ont soumis leur évaluation pour cette étape
        $submittedEvaluations = Evaluation::where('candidature_id', $candidature->id)
            ->where('labellisation_step_id', $step->id)
            ->where('status', 'submitted')
            ->get();

        $allMembersSubmitted = $members->count() === $submittedEvaluations->count();

        if ($allMembersSubmitted) {
            DB::transaction(function () use ($candidature, $step) {
                // Calculer la moyenne de l'étape
                $calculationService = new EvaluationCalculationService;
                $stepAverage = $calculationService->calculateStepAverage($candidature, $step);

                // Marquer l'étape comme terminée
                $candidatureStep = CandidatureStep::where('candidature_id', $candidature->id)
                    ->where('labellisation_step_id', $step->id)
                    ->first();

                if ($candidatureStep) {
                    $candidatureStep->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);
                }

                // Passer à l'étape suivante
                $nextStep = LabellisationStep::where('display_order', '>', $step->display_order)
                    ->orderBy('display_order')
                    ->first();

                if ($nextStep) {
                    // Mettre à jour la candidature avec la nouvelle étape courante
                    $candidature->update([
                        'current_step_id' => $nextStep->id,
                    ]);

                    // Créer un nouveau CandidatureStep pour l'étape suivante
                    CandidatureStep::firstOrCreate(
                        [
                            'candidature_id' => $candidature->id,
                            'labellisation_step_id' => $nextStep->id,
                        ],
                        [
                            'status' => 'in_progress',
                        ]
                    );
                } else {
                    // C'était la dernière étape, marquer la candidature comme prête pour validation président
                    // Le statut reste 'in_review' jusqu'à la validation du président
                }
            });
        }
    }
}
