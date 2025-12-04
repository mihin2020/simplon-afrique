<?php

namespace App\Livewire\Formateur;

use App\Models\Evaluation;
use App\Models\Jury;
use App\Services\EvaluationCalculationService;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();

        // Charger la candidature avec toutes les relations nécessaires
        $candidature = $user->candidatures()
            ->with([
                'badge',
                'currentStep',
                'steps.labellisationStep',
            ])
            ->latest()
            ->first();

        // L'utilisateur est certifié uniquement si la candidature est validée
        $isCertified = $candidature && $candidature->status === 'validated';
        $currentBadge = $isCertified ? $candidature->badge : null;
        $badgeLabel = $isCertified ? ($currentBadge?->label ?? 'Non certifié') : 'Non certifié';

        // Récupérer le jury assigné à cette candidature
        $jury = null;
        $evaluationGrid = null;
        $categories = collect();

        if ($candidature) {
            // Chercher le jury assigné à cette candidature
            $jury = Jury::whereHas('candidatures', function ($query) use ($candidature) {
                $query->where('candidatures.id', $candidature->id);
            })->orWhere('candidature_id', $candidature->id)
                ->with(['members', 'evaluationGrid.categories.criteria'])
                ->first();

            if ($jury && $jury->evaluationGrid) {
                $evaluationGrid = $jury->evaluationGrid;
                $categories = $evaluationGrid->categories()
                    ->with('criteria')
                    ->orderBy('display_order')
                    ->get();
            }
        }

        // Construire les étapes dynamiques basées sur les catégories de la grille
        $stepsWithStatus = collect();

        // Étape 1 : Candidature (toujours présente)
        $candidatureStatus = 'pending';
        if ($candidature) {
            if (in_array($candidature->status, ['submitted', 'in_review', 'validated'])) {
                $candidatureStatus = 'completed';
            } elseif ($candidature->status === 'draft') {
                $candidatureStatus = 'in_progress';
            }
        }

        $stepsWithStatus->push([
            'id' => 'candidature',
            'name' => 'Candidature',
            'label' => 'Dépôt du dossier',
            'status' => $candidatureStatus,
            'type' => 'candidature',
            'comments' => [],
        ]);

        // Étapes intermédiaires : basées sur les catégories de la grille d'évaluation
        if ($categories->isNotEmpty() && $jury) {
            $juryMembersCount = $jury->members()->count();

            foreach ($categories as $index => $category) {
                $criteriaCount = $category->criteria->count();

                // Compter combien de membres ont évalué tous les critères de cette catégorie
                $membersWhoCompletedCategory = 0;

                foreach ($jury->members as $member) {
                    // Récupérer les évaluations de ce membre pour cette candidature
                    $memberEvaluations = Evaluation::where('candidature_id', $candidature->id)
                        ->where('jury_id', $jury->id)
                        ->where('jury_member_id', $member->id)
                        ->where('status', 'submitted')
                        ->with('scores')
                        ->get();

                    // Compter les critères de cette catégorie qui ont été notés par ce membre
                    $criteriaIds = $category->criteria->pluck('id')->toArray();
                    $scoredCriteriaCount = 0;

                    foreach ($memberEvaluations as $evaluation) {
                        foreach ($evaluation->scores as $score) {
                            if (in_array($score->evaluation_criterion_id, $criteriaIds)) {
                                $scoredCriteriaCount++;
                            }
                        }
                    }

                    // Si tous les critères de cette catégorie ont été notés par ce membre
                    if ($scoredCriteriaCount >= $criteriaCount) {
                        $membersWhoCompletedCategory++;
                    }
                }

                // Déterminer le statut de cette étape/catégorie
                $categoryStatus = 'pending';

                if ($candidature->status === 'rejected') {
                    $categoryStatus = 'pending';
                } elseif ($candidature->status === 'validated') {
                    $categoryStatus = 'completed';
                } elseif ($juryMembersCount > 0 && $membersWhoCompletedCategory >= $juryMembersCount) {
                    $categoryStatus = 'completed';
                } elseif ($membersWhoCompletedCategory > 0) {
                    $categoryStatus = 'in_progress';
                } elseif ($candidatureStatus === 'completed') {
                    // La candidature est soumise, les évaluations peuvent commencer
                    $categoryStatus = 'pending';
                }

                // Récupérer les commentaires pour cette catégorie
                $comments = $this->getCategoryComments($candidature, $jury, $category);

                $stepsWithStatus->push([
                    'id' => $category->id,
                    'name' => $category->name,
                    'label' => $category->name,
                    'status' => $categoryStatus,
                    'type' => 'evaluation',
                    'comments' => $comments,
                ]);
            }
        }

        // Étape finale : Décision / Certification
        $decisionStatus = 'pending';
        $decisionComments = [];

        if ($candidature) {
            if ($candidature->status === 'validated') {
                $decisionStatus = 'completed';
            } elseif ($candidature->status === 'rejected') {
                $decisionStatus = 'rejected';
            } elseif ($candidature->status === 'in_review') {
                // Vérifier si toutes les catégories sont complètes
                $allCategoriesCompleted = $stepsWithStatus
                    ->where('type', 'evaluation')
                    ->every(fn ($step) => $step['status'] === 'completed');

                if ($allCategoriesCompleted && $stepsWithStatus->where('type', 'evaluation')->count() > 0) {
                    $decisionStatus = 'in_progress';
                }
            }

            // Récupérer le commentaire du président si disponible
            if ($jury) {
                $presidentEvaluation = Evaluation::where('candidature_id', $candidature->id)
                    ->where('jury_id', $jury->id)
                    ->whereNotNull('president_comment')
                    ->first();

                if ($presidentEvaluation && $presidentEvaluation->president_comment) {
                    $decisionComments[] = $presidentEvaluation->president_comment;
                }
            }
        }

        $stepsWithStatus->push([
            'id' => 'decision',
            'name' => 'Décision',
            'label' => 'Certification',
            'status' => $decisionStatus,
            'type' => 'decision',
            'comments' => $decisionComments,
        ]);

        // Calculer la note finale si la candidature est validée
        $finalScore = null;
        if ($candidature && $candidature->status === 'validated') {
            $calculationService = new EvaluationCalculationService;
            $finalScore = $calculationService->calculateFinalScore($candidature);
        }

        return view('livewire.formateur.dashboard', [
            'candidature' => $candidature,
            'isCertified' => $isCertified,
            'badgeLabel' => $badgeLabel,
            'currentBadge' => $currentBadge,
            'stepsWithStatus' => $stepsWithStatus,
            'finalScore' => $finalScore,
            'jury' => $jury,
        ]);
    }

    /**
     * Récupère les commentaires des membres du jury pour une catégorie donnée.
     */
    private function getCategoryComments($candidature, $jury, $category): array
    {
        $comments = [];
        $criteriaIds = $category->criteria->pluck('id')->toArray();

        $evaluations = Evaluation::where('candidature_id', $candidature->id)
            ->where('jury_id', $jury->id)
            ->where('status', 'submitted')
            ->with('scores')
            ->get();

        foreach ($evaluations as $evaluation) {
            foreach ($evaluation->scores as $score) {
                if (in_array($score->evaluation_criterion_id, $criteriaIds) && ! empty($score->comment)) {
                    $comments[] = $score->comment;
                }
            }
        }

        return array_unique($comments);
    }
}
