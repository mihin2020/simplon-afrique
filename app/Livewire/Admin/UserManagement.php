<?php

namespace App\Livewire\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $activeTab = 'formateurs';

    public $showModal = false;

    public $editingUserId = null;

    public $firstName = '';

    public $lastName = '';

    public $email = '';

    public $role = 'formateur';

    public $search = '';

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->resetPage();

        // Si l'utilisateur n'est pas super_admin, forcer l'onglet formateurs
        if (! $this->isSuperAdmin()) {
            $this->activeTab = 'formateurs';
        }
    }

    public function isSuperAdmin(): bool
    {
        return auth()->user()->roles->contains('name', 'super_admin');
    }

    public function switchTab(string $tab): void
    {
        // Empêcher l'accès à l'onglet administrateurs si l'utilisateur n'est pas super_admin
        if ($tab === 'administrateurs' && ! $this->isSuperAdmin()) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
        $this->reset(['search']);
    }

    public function openModal(?string $userId = null): void
    {
        $this->editingUserId = $userId;
        $this->showModal = true;

        if ($userId) {
            $user = User::findOrFail($userId);
            $userRole = $user->roles->first()?->name ?? 'formateur';

            // Empêcher la modification d'un admin si l'utilisateur n'est pas super_admin
            if ($userRole === 'admin' && ! $this->isSuperAdmin()) {
                $this->closeModal();
                session()->flash('error', 'Vous n\'avez pas la permission de modifier un administrateur.');

                return;
            }

            // Si first_name est vide mais name est rempli, séparer le nom complet
            if (empty($user->first_name) && ! empty($user->name)) {
                $nameParts = explode(' ', trim($user->name), 2);
                $this->firstName = $nameParts[0] ?? '';
                $this->lastName = $nameParts[1] ?? $nameParts[0] ?? '';
            } else {
                $this->firstName = $user->first_name ?? '';
                $this->lastName = $user->name ?? '';
            }
            $this->email = $user->email;
            $this->role = $userRole;
        } else {
            $this->reset(['firstName', 'lastName', 'email']);
            // Si l'utilisateur n'est pas super_admin, forcer le rôle formateur
            $this->role = ($this->activeTab === 'formateurs' || ! $this->isSuperAdmin()) ? 'formateur' : 'admin';
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingUserId', 'firstName', 'lastName', 'email', 'role']);
    }

    public function save(): void
    {
        // Empêcher la création/modification d'admin si l'utilisateur n'est pas super_admin
        if ($this->role === 'admin' && ! $this->isSuperAdmin()) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer ou modifier un administrateur.');

            return;
        }

        $this->validate([
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->editingUserId],
            'role' => ['required', 'string', 'in:formateur,admin'],
        ]);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->update([
                'first_name' => trim($this->firstName),
                'name' => trim($this->lastName),
                'email' => $this->email,
            ]);

            // Mettre à jour le rôle
            $role = Role::where('name', $this->role)->first();
            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        } else {
            // Créer l'utilisateur avec un mot de passe temporaire (qui sera changé lors de l'activation)
            $user = User::create([
                'first_name' => trim($this->firstName),
                'name' => trim($this->lastName),
                'email' => $this->email,
                'password' => Hash::make(\Illuminate\Support\Str::random(32)), // Mot de passe temporaire
            ]);

            $role = Role::where('name', $this->role)->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            // Envoyer l'email d'activation de manière synchrone (sans queue)
            // Forcer l'URL de base à utiliser l'URL de la requête actuelle
            $currentUrl = request()->getSchemeAndHttpHost();
            \Illuminate\Support\Facades\URL::forceRootUrl($currentUrl);

            // Utiliser notify() directement - la notification n'implémente pas ShouldQueue donc elle est synchrone
            $user->notify(new \App\Notifications\UserActivationNotification($this->role));

            // Restaurer l'URL originale
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }

        $this->closeModal();
        session()->flash('message', $this->editingUserId ? 'Utilisateur modifié avec succès.' : 'Utilisateur créé avec succès. Un email d\'activation a été envoyé.');
    }

    public function delete(string $userId): void
    {
        $user = User::findOrFail($userId);
        $userRole = $user->roles->first()?->name ?? 'formateur';

        // Ne pas permettre la suppression du super admin connecté
        if ($user->id === auth()->id()) {
            session()->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');

            return;
        }

        // Empêcher la suppression d'un admin si l'utilisateur n'est pas super_admin
        if ($userRole === 'admin' && ! $this->isSuperAdmin()) {
            session()->flash('error', 'Vous n\'avez pas la permission de supprimer un administrateur.');

            return;
        }

        $user->delete();
        session()->flash('message', 'Utilisateur supprimé avec succès.');
    }

    public function render()
    {
        $roleName = $this->activeTab === 'formateurs' ? 'formateur' : 'admin';
        $role = Role::where('name', $roleName)->first();

        $query = User::query()
            ->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.id', $role->id);
            })
            ->with(['roles', 'juryMembers.jury']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $users = $query->latest()->paginate(10);

        return view('livewire.admin.user-management', [
            'users' => $users,
        ]);
    }
}
