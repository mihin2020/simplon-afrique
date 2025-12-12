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

    public $selectedOrganizations = [];

    public $phoneCountryCode = '+33';

    public $phoneNumber = '';

    public $trainingType = '';

    public $search = '';

    // Propriétés pour le référent pédagogique
    public $isReferentPedagogique = false;

    public $referentCountry = '';

    public $selectedReferentOrganizations = [];

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

    /**
     * Parse les termes de recherche séparés par des virgules.
     * Nettoie et normalise chaque terme pour une recherche flexible.
     *
     * @return array<string>
     */
    private function parseSearchTerms(string $search): array
    {
        // Séparer par virgule
        $terms = explode(',', $search);

        // Nettoyer chaque terme : trim, supprimer les espaces multiples, filtrer les vides
        $cleanedTerms = array_map(function ($term) {
            // Trim et normaliser les espaces
            $cleaned = trim($term);
            $cleaned = preg_replace('/\s+/', ' ', $cleaned); // Remplacer les espaces multiples par un seul espace

            return $cleaned;
        }, $terms);

        // Filtrer les termes vides
        return array_filter($cleanedTerms, fn ($term) => ! empty($term));
    }

    public function switchTab(string $tab): void
    {
        // Empêcher l'accès aux onglets administrateurs et super_administrateurs si l'utilisateur n'est pas super_admin
        if (($tab === 'administrateurs' || $tab === 'super_administrateurs') && ! $this->isSuperAdmin()) {
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

            // Empêcher la modification d'un admin ou super_admin si l'utilisateur n'est pas super_admin
            if (($userRole === 'admin' || $userRole === 'super_admin') && ! $this->isSuperAdmin()) {
                $this->closeModal();
                session()->flash('error', 'Vous n\'avez pas la permission de modifier cet utilisateur.');

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
                $this->selectedOrganizations = $profile->organizations()->pluck('organizations.id')->toArray();
                $this->phoneCountryCode = $profile->phone_country_code ?? '+33';
                $this->phoneNumber = $profile->phone_number ?? '';
                $this->trainingType = $profile->training_type ?? '';
            } else {
                $this->reset(['country', 'selectedOrganizations', 'phoneCountryCode', 'phoneNumber', 'trainingType']);
            }

            // Pour super_admin, ne pas charger de données supplémentaires
            if ($userRole === 'super_admin') {
                $this->reset(['country', 'selectedOrganizations', 'phoneCountryCode', 'phoneNumber', 'trainingType', 'isReferentPedagogique', 'referentCountry', 'selectedReferentOrganizations']);
            }

            // Charger les données du référent pédagogique si c'est un admin
            if ($userRole === 'admin' && $this->isSuperAdmin()) {
                $this->isReferentPedagogique = $user->is_referent_pedagogique ?? false;
                $this->referentCountry = $user->country ?? '';
                $this->selectedReferentOrganizations = $user->referentOrganizations()->pluck('organizations.id')->toArray();
            } else {
                $this->reset(['isReferentPedagogique', 'referentCountry', 'selectedReferentOrganizations']);
            }
        } else {
            $this->reset(['firstName', 'lastName', 'email', 'country', 'selectedOrganizations', 'phoneCountryCode', 'phoneNumber', 'trainingType', 'isReferentPedagogique', 'referentCountry', 'selectedReferentOrganizations']);
            // Définir le rôle selon l'onglet actif
            if ($this->activeTab === 'super_administrateurs') {
                $this->role = 'super_admin';
            } elseif ($this->activeTab === 'administrateurs') {
                $this->role = 'admin';
            } else {
                $this->role = 'formateur';
            }

            // Si l'utilisateur connecté est référent pédagogique (et pas super_admin), définir automatiquement le pays
            if ($this->role === 'formateur' && Auth::check()) {
                /** @var User $currentUser */
                $currentUser = Auth::user();
                if ($currentUser->isReferentPedagogique() && ! $this->isSuperAdmin()) {
                    $this->country = $currentUser->country ?? '';
                }
            }
        }
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['editingUserId', 'firstName', 'lastName', 'email', 'role', 'country', 'selectedOrganizations', 'phoneCountryCode', 'phoneNumber', 'trainingType', 'isReferentPedagogique', 'referentCountry', 'selectedReferentOrganizations']);
    }

    public function openDetailsModal(string $userId): void
    {
        $this->viewingUser = User::with(['formateurProfile.organizations', 'formateurProfile.certifications', 'roles'])->find($userId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->viewingUser = null;
    }

    public function save(): void
    {
        // Empêcher la création/modification d'admin ou super_admin si l'utilisateur n'est pas super_admin
        if (($this->role === 'admin' || $this->role === 'super_admin') && ! $this->isSuperAdmin()) {
            session()->flash('error', 'Vous n\'avez pas la permission de créer ou modifier cet utilisateur.');

            return;
        }

        $rules = [
            'firstName' => ['required', 'string', 'max:255'],
            'lastName' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$this->editingUserId],
            'role' => ['required', 'string', 'in:formateur,admin,super_admin'],
        ];

        // Ajouter les règles de validation pour les formateurs uniquement
        if ($this->role === 'formateur') {
            $rules['country'] = ['nullable', 'string', 'max:255'];
            $rules['selectedOrganizations'] = ['nullable', 'array'];
            $rules['selectedOrganizations.*'] = ['uuid', 'exists:organizations,id'];
            $rules['phoneCountryCode'] = ['nullable', 'string', 'max:10'];
            $rules['phoneNumber'] = ['nullable', 'string', 'max:30'];
            $rules['trainingType'] = ['nullable', 'string', 'in:interne,externe'];
        }

        // Pour super_admin, pas de règles supplémentaires (seulement nom, prénom, email)

        // Ajouter les règles de validation pour les référents pédagogiques
        if ($this->role === 'admin' && $this->isSuperAdmin()) {
            $rules['isReferentPedagogique'] = ['nullable', 'boolean'];
            if ($this->isReferentPedagogique) {
                $rules['referentCountry'] = ['required', 'string', 'max:255'];
            }
            $rules['selectedReferentOrganizations'] = ['nullable', 'array'];
            $rules['selectedReferentOrganizations.*'] = ['uuid', 'exists:organizations,id'];
        }

        $this->validate($rules);

        // Vérifier les restrictions si l'utilisateur connecté est référent pédagogique
        if (Auth::check() && $this->role === 'formateur') {
            /** @var User $currentUser */
            $currentUser = Auth::user();

            // Si l'utilisateur est référent pédagogique (et pas super_admin), définir automatiquement le pays
            if ($currentUser->isReferentPedagogique() && ! $this->isSuperAdmin()) {
                if (! empty($currentUser->country)) {
                    $this->country = $currentUser->country;
                }
            }

            if ($currentUser->isReferentPedagogique()) {
                // Vérifier le pays
                if (! empty($currentUser->country) && $this->country !== $currentUser->country) {
                    session()->flash('error', 'Vous ne pouvez ajouter que des formateurs du même pays que vous ('.$currentUser->country.').');

                    return;
                }

                // Vérifier les organisations si le référent a des organisations assignées
                $referentOrganizations = $currentUser->referentOrganizations()->pluck('organizations.id')->toArray();
                if (! empty($referentOrganizations) && ! empty($this->selectedOrganizations)) {
                    // Vérifier que toutes les organisations sélectionnées sont dans la liste du référent
                    $invalidOrgs = array_diff($this->selectedOrganizations, $referentOrganizations);
                    if (! empty($invalidOrgs)) {
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
                    $user->referentOrganizations()->sync($this->selectedReferentOrganizations ?? []);
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
                    'training_type' => $this->trainingType ?: null,
                ];

                $profile = $user->formateurProfile;
                if ($profile) {
                    $profile->update($profileData);
                    // Synchroniser les organisations
                    $profile->organizations()->sync($this->selectedOrganizations ?? []);
                } else {
                    $profileData['user_id'] = $user->id;
                    $profile = FormateurProfile::create($profileData);
                    // Synchroniser les organisations
                    $profile->organizations()->sync($this->selectedOrganizations ?? []);
                }
            }

            // Pour super_admin, pas de profil supplémentaire à créer/modifier
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
                    $user->referentOrganizations()->sync($this->selectedReferentOrganizations ?? []);
                }
            }

            // Créer le profil formateur si c'est un formateur
            if ($this->role === 'formateur') {
                $profile = FormateurProfile::create([
                    'user_id' => $user->id,
                    'phone_country_code' => $this->phoneCountryCode ?: null,
                    'phone_number' => $this->phoneNumber ?: null,
                    'country' => $this->country ?: null,
                    'training_type' => $this->trainingType ?: null,
                ]);
                // Synchroniser les organisations
                $profile->organizations()->sync($this->selectedOrganizations ?? []);
            }

            // Pour super_admin, pas de profil supplémentaire à créer
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

        // Empêcher la suppression d'un admin ou super_admin si l'utilisateur n'est pas super_admin
        if (($userRole === 'admin' || $userRole === 'super_admin') && ! $this->isSuperAdmin()) {
            session()->flash('error', 'Vous n\'avez pas la permission de supprimer cet utilisateur.');

            return;
        }

        $user->delete();
        session()->flash('message', 'Utilisateur supprimé avec succès.');
    }

    public function render()
    {
        $roleName = match ($this->activeTab) {
            'super_administrateurs' => 'super_admin',
            'administrateurs' => 'admin',
            default => 'formateur',
        };
        $role = Role::where('name', $roleName)->first();

        $query = User::query()
            ->whereHas('roles', function ($q) use ($role) {
                $q->where('roles.id', $role->id);
            })
            ->with(['roles', 'juryMembers.jury']);

        // Pour les formateurs, charger aussi les candidatures validées avec leurs badges et le profil
        if ($roleName === 'formateur') {
            $query->with([
                'formateurProfile',
                'candidatures' => function ($q) {
                    $q->where('status', 'validated')
                        ->with('badge');
                },
            ]);
        }

        // Appliquer les restrictions du référent pédagogique pour les formateurs
        if ($roleName === 'formateur' && Auth::check()) {
            /** @var User $currentUser */
            $currentUser = Auth::user();
            $query->forReferent($currentUser);
        }

        // Appliquer les filtres dynamiques
        if ($this->filterCountry) {
            if ($roleName === 'formateur') {
                $query->whereHas('formateurProfile', function ($q) {
                    $q->where('country', $this->filterCountry);
                });
            } elseif ($roleName === 'admin') {
                $query->where('country', $this->filterCountry);
            }
            // Pas de filtre pays pour super_admin
        }

        if ($this->filterOrganization) {
            if ($roleName === 'formateur') {
                $query->whereHas('formateurProfile.organizations', function ($q) {
                    $q->where('organizations.id', $this->filterOrganization);
                });
            } elseif ($roleName === 'admin') {
                $query->whereHas('referentOrganizations', function ($q) {
                    $q->where('organizations.id', $this->filterOrganization);
                });
            }
            // Pas de filtre organisation pour super_admin
        }

        if ($this->search) {
            // Parser les termes de recherche séparés par des virgules
            $searchTerms = $this->parseSearchTerms($this->search);

            // Pour chaque terme, on doit le trouver quelque part (AND logique entre les termes)
            foreach ($searchTerms as $term) {
                $query->where(function ($q) use ($roleName, $term) {
                    $q->where('first_name', 'like', '%'.$term.'%')
                        ->orWhere('name', 'like', '%'.$term.'%')
                        ->orWhere('email', 'like', '%'.$term.'%')
                        ->orWhere('country', 'like', '%'.$term.'%'); // Pour les administrateurs

                    // Recherche dans le profil formateur (pays, organisations, compétences, certifications)
                    if ($roleName === 'formateur') {
                        $q->orWhereHas('formateurProfile', function ($profileQ) use ($term) {
                            $profileQ->where('country', 'like', '%'.$term.'%')
                                ->orWhere('technical_profile', 'like', '%'.$term.'%')
                                ->orWhere('years_of_experience', 'like', '%'.$term.'%')
                                ->orWhere('training_type', 'like', '%'.$term.'%')
                                ->orWhereHas('organizations', function ($orgQ) use ($term) {
                                    $orgQ->where('name', 'like', '%'.$term.'%');
                                })
                                ->orWhereHas('certifications', function ($certQ) use ($term) {
                                    $certQ->where('name', 'like', '%'.$term.'%');
                                });
                        });
                    }

                    // Recherche dans les organisations du référent (pour les administrateurs)
                    if ($roleName === 'admin') {
                        $q->orWhereHas('referentOrganizations', function ($orgQ) use ($term) {
                            $orgQ->where('name', 'like', '%'.$term.'%');
                        });
                    }
                    // Pas de recherche spéciale pour super_admin (recherche basique sur nom, prénom, email)
                });
            }
        }

        $users = $query->latest()->paginate(10);

        // Pour les administrateurs, charger aussi les organisations du référent
        if ($roleName === 'admin') {
            $users->load('referentOrganizations');
        }
        // Pas de chargement spécial pour super_admin

        // Filtrer les organisations selon le référent pédagogique
        $organizations = Organization::orderBy('name')->get();
        if (Auth::check() && $this->activeTab === 'formateurs') {
            /** @var User $currentUser */
            $currentUser = Auth::user();
            if ($currentUser->isReferentPedagogique() && ! $this->isSuperAdmin()) {
                // Ne montrer que les organisations assignées au référent pédagogique
                $referentOrganizations = $currentUser->referentOrganizations()->pluck('organizations.id')->toArray();
                if (! empty($referentOrganizations)) {
                    $organizations = Organization::whereIn('id', $referentOrganizations)->orderBy('name')->get();
                } else {
                    // Si le référent n'a pas d'organisations assignées, ne pas afficher d'organisations
                    $organizations = collect([]);
                }
            }
        }

        return view('livewire.admin.user-management', [
            'users' => $users,
            'organizations' => $organizations,
            'countries' => CountriesData::getCountries(),
            'phoneCountryCodes' => CountriesData::getPhoneCountryCodes(),
        ]);
    }
}
