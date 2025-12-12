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
        $superAdminRole = Role::where('name', 'super_admin')->first();
        
        // Récupérer formateurs ET administrateurs (sauf super_admin)
        $query = User::query()
            ->with([
                'roles', // Eager load des rôles
                'formateurPromotions', // Eager load des promotions via pivot (formateurs)
                'promotions', // Eager load des promotions via admin_id (admins)
            ])
            ->whereHas('roles', function ($q) {
                // Inclure formateurs ET administrateurs
                $q->whereIn('roles.name', ['formateur', 'admin']);
            });
        
        // Exclure super_admin
        if ($superAdminRole) {
            $query->whereDoesntHave('roles', function ($q) use ($superAdminRole) {
                $q->where('roles.id', $superAdminRole->id);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('first_name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $users = $query->orderBy('name')->paginate(10);

        // Récupérer tous les IDs des utilisateurs pour une requête optimisée
        $userIds = $users->pluck('id')->toArray();
        
        // Une seule requête pour compter toutes les notes par utilisateur
        $notesCounts = PromotionNote::whereIn('admin_id', $userIds)
            ->selectRaw('admin_id, COUNT(*) as count')
            ->groupBy('admin_id')
            ->pluck('count', 'admin_id')
            ->toArray();

        // Une seule requête pour récupérer la dernière note par utilisateur
        $lastNotes = PromotionNote::whereIn('admin_id', $userIds)
            ->selectRaw('admin_id, MAX(created_at) as last_note_date')
            ->groupBy('admin_id')
            ->get()
            ->keyBy('admin_id');

        // Transformer la collection avec les données déjà chargées (pas de requêtes supplémentaires)
        $users->getCollection()->transform(function ($user) use ($notesCounts, $lastNotes) {
            // Combiner toutes les promotions (formateur + admin) - données déjà chargées
            $allPromotions = $user->formateurPromotions->merge($user->promotions)->unique('id');
            $user->all_promotions = $allPromotions;
            
            // Utiliser les données pré-calculées (pas de requêtes supplémentaires)
            $user->notes_count = $notesCounts[$user->id] ?? 0;
            $user->last_note_date = $lastNotes[$user->id]->last_note_date ?? null;

            return $user;
        });

        return view('livewire.admin.follow-up-management', [
            'users' => $users,
        ]);
    }
}
