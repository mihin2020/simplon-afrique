<?php

namespace App\Http\Controllers\Admin;

use App\Events\EvaluationSubmitted;
use App\Http\Controllers\Controller;
use App\Mail\BadgeAwardedMail;
use App\Models\Badge;
use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\Evaluation;
use App\Models\EvaluationGrid;
use App\Models\EvaluationScore;
use App\Models\Jury;
use App\Models\JuryMember;
use App\Models\LabellisationSetting;
use App\Models\LabellisationStep;
use App\Services\AttestationService;
use App\Services\EvaluationCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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
        $calculationService = new \App\Services\EvaluationCalculationService;

        if ($juryMember) {
            foreach ($availableCandidatures as $candidature) {
                // Récupérer les évaluations soumises pour cette candidature et ce membre du jury
                $evaluations = Evaluation::where('candidature_id', $candidature->id)
                    ->where('jury_id', $jury->id)
                    ->where('jury_member_id', $juryMember->id)
                    ->where('status', 'submitted')
                    ->with(['scores.criterion.category'])
                    ->get();

                if ($evaluations->isNotEmpty() && $evaluations->first()->scores->isNotEmpty()) {
                    // Calculer la moyenne finale en utilisant l'algorithme par catégorie
                    $totalCategoryScores = 0;
                    $categoryCount = 0;
                    $criteriaCount = 0;

                    foreach ($evaluations as $evaluation) {
                        // Regrouper les scores par catégorie
                        $scoresByCategory = $evaluation->scores->groupBy(function ($score) {
                            return $score->criterion?->category?->id;
                        })->filter(fn ($group, $key) => $key !== null);

                        foreach ($scoresByCategory as $categoryScores) {
                            // Note de la catégorie = somme des notes pondérées (sur 20 si poids = 100%)
                            $categoryScore = $categoryScores->sum('weighted_score');
                            $totalCategoryScores += $categoryScore;
                            $categoryCount++;
                            $criteriaCount += $categoryScores->count();
                        }
                    }

                    // Moyenne finale = moyenne des notes de catégories
                    $averageScore = $categoryCount > 0 ? ($totalCategoryScores / $categoryCount) : 0;

                    $evaluationsData[$candidature->id] = [
                        'evaluated' => true,
                        'total_weighted_score' => $totalCategoryScores,
                        'average_score' => round($averageScore, 2),
                        'criteria_count' => $criteriaCount,
                        'category_count' => $categoryCount,
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
                    ->with(['scores.criterion.category', 'juryMember.user'])
                    ->get();

                $membersEvaluations = [];
                $allMembersEvaluated = true;
                $totalMembersAverages = 0;
                $membersCount = 0;

                // Vérifier si tous les membres ont évalué
                $juryMembersCount = $jury->members()->count();

                foreach ($allEvaluations as $evaluation) {
                    if ($evaluation->scores->isNotEmpty()) {
                        // Regrouper les scores par catégorie
                        $scoresByCategory = $evaluation->scores->groupBy(function ($score) {
                            return $score->criterion?->category?->id;
                        })->filter(fn ($group, $key) => $key !== null);

                        $memberCategoryScores = 0;
                        $memberCategoryCount = 0;
                        $memberCriteriaCount = 0;

                        foreach ($scoresByCategory as $categoryScores) {
                            // Note de la catégorie = somme des notes pondérées (sur 20 si poids = 100%)
                            $categoryScore = $categoryScores->sum('weighted_score');
                            $memberCategoryScores += $categoryScore;
                            $memberCategoryCount++;
                            $memberCriteriaCount += $categoryScores->count();
                        }

                        // Moyenne du membre = moyenne des notes de catégories
                        $memberAverage = $memberCategoryCount > 0 ? ($memberCategoryScores / $memberCategoryCount) : 0;

                        $membersEvaluations[] = [
                            'member_name' => $evaluation->juryMember->user->name ?? 'Membre inconnu',
                            'member_id' => $evaluation->jury_member_id,
                            'total_weighted_score' => $memberCategoryScores,
                            'average_score' => round($memberAverage, 2),
                            'criteria_count' => $memberCriteriaCount,
                            'category_count' => $memberCategoryCount,
                        ];

                        $totalMembersAverages += $memberAverage;
                        $membersCount++;
                    }
                }

                // Vérifier si tous les membres ont évalué
                $allMembersEvaluated = $membersCount >= $juryMembersCount;

                // Calculer la moyenne globale = moyenne des moyennes des membres
                $globalAverage = $membersCount > 0 ? ($totalMembersAverages / $membersCount) : 0;

                // Déterminer le badge décerné en fonction de la moyenne et des seuils configurés
                $awardedBadge = null;
                if ($globalAverage > 0) {
                    $awardedBadge = Badge::where('min_score', '<=', $globalAverage)
                        ->where('max_score', '>=', $globalAverage)
                        ->orderBy('min_score', 'desc')
                        ->first();
                }

                $presidentData[$candidature->id] = [
                    'members_evaluations' => $membersEvaluations,
                    'all_members_evaluated' => $allMembersEvaluated,
                    'members_count' => $membersCount,
                    'total_members' => $juryMembersCount,
                    'global_weighted_score' => $totalMembersAverages,
                    'global_average' => round($globalAverage, 2),
                    'awarded_badge' => $awardedBadge,
                    'president_comment' => $allEvaluations->first()?->president_comment,
                    'president_decision' => $allEvaluations->first()?->president_decision,
                ];
            }
        }

        // Charger les grilles actives pour la sélection (uniquement pour super admin)
        $availableGrids = $isSuperAdmin
            ? EvaluationGrid::where('is_active', true)->orderBy('name')->get()
            : collect();

        return view('admin.jury-detail', [
            'juryId' => $jury->id,
            'jury' => $jury,
            'isSuperAdmin' => $isSuperAdmin,
            'isPresident' => $isPresident,
            'availableCandidatures' => $availableCandidatures,
            'evaluationsData' => $evaluationsData,
            'presidentData' => $presidentData,
            'availableGrids' => $availableGrids,
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

        // Calculer le total des poids des critères
        $totalWeight = $categories->flatMap(fn ($category) => $category->criteria)->sum('weight');

        // Maximum possible de la somme des notes pondérées
        // Après normalisation sur 20, si on met le max (20/20) partout : max = (somme_poids / 100) × 20
        // Mais pour simplifier l'affichage, on considère que le max est toujours 20 (si poids = 100%)
        // Si les poids ne totalisent pas 100%, le max réel sera proportionnel
        $maxWeightedScore = ($totalWeight / 100) * 20;

        // Récupérer l'échelle de notation configurée
        $noteScale = LabellisationSetting::getNoteScale();

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
            'noteScale' => $noteScale,
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

        // Récupérer l'échelle de notation configurée
        $noteScale = LabellisationSetting::getNoteScale();

        // Valider les notes
        foreach ($scores as $criterionId => $rawScore) {
            if ($rawScore !== null && $rawScore !== '' && ($rawScore < 0 || $rawScore > $noteScale)) {
                return redirect()
                    ->route('admin.jury.evaluation', ['jury' => $jury->id, 'candidature' => $candidature->id])
                    ->with('error', "Les notes doivent être comprises entre 0 et {$noteScale}.")
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

        // Récupérer l'étape "Certification"
        $certificationStep = LabellisationStep::where('name', 'certification')->first();

        // Mettre à jour le statut de la candidature
        if ($decision === 'approved') {
            // Calculer le score final et déterminer le badge
            $calculationService = new EvaluationCalculationService;
            $finalScore = $calculationService->calculateFinalScore($candidature);
            $badge = $calculationService->determineBadge($candidature);

            if ($badge) {
                // Générer l'attestation PDF
                $attestationService = new AttestationService;
                $attestationPath = $attestationService->generateAttestation($candidature, $badge, $finalScore);

                // Attribuer le badge et sauvegarder l'attestation
                $candidature->update([
                    'status' => 'validated',
                    'current_step_id' => $certificationStep?->id,
                    'badge_id' => $badge->id,
                    'badge_awarded_at' => now(),
                    'attestation_path' => $attestationPath,
                ]);

                // Envoyer l'email de notification avec l'attestation
                $formateur = $candidature->user;
                Mail::to($formateur->email)->send(new BadgeAwardedMail(
                    $formateur,
                    $badge,
                    $candidature->fresh(),
                    $finalScore
                ));

                $message = "La candidature a été approuvée. Le badge \"{$badge->label}\" a été attribué au formateur et l'attestation a été envoyée par email.";
            } else {
                // Score insuffisant pour un badge
                $candidature->update([
                    'status' => 'rejected',
                    'current_step_id' => $certificationStep?->id,
                ]);

                $message = "La candidature a été approuvée mais le score final ({$finalScore}/20) est insuffisant pour l'attribution d'un badge.";
            }

            // Créer ou mettre à jour le CandidatureStep pour l'étape Certification
            if ($certificationStep) {
                CandidatureStep::updateOrCreate(
                    [
                        'candidature_id' => $candidature->id,
                        'labellisation_step_id' => $certificationStep->id,
                    ],
                    [
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]
                );
            }
        } else {
            $candidature->update([
                'status' => 'rejected',
                'current_step_id' => $certificationStep?->id,
            ]);

            $message = 'La candidature a été rejetée.';
        }

        return redirect()
            ->route('admin.jury.detail', $jury)
            ->with('success', $message);
    }
}
