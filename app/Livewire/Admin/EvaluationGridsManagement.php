<?php

namespace App\Livewire\Admin;

use App\Models\EvaluationGrid;
use Livewire\Component;
use Livewire\WithPagination;

class EvaluationGridsManagement extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $search = '';

    public $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(string $gridId): void
    {
        $grid = EvaluationGrid::findOrFail($gridId);
        $grid->update(['is_active' => ! $grid->is_active]);

        $status = $grid->is_active ? 'activée' : 'désactivée';
        session()->flash('success', "La grille d'évaluation a été {$status} avec succès.");
    }

    public function deleteGrid(string $gridId): void
    {
        $grid = EvaluationGrid::withCount(['evaluations', 'juries'])->findOrFail($gridId);

        // Vérifier si la grille est utilisée dans des évaluations
        if ($grid->evaluations_count > 0) {
            session()->flash('error', 'Impossible de supprimer cette grille car elle est utilisée dans des évaluations.');

            return;
        }

        // Vérifier si la grille est associée à un jury
        if ($grid->juries_count > 0) {
            session()->flash('error', 'Impossible de supprimer cette grille car elle est associée à un ou plusieurs jurys.');

            return;
        }

        $grid->delete();

        session()->flash('success', "La grille d'évaluation a été supprimée avec succès.");
    }

    public function render()
    {
        $query = EvaluationGrid::withCount(['categories', 'evaluations'])
            ->latest();

        if ($this->search) {
            $query->where('name', 'like', '%'.$this->search.'%')
                ->orWhere('description', 'like', '%'.$this->search.'%');
        }

        if ($this->statusFilter !== '') {
            $isActive = $this->statusFilter === '1';
            $query->where('is_active', $isActive);
        }

        $grids = $query->paginate(15);

        return view('livewire.admin.evaluation-grids-management', [
            'grids' => $grids,
        ]);
    }
}
