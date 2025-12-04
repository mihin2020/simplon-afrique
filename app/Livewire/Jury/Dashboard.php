<?php

namespace App\Livewire\Jury;

use App\Models\Evaluation;
use App\Models\JuryMember;
use App\Models\LabellisationStep;
use Livewire\Component;

class Dashboard extends Component
{
    public function getCandidaturesToEvaluateProperty()
    {
        $user = auth()->user();

        // Récupérer tous les jurys dont l'utilisateur est membre
        $juryMembers = JuryMember::where('user_id', $user->id)
            ->with(['jury.candidatures.currentStep', 'jury.candidatures.user'])
            ->get();

        $candidatures = collect();

        foreach ($juryMembers as $juryMember) {
            $jury = $juryMember->jury;

            if (! $jury) {
                continue;
            }

            // Récupérer toutes les candidatures assignées à ce jury
            $assignedCandidatures = $jury->candidatures()
                ->whereIn('status', ['in_review'])
                ->with(['currentStep', 'user', 'steps.labellisationStep'])
                ->get();

            foreach ($assignedCandidatures as $candidature) {
                // Récupérer toutes les étapes de labellisation
                $allSteps = LabellisationStep::orderBy('display_order')->get();
                $candidatureSteps = $candidature->steps()->with('labellisationStep')->get();
                $currentStepId = $candidature->current_step_id;

                // Pour chaque étape, vérifier le statut et si l'utilisateur a déjà évalué
                $stepsWithStatus = $allSteps->map(function ($step) use ($candidature, $juryMember, $candidatureSteps, $currentStepId) {
                    $candidatureStep = $candidatureSteps->firstWhere('labellisation_step_id', $step->id);

                    // Déterminer le statut de l'étape
                    $status = 'pending';
                    if ($step->id === $currentStepId) {
                        $status = 'in_progress';
                    } elseif ($candidatureStep && $candidatureStep->status === 'completed') {
                        $status = 'completed';
                    } elseif ($currentStepId && $step->display_order < ($candidature->currentStep?->display_order ?? 0)) {
                        $status = 'completed';
                    }

                    // Vérifier si l'utilisateur a déjà évalué cette étape
                    $evaluation = Evaluation::where('candidature_id', $candidature->id)
                        ->where('labellisation_step_id', $step->id)
                        ->where('jury_member_id', $juryMember->id)
                        ->first();

                    $hasEvaluated = $evaluation && $evaluation->status === 'submitted';
                    $canEvaluate = $status === 'in_progress' || ($status === 'completed' && ! $hasEvaluated);

                    return [
                        'step' => $step,
                        'status' => $status,
                        'hasEvaluated' => $hasEvaluated,
                        'canEvaluate' => $canEvaluate,
                        'evaluation' => $evaluation,
                    ];
                });

                $candidatures->push([
                    'candidature' => $candidature,
                    'jury' => $jury,
                    'juryMember' => $juryMember,
                    'stepsWithStatus' => $stepsWithStatus,
                ]);
            }
        }

        return $candidatures->sortBy(function ($item) {
            return $item['candidature']->updated_at;
        });
    }

    public function getCandidaturesReadyForValidationProperty()
    {
        $user = auth()->user();

        // Récupérer tous les jurys dont l'utilisateur est président
        $juryMembers = JuryMember::where('user_id', $user->id)
            ->where('is_president', true)
            ->with(['jury.candidatures.currentStep', 'jury.candidatures.user', 'jury.candidatures.steps.labellisationStep'])
            ->get();

        $candidatures = collect();

        foreach ($juryMembers as $juryMember) {
            $jury = $juryMember->jury;

            if (! $jury) {
                continue;
            }

            // Récupérer toutes les candidatures en cours d'examen
            $candidaturesInReview = $jury->candidatures()
                ->where('status', 'in_review')
                ->with(['currentStep', 'user', 'steps.labellisationStep'])
                ->get();

            foreach ($candidaturesInReview as $candidature) {
                // Vérifier si toutes les étapes sont terminées
                $allStepsCompleted = $candidature->steps()
                    ->where('status', 'completed')
                    ->count() === $candidature->steps()->count();

                // Vérifier si le président n'a pas encore validé
                $hasValidated = Evaluation::where('candidature_id', $candidature->id)
                    ->whereHas('juryMember', function ($query) {
                        $query->where('is_president', true);
                    })
                    ->whereNotNull('president_decision')
                    ->exists();

                if ($allStepsCompleted && ! $hasValidated && $candidature->steps()->count() > 0) {
                    $candidatures->push([
                        'candidature' => $candidature,
                        'jury' => $jury,
                        'juryMember' => $juryMember,
                    ]);
                }
            }
        }

        return $candidatures->sortBy(function ($item) {
            return $item['candidature']->updated_at;
        });
    }

    public function getCompletedCandidaturesProperty()
    {
        $user = auth()->user();

        // Récupérer tous les jurys dont l'utilisateur est membre
        $juryMembers = JuryMember::where('user_id', $user->id)
            ->with(['jury.candidatures.currentStep', 'jury.candidatures.user'])
            ->get();

        $candidatures = collect();

        foreach ($juryMembers as $juryMember) {
            $jury = $juryMember->jury;

            if (! $jury) {
                continue;
            }

            // Récupérer toutes les candidatures validées ou rejetées
            $completedCandidatures = $jury->candidatures()
                ->whereIn('status', ['validated', 'rejected'])
                ->with(['currentStep', 'user', 'badge', 'steps.labellisationStep'])
                ->get();

            foreach ($completedCandidatures as $candidature) {
                $candidatures->push([
                    'candidature' => $candidature,
                    'jury' => $jury,
                    'juryMember' => $juryMember,
                ]);
            }
        }

        return $candidatures->sortByDesc(function ($item) {
            return $item['candidature']->updated_at;
        });
    }

    public function render()
    {
        return view('livewire.jury.dashboard', [
            'candidaturesToEvaluate' => $this->candidaturesToEvaluate,
            'candidaturesReadyForValidation' => $this->candidaturesReadyForValidation,
            'completedCandidatures' => $this->completedCandidatures,
        ]);
    }
}
