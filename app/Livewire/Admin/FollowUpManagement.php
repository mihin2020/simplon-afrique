<?php

namespace App\Livewire\Admin;

use App\Models\Promotion;
use App\Models\PromotionNote;
use App\Models\Role;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class FollowUpManagement extends Component
{
    use WithPagination;

    public $selectedAdminId = null;

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function mount(?string $adminId = null): void
    {
        $this->selectedAdminId = $adminId;
        $this->resetPage();
    }

    public function selectAdmin(string $adminId): void
    {
        $this->selectedAdminId = $adminId;
        $this->resetPage();
        $this->dispatch('admin-selected', ['adminId' => $adminId]);
    }

    public function clearSelection(): void
    {
        $this->selectedAdminId = null;
        $this->resetPage();
        $this->dispatch('admin-selection-cleared');
    }

    public function render()
    {
        $adminRole = Role::where('name', 'admin')->first();
        $query = User::whereHas('roles', function ($q) use ($adminRole) {
            $q->where('roles.id', $adminRole->id);
        })->with(['promotions', 'promotionNotes']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $admins = $query->orderBy('name')->paginate(10);

        // Charger les statistiques pour chaque admin
        $admins->getCollection()->transform(function ($admin) {
            $admin->notes_count = $admin->promotionNotes()->count();
            $admin->last_note_date = $admin->promotionNotes()->latest()->first()?->created_at;
            $admin->promotion = $admin->promotions()->first();

            return $admin;
        });

        return view('livewire.admin.follow-up-management', [
            'admins' => $admins,
        ]);
    }
}
