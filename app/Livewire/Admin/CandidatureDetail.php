<?php

namespace App\Livewire\Admin;

use App\Models\Candidature;
use App\Models\CandidatureStep;
use App\Models\Evaluation;
use App\Models\Jury;
use App\Models\LabellisationStep;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CandidatureDetail extends Component
{
    public string $candidatureId;

    public ?Candidature $candidature = null;

    public $selectedJuryId = '';

    /**
     * Vérifier si l'utilisateur actuel est super admin
     */
    private function isSuperAdmin(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        $user->loadMissing('roles');

        return $user->roles->contains('name', 'super_admin');
    }

    public function mount(string $candidatureId): void
    {
        $this->candidatureId = $candidatureId;
        $this->loadCandidature();
    }

    public function loadCandidature(): void
    {
        $this->candidature = Candidature::with([
            'user.formateurProfile',
            'badge',
            'currentStep',
            'steps.labellisationStep',
            'juries',
        ])->findOrFail($this->candidatureId);

        // Si la candidature a déjà un jury assigné, le sélectionner
        $assignedJury = $this->candidature->juries->first();
        $this->selectedJuryId = $assignedJury ? $assignedJury->id : '';
    }

    /**
     * Valider la candidature (step 1) - Uniquement pour super admin
     */
    public function validateCandidature(): void
    {
        try {
            // Vérifier les permissions
            if (! $this->isSuperAdmin()) {
                $this->dispatch('show-error', message: 'Seul le super administrateur peut valider une candidature.');

                return;
            }

            // Charger la candidature si nécessaire
            if (! $this->candidature) {
                $this->loadCandidature();
            }

            if (! $this->candidature) {
                $this->dispatch('show-error', message: 'Candidature non trouvée.');

                return;
            }

            // Vérifier que la candidature est soumise
            if ($this->candidature->status !== 'submitted') {
                $this->dispatch('show-error', message: 'Cette candidature n\'est pas en attente de validation. Statut actuel : '.$this->candidature->status);
                $this->loadCandidature();

                return;
            }

            // Récupérer la première étape (step 1)
            $firstStep = LabellisationStep::orderBy('display_order')->first();

            if (! $firstStep) {
                $this->dispatch('show-error', message: 'Aucune étape de labellisation trouvée.');

                return;
            }

            // Créer ou mettre à jour le CandidatureStep pour la première étape
            CandidatureStep::updateOrCreate(
                [
                    'candidature_id' => $this->candidature->id,
                    'labellisation_step_id' => $firstStep->id,
                ],
                [
                    'status' => 'in_progress',
                ]
            );

            // Mettre à jour la candidature directement dans la base de données
            DB::table('candidatures')
                ->where('id', $this->candidature->id)
                ->update([
                    'status' => 'in_review',
                    'current_step_id' => $firstStep->id,
                    'updated_at' => now(),
                ]);

            // Recharger la candidature depuis la base de données
            $this->loadCandidature();

            // Forcer le re-render du composant
            $this->dispatch('candidature-validated');

            session()->flash('success', 'La candidature a été validée avec succès. L\'étape 1 ('.$firstStep->label.') est maintenant en cours. Vous pouvez maintenant assigner un jury.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : '.$e->getMessage());
            $this->loadCandidature();
        }
    }

    public function updatedSelectedJuryId(): void
    {
        if ($this->selectedJuryId) {
            // Vérifier que la candidature est en examen ou validée (step 1 validée)
            if (! in_array($this->candidature->status, ['in_review', 'validated']) || ! $this->candidature->current_step_id) {
                session()->flash('error', 'Vous devez d\'abord valider la candidature (étape 1) avant d\'assigner un jury.');
                $this->selectedJuryId = '';
                $this->loadCandidature();

                return;
            }

            // Vérifier que le jury existe et est constitué
            $jury = Jury::with('members.user')->find($this->selectedJuryId);

            if (! $jury) {
                session()->flash('error', 'Le jury sélectionné n\'existe pas.');
                $this->loadCandidature();

                return;
            }

            if ($jury->members->isEmpty()) {
                session()->flash('error', 'Ce jury n\'est pas encore constitué. Veuillez d\'abord ajouter des membres au jury.');
                $this->selectedJuryId = '';
                $this->loadCandidature();

                return;
            }

            // Vérifier si la candidature a déjà un jury assigné
            $existingJury = $this->candidature->juries->first();
            if ($existingJury && $existingJury->id !== $jury->id) {
                // Vérifier s'il y a des évaluations soumises pour l'ancien jury
                $hasEvaluations = Evaluation::where('candidature_id', $this->candidature->id)
                    ->where('jury_id', $existingJury->id)
                    ->where('status', 'submitted')
                    ->exists();

                if ($hasEvaluations) {
                    session()->flash('error', 'Impossible de remplacer le jury. Des évaluations ont déjà été soumises par les membres du jury actuel. Vous devez d\'abord supprimer les évaluations avant de pouvoir changer de jury.');
                    $this->selectedJuryId = $existingJury->id; // Réinitialiser la sélection
                    $this->loadCandidature();

                    return;
                }

                // Retirer l'ancien jury
                $this->candidature->juries()->detach($existingJury->id);

                // Mettre à jour le statut de l'ancien jury s'il n'a plus de candidatures
                $otherCandidatures = $existingJury->candidatures()->where('candidatures.id', '!=', $this->candidature->id)->count();
                if ($otherCandidatures === 0 && $existingJury->status === 'in_progress') {
                    $existingJury->update(['status' => 'constituted']);
                }
            }

            // Assigner le nouveau jury à la candidature
            $this->candidature->juries()->sync([$this->selectedJuryId]);

            // Mettre à jour le statut du jury
            if ($jury->status === 'constituted') {
                $jury->update(['status' => 'in_progress']);
            }

            // Informations sur les membres du jury
            $membersCount = $jury->members->count();

            session()->flash('success', 'Le jury "'.$jury->name.'" ('.$membersCount.' membre'.($membersCount > 1 ? 's' : '').') a été assigné à cette candidature. Les membres du jury peuvent maintenant procéder à l\'évaluation.');
        } else {
            // Retirer l'assignation
            $assignedJury = $this->candidature->juries->first();

            if ($assignedJury) {
                // Vérifier s'il y a des évaluations soumises pour cette candidature
                $hasEvaluations = Evaluation::where('candidature_id', $this->candidature->id)
                    ->where('jury_id', $assignedJury->id)
                    ->where('status', 'submitted')
                    ->exists();

                if ($hasEvaluations) {
                    session()->flash('error', 'Impossible de retirer le jury. Des évaluations ont déjà été soumises par les membres du jury. Vous devez d\'abord supprimer les évaluations avant de pouvoir retirer le jury.');
                    $this->selectedJuryId = $assignedJury->id; // Réinitialiser la sélection
                    $this->loadCandidature();

                    return;
                }

                $juryName = $assignedJury->name;
                $this->candidature->juries()->detach();

                // Mettre à jour le statut du jury s'il n'a plus de candidatures
                $otherCandidatures = $assignedJury->candidatures()->count();
                if ($otherCandidatures === 0 && $assignedJury->status === 'in_progress') {
                    $assignedJury->update(['status' => 'constituted']);
                }

                session()->flash('success', 'Le jury "'.$juryName.'" a été retiré de cette candidature.');
            } else {
                session()->flash('info', 'Aucun jury n\'était assigné à cette candidature.');
            }
        }
        $this->loadCandidature();
    }

    public function render()
    {
        // Récupérer tous les jurys constitués ou en cours (avec au moins un membre)
        $availableJuries = Jury::with('members.user')
            ->whereIn('status', ['constituted', 'in_progress'])
            ->has('members') // Uniquement les jurys qui ont au moins un membre
            ->orderBy('name')
            ->get();

        $roleOptions = [
            'referent_pedagogique' => 'Référent Pédagogique',
            'directeur_pedagogique' => 'Directeur Pédagogique',
            'formateur_senior' => 'Formateur Senior',
        ];

        $isSuperAdmin = $this->isSuperAdmin();

        // Permettre l'assignation de jury si :
        // 1. La candidature est validée (toujours permettre la modification)
        // 2. OU la candidature est en examen ET a un current_step_id
        // 3. OU la candidature a déjà un jury assigné (pour permettre la modification)
        $canAssignJury = $this->candidature && (
            $this->candidature->status === 'validated' ||
            ($this->candidature->status === 'in_review' && $this->candidature->current_step_id !== null) ||
            $this->candidature->juries->isNotEmpty()
        );

        // Vérifier s'il y a des évaluations soumises pour le jury assigné
        $hasEvaluations = false;
        $assignedJury = $this->candidature->juries->first();
        if ($assignedJury) {
            $hasEvaluations = Evaluation::where('candidature_id', $this->candidature->id)
                ->where('jury_id', $assignedJury->id)
                ->where('status', 'submitted')
                ->exists();
        }

        return view('livewire.admin.candidature-detail', [
            'availableJuries' => $availableJuries,
            'roleOptions' => $roleOptions,
            'isSuperAdmin' => $isSuperAdmin,
            'canAssignJury' => $canAssignJury,
            'hasEvaluations' => $hasEvaluations,
        ]);
    }
}
