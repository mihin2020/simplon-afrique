<?php

namespace App\Livewire\Admin;

use App\Models\EvaluationGrid;
use App\Models\Jury;
use App\Models\JuryMember;
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
        $user = auth()->user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        $query = Jury::with([
            'candidatures.user',
            'candidatures.badge',
            'members.user.roles',
            'evaluationGrid.categories.labellisationStep',
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

        $user = auth()->user()->load('roles');
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
        $user = auth()->user()->load('roles');
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
            ]);
        }

        $user = auth()->user()->load('roles');
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

        return view('livewire.admin.jury-detail', [
            'jury' => $this->jury,
            'roleOptions' => $roleOptions,
            'availableGrids' => $availableGrids,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
