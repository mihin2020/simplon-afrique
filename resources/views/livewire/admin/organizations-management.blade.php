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

    <!-- Header avec boutons d'ajout -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900">Gestion des Organisations</h2>
            <button
                wire:click="openBulkModal"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ajouter des organisations
            </button>
        </div>
    </div>

    <!-- Barre de recherche -->
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
        <div class="flex items-center gap-4">
            <div class="flex-1 relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Rechercher une organisation..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Tableau des organisations -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Pays
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Organisations
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nombre total de formateurs
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($groupedOrganizations as $country => $organizations)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap align-top">
                                @php
                                    $countryData = collect($countries)->firstWhere('name', $country);
                                    $countryFlag = $countryData['flag'] ?? '🌍';
                                @endphp
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">{{ $countryFlag }}</span>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-900">{{ $country ?: 'Non défini' }}</div>
                                        <div class="text-xs text-gray-500 mt-0.5">{{ $organizations->count() }} organisation(s)</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @foreach($organizations as $organization)
                                        <div class="group relative inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-red-50 text-red-800 border border-red-200 hover:bg-red-100 transition">
                                            <span>{{ $organization->name }}</span>
                                            <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-xs font-semibold bg-red-200 text-red-900">
                                                {{ $organization->formateurProfiles->count() }}
                                            </span>
                                            <button
                                                wire:click="openEditModal('{{ $organization->id }}')"
                                                class="ml-1 text-red-600 hover:text-red-800 opacity-0 group-hover:opacity-100 transition-opacity"
                                                title="Modifier"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </button>
                                            <button
                                                wire:click="delete('{{ $organization->id }}')"
                                                wire:confirm="Êtes-vous sûr de vouloir supprimer cette organisation ?"
                                                class="ml-1 text-red-600 hover:text-red-800 opacity-0 group-hover:opacity-100 transition-opacity"
                                                title="Supprimer"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap align-top">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $organizations->sum(fn($org) => $org->formateurProfiles->count()) }}
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-sm text-gray-500">
                                Aucune organisation trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal d'ajout multiple d'organisations -->
    @if($showBulkModal)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-data="{ show: @entangle('showBulkModal') }" 
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex items-start justify-center min-h-screen px-4 pt-16 pb-20 text-center sm:block sm:p-0">
                <div 
                    class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
                    x-on:click="show = false"
                ></div>

                <!-- Modal -->
                <div 
                    class="inline-block align-top bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full relative"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                >
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            Ajouter plusieurs organisations
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Pays <span class="text-gray-500 text-xs">(sera appliqué à toutes les organisations)</span>
                                </label>
                                <div x-data="{ 
                                    open: false, 
                                    selected: @entangle('bulkCountry'),
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
                                            @click="selected = ''; $wire.set('bulkCountry', ''); open = false"
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                        >
                                            Aucun pays
                                        </button>
                                        @foreach($countries as $countryItem)
                                            <button
                                                type="button"
                                                @click="selected = @js($countryItem['name']); $wire.set('bulkCountry', @js($countryItem['name'])); open = false"
                                                :class="selected === @js($countryItem['name']) ? 'bg-red-50' : ''"
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2"
                                            >
                                                <span class="text-xl">{{ $countryItem['flag'] }}</span>
                                                <span>{{ $countryItem['name'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nom de l'organisation
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="newOrganizationName"
                                        wire:keydown.enter.prevent="addOrganizationToBulk"
                                        placeholder="Ex: AUF, LuxDev..."
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    >
                                    @error('newOrganizationName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="flex items-end">
                                    <button
                                        type="button"
                                        wire:click="addOrganizationToBulk"
                                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition whitespace-nowrap"
                                    >
                                        Ajouter
                                    </button>
                                </div>
                            </div>

                            @if(count($organizationsToAdd) > 0)
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="text-sm font-medium text-gray-700">
                                            Organisations à créer ({{ count($organizationsToAdd) }})
                                        </p>
                                        @if($bulkCountry)
                                            @php
                                                $countryData = collect($countries)->firstWhere('name', $bulkCountry);
                                                $countryFlag = $countryData['flag'] ?? '🌍';
                                            @endphp
                                            <span class="text-xs text-gray-500">
                                                Pays : <span class="font-medium">{{ $countryFlag }} {{ $bulkCountry }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($organizationsToAdd as $index => $org)
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-medium bg-red-100 text-red-800 border border-red-200">
                                                @if($org['country'])
                                                    @php
                                                        $countryData = collect($countries)->firstWhere('name', $org['country']);
                                                        $countryFlag = $countryData['flag'] ?? '🌍';
                                                    @endphp
                                                    <span class="text-base">{{ $countryFlag }}</span>
                                                @endif
                                                <span>{{ $org['name'] }}</span>
                                                <button
                                                    type="button"
                                                    wire:click="removeOrganizationFromBulk({{ $index }})"
                                                    class="ml-1 text-red-600 hover:text-red-800"
                                                    title="Retirer"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse relative">
                        <button
                            type="button"
                            wire:click="saveBulk"
                            wire:loading.attr="disabled"
                            wire:target="saveBulk"
                            class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <svg wire:loading wire:target="saveBulk" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span wire:loading.remove wire:target="saveBulk">Créer {{ count($organizationsToAdd) > 0 ? count($organizationsToAdd) : '' }} organisation(s)</span>
                            <span wire:loading wire:target="saveBulk">Création...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="closeBulkModal"
                            wire:loading.attr="disabled"
                            wire:target="saveBulk"
                            class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Annuler
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Modal de modification -->
    @if($showEditModal)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto" 
            x-data="{ show: @entangle('showEditModal') }" 
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
                    class="inline-block align-top bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                >
                    <form wire:submit.prevent="updateOrganization">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                Modifier l'organisation
                            </h3>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Nom de l'organisation
                                    </label>
                                    <input
                                        type="text"
                                        wire:model="editName"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                        required
                                    >
                                    @error('editName') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">
                                        Pays
                                    </label>
                                    <div x-data="{ 
                                        open: false, 
                                        selected: @entangle('editCountry'),
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
                                                @click="selected = ''; $wire.set('editCountry', ''); open = false"
                                                class="w-full text-left px-4 py-2 hover:bg-gray-100"
                                            >
                                                Aucun pays
                                            </button>
                                            @foreach($countries as $countryItem)
                                                <button
                                                    type="button"
                                                    @click="selected = @js($countryItem['name']); $wire.set('editCountry', @js($countryItem['name'])); open = false"
                                                    :class="selected === @js($countryItem['name']) ? 'bg-red-50' : ''"
                                                    class="w-full text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2"
                                                >
                                                    <span class="text-xl">{{ $countryItem['flag'] }}</span>
                                                    <span>{{ $countryItem['name'] }}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                    @error('editCountry') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="updateOrganization"
                                class="w-full inline-flex justify-center items-center gap-2 rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 sm:ml-3 sm:w-auto sm:text-sm transition disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <svg wire:loading wire:target="updateOrganization" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="updateOrganization">Modifier</span>
                                <span wire:loading wire:target="updateOrganization">Modification...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="closeEditModal"
                                wire:loading.attr="disabled"
                                wire:target="updateOrganization"
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
</div>
