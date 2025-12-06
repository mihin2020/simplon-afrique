<?php

namespace App\Livewire\Jury;

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\Evaluation;
use App\Models\LabellisationStep;
use App\Services\EvaluationCalculationService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PresidentValidation extends Component
{
    public Candidature $candidature;

    public ?Evaluation $presidentEvaluation = null;

    public string $presidentComment = '';

    public ?Badge $proposedBadge = null;

    public float $finalScore = 0;

    public function mount(string $candidatureId): void
    {
        $this->candidature = Candidature::with(['juries.members', 'steps.labellisationStep', 'badge'])->findOrFail($candidatureId);

        // Vérifier que l'utilisateur est président du jury
        $user = auth()->user();
        $jury = $this->candidature->getJury();

        if (! $jury) {
            abort(403, 'Aucun jury assigné à cette candidature.');
        }

        $juryMember = $jury->members()->where('user_id', $user->id)->where('is_president', true)->first();

        if (! $juryMember) {
            abort(403, 'Vous n\'êtes pas le président du jury pour cette candidature.');
        }

        // Vérifier que toutes les étapes sont terminées
        $allStepsCompleted = $this->candidature->steps()
            ->where('status', 'completed')
            ->count() === $this->candidature->steps()->count();

        if (! $allStepsCompleted) {
            abort(403, 'Toutes les étapes doivent être terminées avant la validation président.');
        }

        // Charger l'évaluation du président (peut être n'importe quelle évaluation de l'étape finale)
        $lastStep = $this->candidature->steps()
            ->with('labellisationStep')
            ->get()
            ->sortByDesc(function ($step) {
                return $step->labellisationStep->display_order ?? 0;
            })
            ->first();

        if ($lastStep) {
            $this->presidentEvaluation = Evaluation::where('candidature_id', $this->candidature->id)
                ->where('labellisation_step_id', $lastStep->labellisation_step_id)
                ->where('jury_member_id', $juryMember->id)
                ->first();

            if ($this->presidentEvaluation && $this->presidentEvaluation->president_comment) {
                $this->presidentComment = $this->presidentEvaluation->president_comment;
            }
        }

        // Calculer la note finale et déterminer le badge
        $calculationService = new EvaluationCalculationService;
        $this->finalScore = $calculationService->calculateFinalScore($this->candidature);
        $this->proposedBadge = $calculationService->determineBadge($this->candidature);
    }

    public function approve(): void
    {
        $this->validate([
            'presidentComment' => 'required|string|min:10',
        ], [
            'presidentComment.required' => 'Le commentaire général est obligatoire.',
            'presidentComment.min' => 'Le commentaire doit contenir au moins 10 caractères.',
        ]);

        if (! $this->proposedBadge) {
            session()->flash('error', 'La note finale est insuffisante pour attribuer un badge (minimum 10/20).');

            return;
        }

        DB::transaction(function () {
            // Mettre à jour toutes les évaluations de cette candidature avec la décision du président
            Evaluation::where('candidature_id', $this->candidature->id)
                ->update([
                    'president_decision' => 'approved',
                    'president_comment' => $this->presidentComment,
                    'president_validated_at' => now(),
                ]);

            // Récupérer l'étape "Certification"
            $certificationStep = LabellisationStep::where('name', 'certification')->first();

            // Mettre à jour la candidature
            $this->candidature->update([
                'badge_id' => $this->proposedBadge->id,
                'status' => 'validated',
                'current_step_id' => $certificationStep?->id,
            ]);

            // Créer ou mettre à jour le CandidatureStep pour l'étape Certification
            if ($certificationStep) {
                CandidatureStep::updateOrCreate(
                    [
                        'candidature_id' => $this->candidature->id,
                        'labellisation_step_id' => $certificationStep->id,
                    ],
                    [
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]
                );
            }
        });

        session()->flash('success', 'La candidature a été validée avec succès. Le badge '.$this->proposedBadge->label.' a été attribué.');
        $this->redirect(route('admin.candidature.show', $this->candidature->id));
    }

    public function reject(): void
    {
        $this->validate([
            'presidentComment' => 'required|string|min:10',
        ], [
            'presidentComment.required' => 'Le commentaire général est obligatoire pour justifier le rejet.',
            'presidentComment.min' => 'Le commentaire doit contenir au moins 10 caractères.',
        ]);

        DB::transaction(function () {
            // Mettre à jour toutes les évaluations de cette candidature avec la décision du président
            Evaluation::where('candidature_id', $this->candidature->id)
                ->update([
                    'president_decision' => 'rejected',
                    'president_comment' => $this->presidentComment,
                    'president_validated_at' => now(),
                ]);

            // Mettre à jour la candidature
            $this->candidature->update([
                'status' => 'rejected',
            ]);
        });

        session()->flash('success', 'La candidature a été rejetée.');
        $this->redirect(route('admin.candidature.show', $this->candidature->id));
    }

    public function getEvaluationsByStepProperty()
    {
        $steps = $this->candidature->steps()
            ->with('labellisationStep')
            ->get()
            ->sortBy(function ($step) {
                return $step->labellisationStep->display_order ?? 0;
            });

        return $steps->map(function ($candidatureStep) {
            $step = $candidatureStep->labellisationStep;
            $evaluations = Evaluation::where('candidature_id', $this->candidature->id)
                ->where('labellisation_step_id', $step->id)
                ->where('status', 'submitted')
                ->with(['juryMember.user', 'scores.criterion'])
                ->get();

            $stepAverage = $evaluations->first()?->average_score ?? 0;

            return [
                'step' => $step,
                'evaluations' => $evaluations,
                'average_score' => $stepAverage,
            ];
        });
    }

    public function render()
    {
        return view('livewire.jury.president-validation', [
            'evaluationsByStep' => $this->evaluationsByStep,
        ]);
    }
}
