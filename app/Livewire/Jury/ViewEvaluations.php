<?php

namespace App\Livewire\Jury;

use App\Models\Candidature;
use App\Models\Evaluation;
use Livewire\Component;

class ViewEvaluations extends Component
{
    public Candidature $candidature;

    public function mount(string $candidatureId): void
    {
        $this->candidature = Candidature::with(['juries.members', 'steps.labellisationStep', 'badge', 'user'])->findOrFail($candidatureId);

        // Vérifier que l'utilisateur est membre du jury
        $user = auth()->user();
        $jury = $this->candidature->getJury();

        if (! $jury) {
            abort(403, 'Aucun jury assigné à cette candidature.');
        }

        $juryMember = $jury->members()->where('user_id', $user->id)->first();

        if (! $juryMember) {
            abort(403, 'Vous n\'êtes pas membre du jury pour cette candidature.');
        }

        // Vérifier que le président a validé
        $presidentEvaluation = Evaluation::where('candidature_id', $this->candidature->id)
            ->whereHas('juryMember', function ($query) {
                $query->where('is_president', true);
            })
            ->whereNotNull('president_decision')
            ->first();

        if (! $presidentEvaluation) {
            abort(403, 'Le président n\'a pas encore validé cette candidature.');
        }
    }

    public function getEvaluationsByStepProperty()
    {
        $steps = $this->candidature->steps()
            ->with('labellisationStep')
            ->orderBy('labellisationStep.display_order')
            ->get();

        return $steps->map(function ($candidatureStep) {
            $step = $candidatureStep->labellisationStep;
            $evaluations = Evaluation::where('candidature_id', $this->candidature->id)
                ->where('labellisation_step_id', $step->id)
                ->where('status', 'submitted')
                ->with(['juryMember.user', 'scores.criterion'])
                ->get();

            $stepAverage = $evaluations->first()?->average_score ?? 0;
            $presidentEvaluation = $evaluations->firstWhere('juryMember.is_president');

            return [
                'step' => $step,
                'evaluations' => $evaluations,
                'average_score' => $stepAverage,
                'president_comment' => $presidentEvaluation?->president_comment,
                'president_decision' => $presidentEvaluation?->president_decision,
            ];
        });
    }

    public function render()
    {
        return view('livewire.jury.view-evaluations', [
            'evaluationsByStep' => $this->evaluationsByStep,
        ]);
    }
}
