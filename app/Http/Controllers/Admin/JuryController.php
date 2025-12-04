<?php

namespace App\Http\Controllers\Admin;

use App\Events\EvaluationSubmitted;
use App\Http\Controllers\Controller;
use App\Models\Candidature;
use App\Models\Evaluation;
use App\Models\EvaluationGrid;
use App\Models\EvaluationScore;
use App\Models\Jury;
use App\Models\JuryMember;
use App\Services\EvaluationCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JuryController extends Controller
{
    /**
     * Affiche la page de détail d'un jury avec les données d'évaluation.
     */
    public function showDetail(Jury $jury): View
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Vérifier que l'utilisateur a accès
        if (! $isSuperAdmin) {
            $juryMember = $jury->members()->where('user_id', $user->id)->first();
            if (! $juryMember) {
                abort(403, 'Vous n\'avez pas accès à ce jury.');
            }
        }

        // Charger le jury avec toutes les relations nécessaires
        $jury->load([
            'candidatures.user',
            'candidatures.badge',
            'candidatures.currentStep',
            'members.user.roles',
            'evaluationGrid.categories.labellisationStep',
            'evaluationGrid.categories.criteria',
        ]);

        // Récupérer le membre du jury pour les évaluations
        $juryMember = $jury->members()->where('user_id', $user->id)->first();
        if (! $juryMember && $isSuperAdmin) {
            $juryMember = $jury->members()->first();
        }

        // Récupérer les candidatures disponibles
        $candidaturesMany = $jury->candidatures()
            ->whereIn('status', ['in_review'])
            ->with('user', 'currentStep')
            ->get();

        $candidatureSingle = collect();
        if ($jury->candidature_id) {
            $cand = Candidature::where('id', $jury->candidature_id)
                ->whereIn('status', ['in_review'])
                ->with('user', 'currentStep')
                ->first();
            if ($cand) {
                $candidatureSingle = collect([$cand]);
            }
        }

        $availableCandidatures = $candidaturesMany->merge($candidatureSingle)->unique('id')->values();

        // Récupérer les évaluations pour chaque candidature
        $evaluationsData = [];
        if ($juryMember) {
            foreach ($availableCandidatures as $candidature) {
                // Récupérer les évaluations soumises pour cette candidature et ce membre du jury
                $evaluations = Evaluation::where('candidature_id', $candidature->id)
                    ->where('jury_id', $jury->id)
                    ->where('jury_member_id', $juryMember->id)
                    ->where('status', 'submitted')
                    ->with('scores')
                    ->get();

                if ($evaluations->isNotEmpty() && $evaluations->first()->scores->isNotEmpty()) {
                    // Calculer la somme totale des notes pondérées
                    $totalWeightedScore = 0;
                    $totalRawScores = 0;
                    $criteriaCount = 0;

                    foreach ($evaluations as $evaluation) {
                        foreach ($evaluation->scores as $score) {
                            $totalWeightedScore += $score->weighted_score ?? 0;
                            $totalRawScores += $score->raw_score ?? 0;
                            $criteriaCount++;
                        }
                    }

                    // Calculer la moyenne des notes brutes
                    $averageScore = $criteriaCount > 0 ? ($totalRawScores / $criteriaCount) : 0;

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
        $currentJuryMember = $jury->members()->where('user_id', $user->id)->first();
        if ($currentJuryMember && $currentJuryMember->is_president) {
            $isPresident = true;
        }

        // Données pour le président : récapitulatif de toutes les évaluations par tous les membres
        $presidentData = [];
        if ($isPresident || $isSuperAdmin) {
            foreach ($availableCandidatures as $candidature) {
                // Récupérer toutes les évaluations de tous les membres pour cette candidature
                $allEvaluations = Evaluation::where('candidature_id', $candidature->id)
                    ->where('jury_id', $jury->id)
                    ->where('status', 'submitted')
                    ->with(['scores', 'juryMember.user'])
                    ->get();

                $membersEvaluations = [];
                $allMembersEvaluated = true;
                $totalMembersWeightedScore = 0;
                $totalMembersRawScores = 0;
                $totalMembersCriteriaCount = 0;
                $membersCount = 0;

                // Vérifier si tous les membres ont évalué
                $juryMembersCount = $jury->members()->count();

                foreach ($allEvaluations as $evaluation) {
                    if ($evaluation->scores->isNotEmpty()) {
                        $memberWeightedScore = 0;
                        $memberRawScores = 0;
                        $memberCriteriaCount = 0;

                        foreach ($evaluation->scores as $score) {
                            $memberWeightedScore += $score->weighted_score ?? 0;
                            $memberRawScores += $score->raw_score ?? 0;
                            $memberCriteriaCount++;
                        }

                        $memberAverage = $memberCriteriaCount > 0 ? ($memberRawScores / $memberCriteriaCount) : 0;

                        $membersEvaluations[] = [
                            'member_name' => $evaluation->juryMember->user->name ?? 'Membre inconnu',
                            'member_id' => $evaluation->jury_member_id,
                            'total_weighted_score' => $memberWeightedScore,
                            'average_score' => $memberAverage,
                            'criteria_count' => $memberCriteriaCount,
                        ];

                        $totalMembersWeightedScore += $memberWeightedScore;
                        $totalMembersRawScores += $memberRawScores;
                        $totalMembersCriteriaCount += $memberCriteriaCount;
                        $membersCount++;
                    }
                }

                // Vérifier si tous les membres ont évalué
                $allMembersEvaluated = $membersCount >= $juryMembersCount;

                // Calculer la moyenne globale de tous les membres
                $globalAverage = $membersCount > 0 ? ($totalMembersRawScores / $totalMembersCriteriaCount) : 0;
                $globalWeightedScore = $membersCount > 0 ? ($totalMembersWeightedScore / $membersCount) : 0;

                // Récupérer le badge demandé par le formateur
                $requestedBadge = $candidature->badge;

                $presidentData[$candidature->id] = [
                    'members_evaluations' => $membersEvaluations,
                    'all_members_evaluated' => $allMembersEvaluated,
                    'members_count' => $membersCount,
                    'total_members' => $juryMembersCount,
                    'global_weighted_score' => $globalWeightedScore,
                    'global_average' => $globalAverage,
                    'requested_badge' => $requestedBadge,
                    'president_comment' => $allEvaluations->first()?->president_comment,
                    'president_decision' => $allEvaluations->first()?->president_decision,
                ];
            }
        }

        return view('admin.jury-detail', [
            'juryId' => $jury->id,
            'jury' => $jury,
            'isSuperAdmin' => $isSuperAdmin,
            'isPresident' => $isPresident,
            'availableCandidatures' => $availableCandidatures,
            'evaluationsData' => $evaluationsData,
            'presidentData' => $presidentData,
        ]);
    }

    /**
     * Retire un membre d'un jury.
     */
    public function removeMember(Request $request, Jury $jury, JuryMember $member): RedirectResponse
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        if (! $isSuperAdmin) {
            return redirect()
                ->route('admin.jury.detail', $jury)
                ->with('error', 'Seul le super administrateur peut retirer un membre d\'un jury.');
        }

        // Vérifier que le membre appartient bien à ce jury
        if ($member->jury_id !== $jury->id) {
            return redirect()
                ->route('admin.jury.detail', $jury)
                ->with('error', 'Ce membre n\'appartient pas à ce jury.');
        }

        $memberName = $member->user->name;
        $member->delete();

        return redirect()
            ->route('admin.jury.detail', $jury)
            ->with('success', 'Le membre "'.$memberName.'" a été retiré du jury.');
    }

    /**
     * Associe ou retire une grille d'évaluation à un jury.
     */
    public function updateEvaluationGrid(Request $request, Jury $jury): RedirectResponse
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        if (! $isSuperAdmin) {
            return redirect()
                ->route('admin.jury.detail', $jury)
                ->with('error', 'Seul le super administrateur peut associer une grille d\'évaluation à un jury.');
        }

        $validated = $request->validate([
            'evaluation_grid_id' => ['nullable', 'string', 'exists:evaluation_grids,id'],
        ]);

        $gridId = $validated['evaluation_grid_id'] ?? null;

        // Si gridId est fourni, vérifier que la grille existe et est active
        if ($gridId) {
            $grid = EvaluationGrid::where('id', $gridId)
                ->where('is_active', true)
                ->first();

            if (! $grid) {
                return redirect()
                    ->route('admin.jury.detail', $jury)
                    ->with('error', 'La grille d\'évaluation sélectionnée n\'existe pas ou n\'est pas active.');
            }
        }

        $jury->update([
            'evaluation_grid_id' => $gridId,
        ]);

        $message = $gridId
            ? 'La grille d\'évaluation a été associée au jury avec succès.'
            : 'La grille d\'évaluation a été retirée du jury avec succès.';

        return redirect()
            ->route('admin.jury.detail', $jury)
            ->with('success', $message);
    }

    /**
     * Affiche le formulaire d'évaluation pour un jury et une candidature.
     */
    public function showEvaluationForm(Jury $jury, Candidature $candidature): View
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Vérifier que l'utilisateur a accès
        if (! $isSuperAdmin) {
            $juryMember = $jury->members()->where('user_id', $user->id)->first();
            if (! $juryMember) {
                abort(403, 'Vous n\'avez pas accès à cette évaluation.');
            }
        }

        if (! $jury->evaluationGrid) {
            abort(404, 'Aucune grille d\'évaluation associée à ce jury.');
        }

        // Charger toutes les catégories avec leurs critères
        $categories = $jury->evaluationGrid->categories()
            ->with(['criteria' => function ($query) {
                $query->orderBy('display_order');
            }, 'labellisationStep'])
            ->orderBy('display_order')
            ->get();

        // Charger les évaluations existantes
        $juryMember = $jury->members()->where('user_id', $user->id)->first();
        if (! $juryMember) {
            $juryMember = $jury->members()->first();
        }

        $evaluations = collect();
        $scores = [];
        $weightedScores = [];
        $comments = [];

        if ($juryMember) {
            $evaluations = Evaluation::where('candidature_id', $candidature->id)
                ->where('jury_member_id', $juryMember->id)
                ->with('scores')
                ->get();

            foreach ($evaluations as $evaluation) {
                foreach ($evaluation->scores as $score) {
                    $scores[$score->evaluation_criterion_id] = $score->raw_score;
                    $weightedScores[$score->evaluation_criterion_id] = $score->weighted_score;
                    $comments[$score->evaluation_criterion_id] = $score->comment ?? '';
                }
            }
        }

        // Charger les candidatures disponibles
        $candidaturesMany = $jury->candidatures()
            ->whereIn('status', ['in_review'])
            ->with('user', 'currentStep')
            ->get();

        $candidatureSingle = collect();
        if ($jury->candidature_id) {
            $cand = Candidature::where('id', $jury->candidature_id)
                ->whereIn('status', ['in_review'])
                ->with('user', 'currentStep')
                ->first();
            if ($cand) {
                $candidatureSingle = collect([$cand]);
            }
        }

        $allCandidatures = $candidaturesMany->merge($candidatureSingle)->unique('id')->values();

        // Récupérer les IDs des candidatures déjà évaluées par ce membre du jury
        // Une candidature est considérée comme évaluée si elle a au moins une évaluation soumise
        $evaluatedCandidatureIds = [];
        if ($juryMember) {
            $evaluatedCandidatureIds = Evaluation::where('jury_member_id', $juryMember->id)
                ->where('status', 'submitted')
                ->whereNotNull('submitted_at')
                ->pluck('candidature_id')
                ->unique()
                ->toArray();
        }

        // Garder toutes les candidatures (on affichera un badge pour celles évaluées)
        $candidatures = $allCandidatures;

        // Calculer le maximum possible de la somme des notes pondérées
        // Si on met 20 partout, la somme = (somme des poids / 100) × 20
        $totalWeight = $categories->flatMap(fn ($category) => $category->criteria)->sum('weight');
        $maxWeightedScore = ($totalWeight / 100) * 20;

        return view('admin.jury-evaluation', [
            'jury' => $jury,
            'candidature' => $candidature,
            'categories' => $categories,
            'candidatures' => $candidatures,
            'scores' => $scores,
            'weightedScores' => $weightedScores,
            'comments' => $comments,
            'maxWeightedScore' => $maxWeightedScore,
            'evaluatedCandidatureIds' => $evaluatedCandidatureIds,
        ]);
    }

    /**
     * Sauvegarde l'évaluation.
     */
    public function saveEvaluation(Request $request, Jury $jury, Candidature $candidature): RedirectResponse
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Vérifier que l'utilisateur a accès
        if (! $isSuperAdmin) {
            $juryMember = $jury->members()->where('user_id', $user->id)->first();
            if (! $juryMember) {
                abort(403, 'Vous n\'avez pas accès à cette évaluation.');
            }
        } else {
            $juryMember = $jury->members()->where('user_id', $user->id)->first();
            if (! $juryMember) {
                $juryMember = $jury->members()->first();
            }
        }

        if (! $juryMember) {
            return redirect()
                ->route('admin.jury.detail', $jury)
                ->with('error', 'Aucun membre trouvé dans ce jury.');
        }

        if (! $jury->evaluationGrid) {
            return redirect()
                ->route('admin.jury.detail', $jury)
                ->with('error', 'Aucune grille d\'évaluation associée à ce jury.');
        }

        // Récupérer les données du formulaire
        $scores = $request->input('scores', []);
        $weightedScores = $request->input('weighted_scores', []);
        $comments = $request->input('comments', []);

        // Valider les notes
        foreach ($scores as $criterionId => $rawScore) {
            if ($rawScore !== null && $rawScore !== '' && ($rawScore < 0 || $rawScore > 20)) {
                return redirect()
                    ->route('admin.jury.evaluation', ['jury' => $jury->id, 'candidature' => $candidature->id])
                    ->with('error', 'Les notes doivent être comprises entre 0 et 20.')
                    ->withInput();
            }
        }

        $calculationService = new EvaluationCalculationService;

        // Récupérer toutes les catégories avec leurs critères
        $allCategories = $jury->evaluationGrid->categories()
            ->with(['criteria', 'labellisationStep'])
            ->get();

        DB::transaction(function () use ($jury, $candidature, $juryMember, $allCategories, $calculationService, $scores, $weightedScores, $comments) {
            // Créer ou mettre à jour une évaluation unique pour cette candidature
            $evaluation = Evaluation::where('candidature_id', $candidature->id)
                ->where('jury_id', $jury->id)
                ->where('jury_member_id', $juryMember->id)
                ->first();

            if (! $evaluation) {
                $evaluation = Evaluation::create([
                    'candidature_id' => $candidature->id,
                    'jury_id' => $jury->id,
                    'jury_member_id' => $juryMember->id,
                    'evaluation_grid_id' => $jury->evaluation_grid_id,
                    'labellisation_step_id' => $candidature->current_step_id,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            } else {
                $evaluation->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }

            // Supprimer les anciens scores
            $evaluation->scores()->delete();

            // Créer les nouveaux scores pour tous les critères
            $allCriteria = $allCategories->flatMap->criteria;
            foreach ($allCriteria as $criterion) {
                $criterionId = $criterion->id;
                $rawScore = $scores[$criterionId] ?? null;
                $weightedScore = $weightedScores[$criterionId] ?? null;

                // Ne créer un score que si une note brute a été saisie
                if ($rawScore !== null && $rawScore !== '') {
                    // Si la note pondérée n'est pas fournie, la calculer
                    if ($weightedScore === null || $weightedScore === '') {
                        $weight = $criterion->weight ?? 0;
                        $weightedScore = $calculationService->calculateWeightedScore($rawScore, $weight);
                    }

                    EvaluationScore::create([
                        'evaluation_id' => $evaluation->id,
                        'evaluation_criterion_id' => $criterionId,
                        'raw_score' => $rawScore,
                        'weighted_score' => $weightedScore,
                        'comment' => $comments[$criterionId] ?? null,
                    ]);
                }
            }

            // Calculer et sauvegarder le total du membre
            $memberTotal = $calculationService->calculateMemberTotalScore($evaluation);
            $evaluation->update(['member_total_score' => $memberTotal]);

            // Déclencher l'événement si une étape est définie
            if ($candidature->currentStep) {
                event(new EvaluationSubmitted($evaluation, $candidature, $candidature->currentStep));
            }
        });

        return redirect()
            ->route('admin.jury.evaluation', ['jury' => $jury->id, 'candidature' => $candidature->id])
            ->with('success', 'Votre évaluation a été enregistrée avec succès.');
    }

    /**
     * Validation par le président du jury.
     */
    public function presidentValidate(Request $request, Jury $jury, Candidature $candidature): RedirectResponse
    {
        $user = Auth::user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        // Vérifier que l'utilisateur est président du jury ou super admin
        $juryMember = $jury->members()->where('user_id', $user->id)->first();
        if (! $isSuperAdmin && (! $juryMember || ! $juryMember->is_president)) {
            return redirect()
                ->route('admin.jury.detail', $jury)
                ->with('error', 'Seul le président du jury peut valider les candidatures.');
        }

        $validated = $request->validate([
            'president_comment' => ['nullable', 'string', 'max:5000'],
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        $decision = $validated['decision'];
        $comment = $validated['president_comment'] ?? '';

        // Mettre à jour toutes les évaluations de cette candidature
        $evaluations = Evaluation::where('candidature_id', $candidature->id)
            ->where('jury_id', $jury->id)
            ->get();

        foreach ($evaluations as $evaluation) {
            $evaluation->update([
                'president_comment' => $comment,
                'president_decision' => $decision,
                'president_validated_at' => now(),
            ]);
        }

        // Mettre à jour le statut de la candidature
        if ($decision === 'approved') {
            // Attribuer le badge demandé si approuvé
            $candidature->update([
                'status' => 'validated',
            ]);

            $message = 'La candidature a été approuvée. Le badge a été attribué au formateur.';
        } else {
            $candidature->update([
                'status' => 'rejected',
            ]);

            $message = 'La candidature a été rejetée.';
        }

        return redirect()
            ->route('admin.jury.detail', $jury)
            ->with('success', $message);
    }
}
