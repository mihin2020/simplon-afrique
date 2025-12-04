<?php

namespace App\Livewire\Formateur;

use App\Models\Evaluation;
use App\Models\Jury;
use Livewire\Component;
use Livewire\WithPagination;

class MyCandidatures extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        $user = auth()->user();
        $candidatures = $user->candidatures()
            ->with(['badge'])
            ->latest()
            ->paginate(10);

        // Pour chaque candidature, calculer les étapes dynamiques
        $candidaturesWithSteps = $candidatures->getCollection()->map(function ($candidature) {
            $stepsData = $this->calculateDynamicSteps($candidature);

            return [
                'candidature' => $candidature,
                'steps' => $stepsData['steps'],
                'currentStepLabel' => $stepsData['currentStepLabel'],
            ];
        });

        return view('livewire.formateur.my-candidatures', [
            'candidatures' => $candidatures,
            'candidaturesWithSteps' => $candidaturesWithSteps,
        ]);
    }

    /**
     * Calcule les étapes dynamiques basées sur la grille d'évaluation.
     */
    private function calculateDynamicSteps($candidature): array
    {
        $steps = collect();
        $currentStepLabel = null;

        // Étape 1 : Candidature
        $candidatureStatus = 'pending';
        if (in_array($candidature->status, ['submitted', 'in_review', 'validated', 'rejected'])) {
            $candidatureStatus = 'completed';
        } elseif ($candidature->status === 'draft') {
            $candidatureStatus = 'in_progress';
            $currentStepLabel = 'Dépôt du dossier';
        }

        $steps->push([
            'label' => 'Candidature',
            'status' => $candidatureStatus,
        ]);

        // Récupérer le jury assigné
        $jury = Jury::whereHas('candidatures', function ($query) use ($candidature) {
            $query->where('candidatures.id', $candidature->id);
        })->orWhere('candidature_id', $candidature->id)
            ->with(['members', 'evaluationGrid.categories.criteria'])
            ->first();

        $hasEvaluationSteps = false;

        if ($jury && $jury->evaluationGrid) {
            $categories = $jury->evaluationGrid->categories()
                ->with('criteria')
                ->orderBy('display_order')
                ->get();

            $juryMembersCount = $jury->members()->count();
            $firstPendingFound = false;

            foreach ($categories as $category) {
                $hasEvaluationSteps = true;
                $criteriaCount = $category->criteria->count();
                $membersWhoCompletedCategory = 0;

                foreach ($jury->members as $member) {
                    $memberEvaluations = Evaluation::where('candidature_id', $candidature->id)
                        ->where('jury_id', $jury->id)
                        ->where('jury_member_id', $member->id)
                        ->where('status', 'submitted')
                        ->with('scores')
                        ->get();

                    $criteriaIds = $category->criteria->pluck('id')->toArray();
                    $scoredCriteriaCount = 0;

                    foreach ($memberEvaluations as $evaluation) {
                        foreach ($evaluation->scores as $score) {
                            if (in_array($score->evaluation_criterion_id, $criteriaIds)) {
                                $scoredCriteriaCount++;
                            }
                        }
                    }

                    if ($scoredCriteriaCount >= $criteriaCount) {
                        $membersWhoCompletedCategory++;
                    }
                }

                // Déterminer le statut
                $categoryStatus = 'pending';

                if ($candidature->status === 'validated') {
                    $categoryStatus = 'completed';
                } elseif ($candidature->status === 'rejected') {
                    $categoryStatus = 'pending';
                } elseif ($juryMembersCount > 0 && $membersWhoCompletedCategory >= $juryMembersCount) {
                    $categoryStatus = 'completed';
                } elseif ($membersWhoCompletedCategory > 0) {
                    $categoryStatus = 'in_progress';
                    if (! $currentStepLabel) {
                        $currentStepLabel = $category->name;
                    }
                } elseif ($candidatureStatus === 'completed' && ! $firstPendingFound) {
                    // La première étape en attente après la candidature devient "en cours"
                    $categoryStatus = 'in_progress';
                    $currentStepLabel = $category->name;
                    $firstPendingFound = true;
                }

                $steps->push([
                    'label' => $category->name,
                    'status' => $categoryStatus,
                ]);
            }
        }

        // Étape finale : Décision
        $decisionStatus = 'pending';
        if ($candidature->status === 'validated') {
            $decisionStatus = 'completed';
            $currentStepLabel = 'Certification obtenue';
        } elseif ($candidature->status === 'rejected') {
            $decisionStatus = 'rejected';
            $currentStepLabel = 'Candidature rejetée';
        } elseif ($candidature->status === 'in_review') {
            $allEvaluationsCompleted = $steps->where('status', '!=', 'completed')
                ->filter(fn ($s) => $s['label'] !== 'Candidature')
                ->isEmpty();

            if ($allEvaluationsCompleted && $hasEvaluationSteps) {
                $decisionStatus = 'in_progress';
                $currentStepLabel = 'Décision finale';
            }
        }

        $steps->push([
            'label' => 'Certification',
            'status' => $decisionStatus,
        ]);

        // Si pas de jury assigné et candidature soumise, indiquer l'attente d'assignation
        if (! $jury && $candidature->status === 'submitted') {
            $currentStepLabel = 'En attente d\'un jury';
        } elseif (! $jury && $candidature->status === 'in_review') {
            $currentStepLabel = 'En cours d\'examen';
        }

        // Si aucune étape courante n'est définie
        if (! $currentStepLabel) {
            if ($candidature->status === 'draft') {
                $currentStepLabel = 'Dépôt du dossier';
            } elseif ($candidature->status === 'submitted') {
                $currentStepLabel = 'Candidature soumise';
            } elseif ($candidature->status === 'in_review') {
                $currentStepLabel = 'Évaluation en cours';
            }
        }

        return [
            'steps' => $steps,
            'currentStepLabel' => $currentStepLabel,
        ];
    }
}
