<?php

namespace App\Livewire\Admin;

use App\Models\Jury;
use Livewire\Component;
use Livewire\WithPagination;

class JuriesManagement extends Component
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

    public function deleteJury(string $juryId): void
    {
        $jury = Jury::findOrFail($juryId);
        $jury->delete();

        session()->flash('success', 'Le jury a été supprimé avec succès.');
    }

    public function render()
    {
        $user = auth()->user()->load('roles');
        $isSuperAdmin = $user->roles->contains('name', 'super_admin');

        $query = Jury::with(['candidatures.user', 'members.user', 'evaluationGrid'])
            ->latest();

        // Filtrage selon le rôle
        if (! $isSuperAdmin) {
            // Les administrateurs voient uniquement les jurys dont ils sont membres
            $query->whereHas('members', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('candidatures.user', function ($subQ) {
                        $subQ->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
            });
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $juries = $query->paginate(15);

        $statusOptions = [
            'constituted' => 'Constitué',
            'in_progress' => 'En cours',
            'completed' => 'Terminé',
        ];

        return view('livewire.admin.juries-management', [
            'juries' => $juries,
            'statusOptions' => $statusOptions,
            'isSuperAdmin' => $isSuperAdmin,
        ]);
    }
}
