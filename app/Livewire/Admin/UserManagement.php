<?php

namespace App\Livewire\Admin;

use App\Data\CountriesData;
use App\Models\FormateurProfile;
use App\Models\Organization;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithPagination;

class UserManagement extends Component
{
    use WithPagination;

    public $activeTab = 'formateurs';

    public $showModal = false;

    public $showDetailsModal = false;

    public $viewingUser = null;

    public $editingUserId = null;

    public $firstName = '';

    public $lastName = '';

    public $email = '';

    public $role = 'formateur';

    public $country = '';

    public $organizationId = '';

    public $phoneCountryCode = '+33';

    public $phoneNumber = '';

    public $trainingType = '';

    public $search = '';

    // Propriétés pour le référent pédagogique
    public $isReferentPedagogique = false;

    public $referentCountry = '';

    public $selectedOrganizations = [];

    // Filtres pour la liste des formateurs
    public $filterCountry = '';

    public $filterOrganization = '';

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
        if (! Auth::check()) {
            return false;
        }

        /** @var User $user */
        $user = Auth::user();

        return $user->roles->contains('name', 'super_admin');
    }

    public function switchTab(string $tab): void
    {
        // Empêcher l'accès à l'onglet administrateurs si l'utilisateur n'est pas super_admin
        if ($tab === 'administrateurs' && ! $this->isSuperAdmin()) {
            return;
        }

        $this->activeTab = $tab;
        $this->resetPage();
        $this->reset(['search', 'filterCountry', 'filterOrganization']);
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

            // Charger les données du profil formateur si c'est un formateur
            if ($userRole === 'formateur' && $user->formateurProfile) {
                $profile = $user->formateurProfile;
                $this->country = $profile->country ?? '';
                $this->organizationId = $profile->organization_id ?? '';
                $this->phoneCountryCode = $profile->phone_country_code ?? '+33';
                $this->phoneNumber = $profile->phone_number ?? '';
                $this->trainingType = $profile->training_type ?? '';
            } else {
                $this->reset(['country', 'organizationId', 'phoneCountryCode', 'phoneNumber', 'trainingType']);
            }

            // Charger les données du référent pédagogique si c'est un admin
            if ($userRole === 'admin' && $this->isSuperAdmin()) {
                $this->isReferentPedagogique = $user->is_referent_pedagogique ?? false;
                $this->referentCountry = $user->country ?? '';
                $this->selectedOrganizations = $user->referentOrganizations()->pluck('organizations.id')->toArray();
            } else {
                $this->reset(['isReferentPedagogique', 'referentCountry', 'selectedOrganizations']);
            }
        } else {
            $this->reset(['firstName', 'lastName', 'email', 'country', 'organizationId', 'phoneCountryCode', 'phoneNumber', 'trainingType', 'isReferentPedagogique', 'referentCountry', 'selectedOrganizations']);
            // Si l'utilisateur n'est pas super_admin, forcer le rôle formateur
            $this->role = ($this->activeTab === 'formateurs' || ! $this->isSuperAdmin()) ? 'formateur' : 'admin';
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingUserId', 'firstName', 'lastName', 'email', 'role', 'country', 'organizationId', 'phoneCountryCode', 'phoneNumber', 'trainingType', 'isReferentPedagogique', 'referentCountry', 'selectedOrganizations']);
    }

    public function openDetailsModal(string $userId): void
    {
        $this->viewingUser = User::with(['formateurProfile.organization', 'formateurProfile.certifications', 'roles'])->find($userId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->viewingUser = null;
    }

    public function save(): void
    {
        // Empêcher la création/modification d'admin si l'utilisateur n'est pas super_admin
        if ($this->role === 'admin' && ! $this->isSuperAdmin()) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer ou modifier un administrateur.');

            return;
        }

        $rules = [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->editingUserId],
            'role' => ['required', 'string', 'in:formateur,admin'],
        ];

        // Ajouter les règles de validation pour les formateurs uniquement
        if ($this->role === 'formateur') {
            $rules['country'] = ['nullable', 'string', 'max:255'];
            $rules['organizationId'] = ['nullable', 'uuid', 'exists:organizations,id'];
            $rules['phoneCountryCode'] = ['nullable', 'string', 'max:10'];
            $rules['phoneNumber'] = ['nullable', 'string', 'max:30'];
            $rules['trainingType'] = ['nullable', 'string', 'in:interne,externe'];
        }

        // Ajouter les règles de validation pour les référents pédagogiques
        if ($this->role === 'admin' && $this->isSuperAdmin()) {
            $rules['isReferentPedagogique'] = ['nullable', 'boolean'];
            if ($this->isReferentPedagogique) {
                $rules['referentCountry'] = ['required', 'string', 'max:255'];
            }
            $rules['selectedOrganizations'] = ['nullable', 'array'];
            $rules['selectedOrganizations.*'] = ['uuid', 'exists:organizations,id'];
        }

        $this->validate($rules);

        // Vérifier les restrictions si l'utilisateur connecté est référent pédagogique
        if (Auth::check() && $this->role === 'formateur') {
            /** @var User $currentUser */
            $currentUser = Auth::user();

            if ($currentUser->isReferentPedagogique()) {
                // Vérifier le pays
                if (! empty($currentUser->country) && $this->country !== $currentUser->country) {
                    session()->flash('error', 'Vous ne pouvez ajouter que des formateurs du même pays que vous ('.$currentUser->country.').');

                    return;
                }

                // Vérifier l'organisation si le référent a des organisations assignées
                $referentOrganizations = $currentUser->referentOrganizations()->pluck('organizations.id')->toArray();
                if (! empty($referentOrganizations) && ! empty($this->organizationId)) {
                    if (! in_array($this->organizationId, $referentOrganizations)) {
                        session()->flash('error', 'Vous ne pouvez ajouter que des formateurs appartenant à vos organisations assignées.');

                        return;
                    }
                }
            }
        }

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

            // Mettre à jour les données du référent pédagogique si c'est un admin
            if ($this->role === 'admin' && $this->isSuperAdmin()) {
                $user->update([
                    'is_referent_pedagogique' => $this->isReferentPedagogique ?? false,
                    'country' => $this->isReferentPedagogique ? ($this->referentCountry ?: null) : null,
                ]);

                // Synchroniser les organisations du référent
                if ($this->isReferentPedagogique) {
                    $user->referentOrganizations()->sync($this->selectedOrganizations ?? []);
                } else {
                    $user->referentOrganizations()->sync([]);
                }
            }

            // Mettre à jour ou créer le profil formateur si c'est un formateur
            if ($this->role === 'formateur') {
                $profileData = [
                    'phone_country_code' => $this->phoneCountryCode ?: null,
                    'phone_number' => $this->phoneNumber ?: null,
                    'country' => $this->country ?: null,
                    'organization_id' => $this->organizationId ?: null,
                    'training_type' => $this->trainingType ?: null,
                ];

                $profile = $user->formateurProfile;
                if ($profile) {
                    $profile->update($profileData);
                } else {
                    $profileData['user_id'] = $user->id;
                    FormateurProfile::create($profileData);
                }
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

            // Mettre à jour les données du référent pédagogique si c'est un admin
            if ($this->role === 'admin' && $this->isSuperAdmin()) {
                $user->update([
                    'is_referent_pedagogique' => $this->isReferentPedagogique ?? false,
                    'country' => $this->isReferentPedagogique ? ($this->referentCountry ?: null) : null,
                ]);

                // Synchroniser les organisations du référent
                if ($this->isReferentPedagogique) {
                    $user->referentOrganizations()->sync($this->selectedOrganizations ?? []);
                }
            }

            // Créer le profil formateur si c'est un formateur
            if ($this->role === 'formateur') {
                FormateurProfile::create([
                    'user_id' => $user->id,
                    'phone_country_code' => $this->phoneCountryCode ?: null,
                    'phone_number' => $this->phoneNumber ?: null,
                    'country' => $this->country ?: null,
                    'organization_id' => $this->organizationId ?: null,
                    'training_type' => $this->trainingType ?: null,
                ]);
            }
        }

        $wasEditing = (bool) $this->editingUserId;
        $this->closeModal();
        session()->flash('message', $wasEditing ? 'Utilisateur modifié avec succès.' : 'Utilisateur créé avec succès. Un email d\'activation a été envoyé.');
    }

    public function delete(string $userId): void
    {
        $user = User::findOrFail($userId);
        $userRole = $user->roles->first()?->name ?? 'formateur';

        // Ne pas permettre la suppression du super admin connecté
        if ($user->id === Auth::id()) {
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

        // Appliquer les restrictions du référent pédagogique pour les formateurs
        if ($roleName === 'formateur' && Auth::check()) {
            /** @var User $currentUser */
            $currentUser = Auth::user();
            $query->forReferent($currentUser);
        }

        // Appliquer les filtres dynamiques
        if ($this->filterCountry) {
            $query->whereHas('formateurProfile', function ($q) {
                $q->where('country', $this->filterCountry);
            });
        }

        if ($this->filterOrganization) {
            $query->whereHas('formateurProfile', function ($q) {
                $q->where('organization_id', $this->filterOrganization);
            });
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%'.$this->search.'%')
                    ->orWhere('email', 'like', '%'.$this->search.'%');
            });
        }

        $users = $query->latest()->paginate(10);

        // Pour les administrateurs, charger aussi les organisations du référent
        if ($roleName === 'admin') {
            $users->load('referentOrganizations');
        }

        return view('livewire.admin.user-management', [
            'users' => $users,
            'organizations' => Organization::orderBy('name')->get(),
            'countries' => CountriesData::getCountries(),
            'phoneCountryCodes' => CountriesData::getPhoneCountryCodes(),
        ]);
    }
}