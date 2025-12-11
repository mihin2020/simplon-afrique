<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\Evaluation;
use App\Models\EvaluationGrid;
use App\Models\Jury;
use App\Models\JuryMember;
use App\Models\User;
use App\Services\EvaluationCalculationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class JuryDetail extends Component
{
    public string $juryId;

    public ?Jury $jury = null;

    public $name = '';

    public $selectedGridId = null;

    protected $rules = [
        'name' => ['required', 'string', 'max:255'],
    ];

    protected $messages = [
        'name.required' => 'Le nom du jury est obligatoire.',
    ];

    public function mount(string $juryId): void
    {
        $this->juryId = $juryId;
        $this->loadJury();
    }

    public function loadJury(): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }
        $user->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        $query = Jury::with([
            'candidatures.user',
            'candidatures.badge',
            'members.user.roles',
            'evaluationGrid.categories.criteria',
        ]);

        // Si l'utilisateur n'est pas super admin, vérifier qu'il est membre du jury
        if (! $isSuperAdmin) {
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        $this->jury = $query->findOrFail($this->juryId);

        $this->name = $this->jury->name;
        $this->selectedGridId = $this->jury->evaluation_grid_id;
    }

    public function applyGridSelection(): void
    {
        $this->updateEvaluationGrid($this->selectedGridId);
    }

    public function updateEvaluationGrid(?string $gridId = null): void
    {
        if (! $this->jury) {
            return;
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }
        $user->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        if (! $isSuperAdmin) {
            session()->flash('error', 'Seul le super administrateur peut associer une grille d\'évaluation à un jury.');
            $this->loadJury();

            return;
        }

        // Si gridId est vide string, convertir en null
        if ($gridId === '' || $gridId === null) {
            $gridId = null;
        }

        // Validation : vérifier que la grille existe et est active si un ID est fourni
        if ($gridId) {
            $grid = EvaluationGrid::where('id', $gridId)
                ->where('is_active', true)
                ->first();

            if (! $grid) {
                session()->flash('error', 'La grille d\'évaluation sélectionnée n\'existe pas ou n\'est pas active.');
                $this->selectedGridId = $this->jury->evaluation_grid_id; // Réinitialiser à l'ancienne valeur
                $this->loadJury();

                return;
            }
        }

        $this->jury->update([
            'evaluation_grid_id' => $gridId,
        ]);

        session()->flash('success', $gridId ? 'La grille d\'évaluation a été associée au jury avec succès.' : 'La grille d\'évaluation a été retirée du jury avec succès.');
        $this->loadJury();
    }

    public function removeMember(string $memberId): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }
        $user->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        if (! $isSuperAdmin) {
            session()->flash('error', 'Seul le super administrateur peut retirer un membre d\'un jury.');
            $this->loadJury();

            return;
        }

        $member = JuryMember::findOrFail($memberId);

        // Vérifier que le membre appartient bien à ce jury
        if ($member->jury_id !== $this->juryId) {
            session()->flash('error', 'Ce membre n\'appartient pas à ce jury.');
            $this->loadJury();

            return;
        }

        $memberName = $member->user->name;
        $member->delete();

        session()->flash('success', 'Le membre "'.$memberName.'" a été retiré du jury.');
        $this->loadJury();
    }

    public function setPresident(string $memberId): void
    {
        // Retirer le président actuel
        $this->jury->members()->update(['is_president' => false]);

        // Définir le nouveau président
        $member = JuryMember::findOrFail($memberId);
        $member->update(['is_president' => true]);

        session()->flash('success', 'Le président du jury a été défini.');
        $this->loadJury();
    }

    public function render()
    {
        if (! $this->jury) {
            return view('livewire.admin.jury-detail', [
                'jury' => null,
                'roleOptions' => [],
                'availableGrids' => collect(),
                'isSuperAdmin' => false,
                'isPresident' => false,
                'availableCandidatures' => collect(),
                'evaluationsData' => [],
                'presidentData' => [],
            ]);
        }

        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }
        $user->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        $roleOptions = [
            'referent_pedagogique' => 'Référent Pédagogique',
            'directeur_pedagogique' => 'Directeur Pédagogique',
            'formateur_senior' => 'Formateur Senior',
        ];

        // Charger les grilles actives pour la sélection (uniquement pour super admin)
        $availableGrids = $isSuperAdmin
            ? EvaluationGrid::where('is_active', true)->orderBy('name')->get()
            : collect();

        // Récupérer le membre du jury pour les évaluations
        $juryMember = $this->jury->members()->where('user_id', $user->id)->first();
        if (! $juryMember && $isSuperAdmin) {
            $juryMember = $this->jury->members()->first();
        }

        // Charger les candidatures disponibles
        $candidaturesMany = $this->jury->candidatures()
            ->whereIn('status', ['in_review'])
            ->with('user', 'currentStep')
            ->get();

        $candidatureSingle = collect();
        if ($this->jury->candidature_id) {
            $cand = \App\Models\Candidature::where('id', $this->jury->candidature_id)
                ->whereIn('status', ['in_review'])
                ->with('user', 'currentStep')
                ->first();
            if ($cand) {
                $candidatureSingle = collect([$cand]);
            }
        }

        $availableCandidatures = $candidaturesMany->merge($candidatureSingle)->unique('id')->values();

        // Charger les données d'évaluation pour chaque candidature
        $evaluationsData = [];
        $calculationService = new EvaluationCalculationService;

        if ($juryMember) {
            foreach ($availableCandidatures as $candidature) {
                // Récupérer les évaluations soumises pour cette candidature et ce membre du jury
                $evaluations = Evaluation::where('candidature_id', $candidature->id)
                    ->where('jury_id', $this->jury->id)
                    ->where('jury_member_id', $juryMember->id)
                    ->where('status', 'submitted')
                    ->with('scores')
                    ->get();

                if ($evaluations->isNotEmpty() && $evaluations->first()->scores->isNotEmpty()) {
                    // Calculer la somme totale des notes pondérées
                    $totalWeightedScore = 0;
                    $criteriaCount = 0;
                    $evaluation = $evaluations->first();

                    foreach ($evaluation->scores as $score) {
                        $totalWeightedScore += $score->weighted_score ?? 0;
                        $criteriaCount++;
                    }

                    // Calculer la moyenne normalisée en utilisant le service
                    // Cette méthode groupe par catégories et calcule correctement la moyenne
                    $averageScore = $calculationService->calculateNormalizedAverage($evaluation);

                    $evaluationsData[$candidature->id] = [
                        'evaluated' => true,
                        'total_weighted_score' => $totalWeightedScore,
                        'average_score' => $averageScore,
                        'criteria_count' => $criteriaCount,
                    ];
                } else {
                    $evaluationsData[$candidature->id] = [
                        'evaluated' => false,
                    ];
                }
            }
        }

        // Vérifier si l'utilisateur est président du jury
        $isPresident = false;
        $currentJuryMember = $this->jury->members()->where('user_id', $user->id)->first();
        if ($currentJuryMember && $currentJuryMember->is_president) {
            $isPresident = true;
        }

        // Données pour le président : récapitulatif de toutes les évaluations par tous les membres
        $presidentData = [];
        $calculationService = new EvaluationCalculationService;

        if ($isPresident || $isSuperAdmin) {
            foreach ($availableCandidatures as $candidature) {
                // Récupérer toutes les évaluations de tous les membres pour cette candidature
                $allEvaluations = Evaluation::where('candidature_id', $candidature->id)
                    ->where('jury_id', $this->jury->id)
                    ->where('status', 'submitted')
                    ->with(['scores', 'juryMember.user'])
                    ->get();

                $membersEvaluations = [];
                $allMembersEvaluated = true;
                $totalMembersWeightedScore = 0;
                $membersCount = 0;

                // Vérifier si tous les membres ont évalué
                $juryMembersCount = $this->jury->members()->count();

                foreach ($allEvaluations as $evaluation) {
                    if ($evaluation->scores->isNotEmpty()) {
                        $memberWeightedScore = 0;
                        $memberCriteriaCount = 0;

                        foreach ($evaluation->scores as $score) {
                            $memberWeightedScore += $score->weighted_score ?? 0;
                            $memberCriteriaCount++;
                        }

                        // Utiliser le service pour calculer la moyenne normalisée
                        // Cette méthode groupe par catégories et calcule correctement la moyenne sur 20
                        $memberAverage = $calculationService->calculateNormalizedAverage($evaluation);

                        // Compter les catégories distinctes
                        $categoryCount = $evaluation->scores()
                            ->join('evaluation_criteria', 'evaluation_scores.evaluation_criterion_id', '=', 'evaluation_criteria.id')
                            ->join('evaluation_categories', 'evaluation_criteria.evaluation_category_id', '=', 'evaluation_categories.id')
                            ->distinct('evaluation_categories.id')
                            ->count('evaluation_categories.id');

                        $membersEvaluations[] = [
                            'member_name' => $evaluation->juryMember->user->name ?? 'Membre inconnu',
                            'member_id' => $evaluation->jury_member_id,
                            'total_weighted_score' => $memberWeightedScore,
                            'average_score' => $memberAverage,
                            'criteria_count' => $memberCriteriaCount,
                            'category_count' => $categoryCount,
                        ];

                        $totalMembersWeightedScore += $memberWeightedScore;
                        $membersCount++;
                    }
                }

                // Vérifier si tous les membres ont évalué
                $allMembersEvaluated = $membersCount >= $juryMembersCount;

                // Calculer la moyenne globale : moyenne des moyennes normalisées de chaque membre
                // Chaque moyenne est déjà normalisée sur 20 grâce à calculateNormalizedAverage()
                $normalizedAverages = array_column($membersEvaluations, 'average_score');
                $globalAverage = count($normalizedAverages) > 0
                    ? (array_sum($normalizedAverages) / count($normalizedAverages))
                    : 0;

                $globalWeightedScore = $membersCount > 0 ? ($totalMembersWeightedScore / $membersCount) : 0;

                // Récupérer le badge demandé par le formateur
                $requestedBadge = $candidature->badge;

                // Déterminer le badge attribué selon la moyenne globale
                $awardedBadge = null;
                if ($globalAverage > 0) {
                    // Trouver le badge approprié selon la moyenne
                    $juniorBadge = Badge::where('name', 'junior')->first();
                    $minThreshold = $juniorBadge?->min_score ?? 10.0;

                    if ($globalAverage >= $minThreshold) {
                        $awardedBadge = Badge::where('min_score', '<=', $globalAverage)
                            ->where('max_score', '>=', $globalAverage)
                            ->orderBy('min_score', 'desc')
                            ->first();
                    }
                }

                $presidentData[$candidature->id] = [
                    'members_evaluations' => $membersEvaluations,
                    'all_members_evaluated' => $allMembersEvaluated,
                    'members_count' => $membersCount,
                    'total_members' => $juryMembersCount,
                    'global_weighted_score' => $globalWeightedScore,
                    'global_average' => $globalAverage,
                    'requested_badge' => $requestedBadge,
                    'awarded_badge' => $awardedBadge,
                    'president_comment' => $allEvaluations->first()?->president_comment,
                    'president_decision' => $allEvaluations->first()?->president_decision,
                ];
            }
        }

        return view('livewire.admin.jury-detail', [
            'jury' => $this->jury,
            'roleOptions' => $roleOptions,
            'availableGrids' => $availableGrids,
            'isSuperAdmin' => $isSuperAdmin,
            'isPresident' => $isPresident,
            'availableCandidatures' => $availableCandidatures,
            'evaluationsData' => $evaluationsData,
            'presidentData' => $presidentData,
        ]);
    }
}
