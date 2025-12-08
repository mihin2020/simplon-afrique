<div>
    @if (session()->has('message'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Header avec onglets et bouton d'ajout -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Gestion des Utilisateurs</h2>
            <button
                wire:click="openModal"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ajouter un {{ ($activeTab === 'formateurs' || !$this->isSuperAdmin()) ? 'Formateur' : 'Administrateur' }}
            </button>
        </div>

        <!-- Onglets -->
        <div class="flex gap-4 border-b border-gray-200">
            <button
                wire:click="switchTab('formateurs')"
                class="px-4 py-2 text-sm font-medium border-b-2 transition
                    {{ $activeTab === 'formateurs' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-red-600' }}"
            >
                Formateurs
            </button>
            @if($this->isSuperAdmin())
                <button
                    wire:click="switchTab('administrateurs')"
                    class="px-4 py-2 text-sm font-medium border-b-2 transition
                        {{ $activeTab === 'administrateurs' ? 'border-red-600 text-red-600' : 'border-transparent text-gray-500 hover:text-red-600' }}"
                >
                    Administrateurs
                </button>
            @endif
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher par nom ou email..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Prénom
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nom
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Rôle
                    </th>
                    @if($this->isSuperAdmin() && $activeTab === 'administrateurs')
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Participation Jury
                        </th>
                    @endif
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                @if(empty($user->first_name) && !empty($user->name))
                                    @php
                                        $nameParts = explode(' ', trim($user->name), 2);
                                        $displayFirstName = $nameParts[0] ?? '-';
                                    @endphp
                                    {{ $displayFirstName }}
                                @else
                                    {{ $user->first_name ?? '-' }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if(empty($user->first_name) && !empty($user->name))
                                    @php
                                        $nameParts = explode(' ', trim($user->name), 2);
                                        $displayLastName = isset($nameParts[1]) ? $nameParts[1] : '-';
                                    @endphp
                                    {{ $displayLastName }}
                                @else
                                    {{ $user->name ?? '-' }}
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $userRole = $user->roles->first()?->name ?? 'formateur';
                                $roleLabel = $userRole === 'formateur' ? 'Formateur' : ($userRole === 'admin' ? 'Administrateur' : ucfirst($userRole));
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                {{ $userRole === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $roleLabel }}
                            </span>
                        </td>
                        @if($this->isSuperAdmin() && $activeTab === 'administrateurs')
                            <td class="px-6 py-4">
                                @if($user->juryMembers->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($user->juryMembers as $membership)
                                            <div class="flex flex-col gap-1 p-2 bg-gray-50 rounded-lg border border-gray-200">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800" title="{{ $membership->jury->name ?? 'Jury inconnu' }}">
                                                    {{ Str::limit($membership->jury->name ?? 'Jury', 20) }}
                                                </span>
                                                <div class="flex flex-wrap gap-1">
                                                    @php
                                                        $roleLabels = [
                                                            'referent_pedagogique' => 'Réf. Péda.',
                                                            'directeur_pedagogique' => 'Dir. Péda.',
                                                            'formateur_senior' => 'Form. Senior',
                                                        ];
                                                    @endphp
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                                        {{ $roleLabels[$membership->role] ?? $membership->role }}
                                                    </span>
                                                    @if($membership->is_president)
                                                        <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                                            </svg>
                                                            Président
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Aucune participation</span>
                                @endif
                            </td>
                        @endif
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                @if($activeTab === 'formateurs')
                                    <button
                                        wire:click="openDetailsModal('{{ $user->id }}')"
                                        class="text-gray-600 hover:text-gray-900"
                                        title="Voir les détails"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                @endif
                                <button
                                    wire:click="openModal('{{ $user->id }}')"
                                    class="text-blue-600 hover:text-blue-900"
                                    title="Modifier"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button
                                    wire:click="delete('{{ $user->id }}')"
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cet utilisateur ?"
                                    class="text-red-600 hover:text-red-900"
                                    title="Supprimer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ ($this->isSuperAdmin() && $activeTab === 'administrateurs') ? 6 : 5 }}" class="px-6 py-12 text-center text-sm text-gray-500">
                            Aucun {{ $activeTab === 'formateurs' ? 'formateur' : 'administrateur' }} trouvé.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal d'ajout/modification -->
    @if($showModal)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-data="{ show: @entangle('showModal') }" 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex items-start justify-center min-h-screen px-4 pt-16 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div 
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                    x-on:click="show = false"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                ></div>

                <!-- Modal avec animation slide down -->
                <div 
                    class="inline-block align-top bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full relative"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <!-- Loader overlay -->
                    <div wire:loading wire:target="save" class="absolute inset-0 bg-white bg-opacity-90 flex items-center justify-center z-50 rounded-lg">
                        <div class="flex flex-col items-center gap-3">
                            <svg class="animate-spin h-8 w-8 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm text-gray-600 font-medium">Création en cours...</span>
                        </div>
                    </div>
                    
                    <form wire:submit.prevent="save">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                {{ $editingUserId ? 'Modifier l\'utilisateur' : 'Ajouter un ' . ($activeTab === 'formateurs' ? 'Formateur' : 'Administrateur') }}
                            </h3>

                            <div class="space-y-4">
                                @if($activeTab === 'formateurs' || $role === 'formateur')
                                    {{-- Champs spécifiques aux formateurs --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        {{-- Pays --}}
                                        <div>
                                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                                                Pays
                                            </label>
                                            <div x-data="{ 
                                                open: false, 
                                                selected: @entangle('country'),
                                                getDisplayText() {
                                                    if (!this.selected) return 'Sélectionner un pays';
                                                    const item = @js($countries).find(c => c.name === this.selected);
                                                    return item ? item.flag + ' ' + item.name : 'Sélectionner un pays';
                                                }
                                            }" class="relative">
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-left flex items-center justify-between"
                                                >
                                                    <span x-text="getDisplayText()"></span>
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                                <div
                                                    x-show="open"
                                                    @click.away="open = false"
                                                    x-transition
                                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                                >
                                                    <button
                                                        type="button"
                                                        @click="selected = ''; $wire.set('country', ''); open = false"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                                    >
                                                        Sélectionner un pays
                                                    </button>
                                                    @foreach($countries as $countryItem)
                                                        <button
                                                            type="button"
                                                            @click="selected = '{{ $countryItem['name'] }}'; $wire.set('country', '{{ $countryItem['name'] }}'); open = false"
                                                            :class="selected === '{{ $countryItem['name'] }}' ? 'bg-red-50' : ''"
                                                            class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2"
                                                        >
                                                            <span class="text-xl">{{ $countryItem['flag'] }}</span>
                                                            <span>{{ $countryItem['name'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @error('country') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Organisation --}}
                                        <div>
                                            <label for="organizationId" class="block text-sm font-medium text-gray-700 mb-1">
                                                Organisation
                                            </label>
                                            <select
                                                id="organizationId"
                                                wire:model="organizationId"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            >
                                                <option value="">Sélectionner une organisation</option>
                                                @foreach($organizations as $organization)
                                                    <option value="{{ $organization->id }}">{{ $organization->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('organizationId') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                @endif

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">
                                            Prénom
                                        </label>
                                        <input
                                            type="text"
                                            id="firstName"
                                            wire:model="firstName"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            required
                                        >
                                        @error('firstName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label for="lastName" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nom
                                        </label>
                                        <input
                                            type="text"
                                            id="lastName"
                                            wire:model="lastName"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            required
                                        >
                                        @error('lastName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                        Email
                                    </label>
                                    <input
                                        type="email"
                                        id="email"
                                        wire:model="email"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                        required
                                    >
                                    @error('email') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    @if(!$editingUserId)
                                        <p class="mt-1 text-xs text-gray-500">Un email d'activation sera envoyé à cette adresse.</p>
                                    @endif
                                </div>

                                @if($activeTab === 'formateurs' || $role === 'formateur')
                                    {{-- Téléphone et Type de formation pour les formateurs --}}
                                    <div class="grid grid-cols-2 gap-4">
                                        {{-- Code pays téléphone --}}
                                        <div>
                                            <label for="phoneCountryCode" class="block text-sm font-medium text-gray-700 mb-1">
                                                Code pays
                                            </label>
                                            <div x-data="{ 
                                                open: false, 
                                                selected: @entangle('phoneCountryCode'),
                                                getDisplayText() {
                                                    if (!this.selected) return 'Sélectionner un code';
                                                    const item = @js($phoneCountryCodes).find(c => c.code === this.selected);
                                                    return item ? item.flag + ' ' + item.code + ' - ' + item.country : 'Sélectionner un code';
                                                }
                                            }" class="relative">
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-left flex items-center justify-between"
                                                >
                                                    <span x-text="getDisplayText()"></span>
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                                <div
                                                    x-show="open"
                                                    @click.away="open = false"
                                                    x-transition
                                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"
                                                >
                                                    <button
                                                        type="button"
                                                        @click="selected = ''; $wire.set('phoneCountryCode', ''); open = false"
                                                        class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                                    >
                                                        Sélectionner un code
                                                    </button>
                                                    @foreach($phoneCountryCodes as $phoneCode)
                                                        <button
                                                            type="button"
                                                            @click="selected = '{{ $phoneCode['code'] }}'; $wire.set('phoneCountryCode', '{{ $phoneCode['code'] }}'); open = false"
                                                            :class="selected === '{{ $phoneCode['code'] }}' ? 'bg-red-50' : ''"
                                                            class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2"
                                                        >
                                                            <span class="text-xl">{{ $phoneCode['flag'] }}</span>
                                                            <span>{{ $phoneCode['code'] }} - {{ $phoneCode['country'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>
                                            @error('phoneCountryCode') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                        </div>

                                        {{-- Numéro de téléphone --}}
                                        <div>
                                            <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">
                                                Numéro de téléphone
                                            </label>
                                            <input
                                                type="text"
                                                id="phoneNumber"
                                                wire:model="phoneNumber"
                                                placeholder="06 12 34 56 78"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            >
                                            @error('phoneNumber') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    {{-- Type de formation --}}
                                    <div>
                                        <label for="trainingType" class="block text-sm font-medium text-gray-700 mb-1">
                                            Type de formation
                                        </label>
                                        <select
                                            id="trainingType"
                                            wire:model="trainingType"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                        >
                                            <option value="">Sélectionner un type</option>
                                            <option value="interne">Interne</option>
                                            <option value="externe">Externe</option>
                                        </select>
                                        @error('trainingType') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                    </div>
                                @endif

                                @if($editingUserId)
                                    <div>
                                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                            Rôle
                                        </label>
                                        @if($activeTab === 'administrateurs')
                                            {{-- Pour les administrateurs, le rôle est fixe et non modifiable --}}
                                            <input
                                                type="text"
                                                value="Administrateur"
                                                disabled
                                                class="w-full px-3 py-2 border border-gray-200 rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed"
                                            >
                                            <input type="hidden" wire:model="role" value="admin">
                                        @else
                                            {{-- Pour les formateurs, on peut changer le rôle si super admin --}}
                                            <select
                                                id="role"
                                                wire:model="role"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            >
                                                <option value="formateur">Formateur</option>
                                                @if($this->isSuperAdmin())
                                                    <option value="admin">Administrateur</option>
                                                @endif
                                            </select>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse relative">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="save">{{ $editingUserId ? 'Modifier' : 'Créer' }}</span>
                                <span wire:loading wire:target="save">Création...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="closeModal"
                                wire:loading.attr="disabled"
                                wire:target="save"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Annuler
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de détails du formateur -->
    @if($showDetailsModal && $viewingUser)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-data="{ show: @entangle('showDetailsModal') }" 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex items-start justify-center min-h-screen px-4 pt-16 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div 
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                    x-on:click="show = false"
                ></div>

                <!-- Modal -->
                <div 
                    class="inline-block align-top bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div class="bg-white px-6 pt-5 pb-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-900">
                                Détails du formateur
                            </h3>
                            <button
                                type="button"
                                wire:click="closeDetailsModal"
                                class="text-gray-400 hover:text-gray-600"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="space-y-6">
                            <!-- Informations personnelles -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Informations personnelles</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-xs text-gray-500">Prénom</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewingUser->first_name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Nom</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewingUser->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Email</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewingUser->email }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Téléphone</span>
                                        <p class="text-sm font-medium text-gray-900">
                                            @if($viewingUser->formateurProfile?->phone_number)
                                                {{ $viewingUser->formateurProfile->phone_country_code }} {{ $viewingUser->formateurProfile->phone_number }}
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Localisation et Organisation -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Localisation et Organisation</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-xs text-gray-500">Pays</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewingUser->formateurProfile?->country ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Organisation</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewingUser->formateurProfile?->organization?->name ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Type de formation</span>
                                        <p class="text-sm font-medium text-gray-900">
                                            @if($viewingUser->formateurProfile?->training_type)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $viewingUser->formateurProfile->training_type === 'interne' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                                    {{ ucfirst($viewingUser->formateurProfile->training_type) }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Profil professionnel -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Profil professionnel</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <span class="text-xs text-gray-500">Profil technique</span>
                                        <p class="text-sm font-medium text-gray-900">{{ $viewingUser->formateurProfile?->technical_profile ?? '-' }}</p>
                                    </div>
                                    <div>
                                        <span class="text-xs text-gray-500">Années d'expérience</span>
                                        <p class="text-sm font-medium text-gray-900">
                                            @php
                                                $experienceLabels = [
                                                    'moins_de_2_ans' => 'Moins de 2 ans',
                                                    'entre_2_et_5_ans' => 'Entre 2 et 5 ans',
                                                    'plus_de_5_ans' => 'Plus de 5 ans',
                                                ];
                                            @endphp
                                            {{ $experienceLabels[$viewingUser->formateurProfile?->years_of_experience] ?? $viewingUser->formateurProfile?->years_of_experience ?? '-' }}
                                        </p>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-xs text-gray-500">Portfolio</span>
                                        <p class="text-sm font-medium text-gray-900">
                                            @if($viewingUser->formateurProfile?->portfolio_url)
                                                <a href="{{ $viewingUser->formateurProfile->portfolio_url }}" target="_blank" class="text-blue-600 hover:underline">
                                                    {{ $viewingUser->formateurProfile->portfolio_url }}
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                    <div class="col-span-2">
                                        <span class="text-xs text-gray-500">CV</span>
                                        <p class="text-sm font-medium text-gray-900">
                                            @if($viewingUser->formateurProfile?->cv_path)
                                                @php
                                                    $cvFilename = basename($viewingUser->formateurProfile->cv_path);
                                                    // Format: nom_original__hash.extension
                                                    if (strpos($cvFilename, '__') !== false) {
                                                        $parts = explode('__', $cvFilename);
                                                        $extension = pathinfo($cvFilename, PATHINFO_EXTENSION);
                                                        $cvDisplayName = $parts[0] . '.' . $extension;
                                                    } else {
                                                        $cvDisplayName = $cvFilename;
                                                    }
                                                @endphp
                                                <a href="{{ Storage::url($viewingUser->formateurProfile->cv_path) }}" target="_blank" class="inline-flex items-center gap-2 text-blue-600 hover:underline">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                    {{ $cvDisplayName }}
                                                </a>
                                            @else
                                                <span class="text-gray-400">Non téléversé</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Certifications -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">Compétences et Certifications</h4>
                                @if($viewingUser->formateurProfile?->certifications && $viewingUser->formateurProfile->certifications->count() > 0)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($viewingUser->formateurProfile->certifications as $certification)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ $certification->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 italic">Aucune certification renseignée</p>
                                @endif
                            </div>

                            <!-- Date d'inscription -->
                            <div class="text-xs text-gray-500 text-center">
                                Inscrit le {{ $viewingUser->created_at->format('d/m/Y à H:i') }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 flex justify-end">
                        <button
                            type="button"
                            wire:click="closeDetailsModal"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
