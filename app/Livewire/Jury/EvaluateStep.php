<?php

namespace App\Livewire\Jury;

use App\Events\EvaluationSubmitted;
use App\Models\Badge;
use App\Models\Candidature;
use App\Models\Evaluation;
use App\Models\EvaluationCriterion;
use App\Models\EvaluationScore;
use App\Models\LabellisationSetting;
use App\Models\LabellisationStep;
use App\Services\EvaluationCalculationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EvaluateStep extends Component
{
    public Candidature $candidature;

    public LabellisationStep $step;

    public ?Evaluation $evaluation = null;

    public array $scores = [];

    public array $comments = [];

    public bool $isReadOnly = false;

    public int $noteScale = 20;

    public array $badgeThresholds = [];

    public function mount(string $candidatureId, string $stepId): void
    {
        $this->candidature = Candidature::with([
            'juries.members',
            'juries.evaluationGrid.categories.criteria',
            'currentStep',
        ])->findOrFail($candidatureId);
        $this->step = LabellisationStep::findOrFail($stepId);

        // Charger l'échelle de notation configurée
        $this->noteScale = LabellisationSetting::getNoteScale();

        // Charger les seuils des badges
        $this->badgeThresholds = Badge::orderBy('min_score')->get()->map(function ($badge) {
            return [
                'name' => $badge->name,
                'label' => $badge->label,
                'min_score' => $badge->min_score,
                'max_score' => $badge->max_score,
                'emoji' => $badge->getEmoji(),
            ];
        })->toArray();

        // Vérifier les permissions d'accès
        $user = auth()->user();
        $user->load('roles');
        $userRoles = $user->roles->pluck('name');
        $isSuperAdmin = $userRoles->contains('super_admin');
        $isAdmin = $userRoles->contains('admin');

        $jury = $this->candidature->getJury();

        if (! $jury) {
            abort(403, 'Aucun jury assigné à cette candidature.');
        }

        $juryMember = $jury->members()->where('user_id', $user->id)->first();

        // Autoriser l'accès si :
        // 1. L'utilisateur est super_admin (accès total)
        // 2. L'utilisateur est admin ET membre du jury
        // 3. L'utilisateur est membre du jury (normal)
        if (! $isSuperAdmin && ! ($isAdmin && $juryMember) && ! $juryMember) {
            abort(403, 'Vous n\'êtes pas membre du jury pour cette candidature.');
        }

        // Vérifier que l'étape est accessible pour évaluation
        $candidatureStep = $this->candidature->steps()
            ->where('labellisation_step_id', $this->step->id)
            ->first();

        // L'étape est accessible si :
        // 1. C'est l'étape courante (in_progress)
        // 2. L'étape est terminée (completed) et l'utilisateur n'a pas encore évalué
        $isCurrentStep = $this->candidature->current_step_id === $this->step->id;
        $isCompletedStep = $candidatureStep && $candidatureStep->status === 'completed';

        if (! $isCurrentStep && ! $isCompletedStep) {
            abort(403, 'Cette étape n\'est pas encore accessible pour évaluation.');
        }

        // Charger ou créer l'évaluation (seulement si l'utilisateur est membre du jury)
        if ($juryMember) {
            $this->evaluation = Evaluation::where('candidature_id', $this->candidature->id)
                ->where('labellisation_step_id', $this->step->id)
                ->where('jury_member_id', $juryMember->id)
                ->first();
        }

        // Vérifier si le président a déjà validé
        $presidentEvaluation = Evaluation::where('candidature_id', $this->candidature->id)
            ->where('labellisation_step_id', $this->step->id)
            ->whereHas('juryMember', function ($query) {
                $query->where('is_president', true);
            })
            ->whereNotNull('president_decision')
            ->first();

        if ($presidentEvaluation) {
            $this->isReadOnly = true;
        }

        // Charger les scores existants
        if ($this->evaluation) {
            foreach ($this->evaluation->scores as $score) {
                $this->scores[$score->evaluation_criterion_id] = $score->raw_score;
                $this->comments[$score->evaluation_criterion_id] = $score->comment ?? '';
            }
        }
    }

    public function updatedScores($value, $criterionId): void
    {
        // Valider que la note est entre 0 et l'échelle configurée
        if ($value < 0) {
            $this->scores[$criterionId] = 0;
        } elseif ($value > $this->noteScale) {
            $this->scores[$criterionId] = $this->noteScale;
        }
    }

    public function getCategoriesProperty()
    {
        $grid = $this->candidature->getEvaluationGrid();

        if (! $grid) {
            return collect();
        }

        // Charger les catégories avec leurs critères pour l'étape courante
        $categories = $grid->categories()
            ->where('labellisation_step_id', $this->step->id)
            ->with(['criteria' => function ($query) {
                $query->orderBy('display_order');
            }])
            ->orderBy('display_order')
            ->get();

        // Filtrer les catégories qui ont au moins un critère
        return $categories->filter(function ($category) {
            return $category->criteria->isNotEmpty();
        });
    }

    /**
     * Calcule la moyenne finale sur 20 (moyenne des notes de catégories).
     */
    public function getTotalWeightedScoreProperty(): float
    {
        $calculationService = new EvaluationCalculationService;
        $categories = $this->categories;

        if ($categories->isEmpty()) {
            return 0.0;
        }

        $categoryScores = [];

        // Calculer la note de chaque catégorie
        foreach ($categories as $category) {
            $categoryTotal = 0;
            $hasScores = false;

            foreach ($category->criteria as $criterion) {
                $rawScore = $this->scores[$criterion->id] ?? null;

                if ($rawScore !== null && $rawScore !== '') {
                    $weight = $criterion->weight ?? 0;
                    $categoryTotal += $calculationService->calculateWeightedScore((float) $rawScore, $weight);
                    $hasScores = true;
                }
            }

            if ($hasScores) {
                // La note de la catégorie est sur 20 (car poids totalisent 100%)
                $categoryScores[] = $categoryTotal;
            }
        }

        if (empty($categoryScores)) {
            return 0.0;
        }

        // La moyenne finale = moyenne des notes de catégories
        $average = array_sum($categoryScores) / count($categoryScores);

        return round($average, 2);
    }

    public function submit(): void
    {
        $categories = $this->categories;

        if ($categories->isEmpty()) {
            session()->flash('error', 'Aucune catégorie trouvée pour cette étape.');

            return;
        }

        // Vérifier que tous les critères sont notés
        $allCriteria = $categories->flatMap->criteria;
        $missingCriteria = $allCriteria->filter(function ($criterion) {
            return ! isset($this->scores[$criterion->id]) || $this->scores[$criterion->id] === null || $this->scores[$criterion->id] === '';
        });

        if ($missingCriteria->isNotEmpty()) {
            session()->flash('error', 'Veuillez noter tous les critères avant de soumettre.');

            return;
        }

        // Valider les notes
        foreach ($this->scores as $criterionId => $rawScore) {
            if ($rawScore < 0 || $rawScore > $this->noteScale) {
                session()->flash('error', "Les notes doivent être comprises entre 0 et {$this->noteScale}.");

                return;
            }
        }

        $user = auth()->user();
        $user->load('roles');
        $jury = $this->candidature->getJury();
        $juryMember = $jury->members()->where('user_id', $user->id)->first();

        // Seuls les membres du jury peuvent soumettre une évaluation
        if (! $juryMember) {
            session()->flash('error', 'Seuls les membres du jury peuvent soumettre une évaluation.');

            return;
        }

        DB::transaction(function () use ($juryMember, $jury) {
            // Créer ou mettre à jour l'évaluation
            if (! $this->evaluation) {
                $this->evaluation = Evaluation::create([
                    'candidature_id' => $this->candidature->id,
                    'jury_id' => $jury->id,
                    'jury_member_id' => $juryMember->id,
                    'evaluation_grid_id' => $this->candidature->getEvaluationGrid()->id,
                    'labellisation_step_id' => $this->step->id,
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            } else {
                $this->evaluation->update([
                    'status' => 'submitted',
                    'submitted_at' => now(),
                ]);
            }

            // Supprimer les anciens scores
            $this->evaluation->scores()->delete();

            // Créer les nouveaux scores avec calcul automatique du weighted_score
            $calculationService = new EvaluationCalculationService;

            foreach ($this->scores as $criterionId => $rawScore) {
                $criterion = EvaluationCriterion::find($criterionId);
                $weight = $criterion->weight ?? 0;
                $weightedScore = $calculationService->calculateWeightedScore($rawScore, $weight);

                EvaluationScore::create([
                    'evaluation_id' => $this->evaluation->id,
                    'evaluation_criterion_id' => $criterionId,
                    'raw_score' => $rawScore,
                    'weighted_score' => $weightedScore,
                    'comment' => $this->comments[$criterionId] ?? null,
                ]);
            }

            // Calculer et sauvegarder le total du membre
            $memberTotal = $calculationService->calculateMemberTotalScore($this->evaluation);
            $this->evaluation->update(['member_total_score' => $memberTotal]);
        });

        // Déclencher l'événement
        event(new EvaluationSubmitted($this->evaluation, $this->candidature, $this->step));

        session()->flash('success', 'Votre évaluation a été soumise avec succès.');
    }

    public function render()
    {
        return view('livewire.jury.evaluate-step', [
            'categories' => $this->categories,
            'noteScale' => $this->noteScale,
            'badgeThresholds' => $this->badgeThresholds,
        ]);
    }
}
