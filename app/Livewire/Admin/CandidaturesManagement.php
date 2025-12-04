<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\Candidature;
use App\Models\LabellisationStep;
use Livewire\Component;
use Livewire\WithPagination;

class CandidaturesManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public $statusFilter = '';

    public $badgeFilter = '';

    public $stepFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingBadgeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStepFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->badgeFilter = '';
        $this->stepFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $query = Candidature::with(['user', 'badge', 'currentStep'])
            ->latest();

        // Filtre par recherche (nom ou email du formateur)
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        // Filtre par statut
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Filtre par badge
        if ($this->badgeFilter) {
            $query->where('badge_id', $this->badgeFilter);
        }

        // Filtre par étape
        if ($this->stepFilter) {
            $query->where('current_step_id', $this->stepFilter);
        }

        $candidatures = $query->paginate(15);

        $badges = Badge::orderBy('min_score')->get();
        $steps = LabellisationStep::orderBy('display_order')->get();

        $statusOptions = [
            'draft' => 'Brouillon',
            'submitted' => 'Soumise',
            'in_review' => 'En examen',
            'validated' => 'Validée',
            'rejected' => 'Rejetée',
        ];

        return view('livewire.admin.candidatures-management', [
            'candidatures' => $candidatures,
            'badges' => $badges,
            'steps' => $steps,
            'statusOptions' => $statusOptions,
        ]);
    }
}
