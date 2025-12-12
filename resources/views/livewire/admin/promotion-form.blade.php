<div class="flex items-start justify-center min-h-screen px-4 pt-16 pb-20 text-center sm:block sm:p-0">
    <!-- Overlay -->
    <div 
        class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
        x-on:click="$dispatch('close-promotion-form')"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
    ></div>

    <!-- Modal -->
    <div 
        class="inline-block align-top bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full relative z-10"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-on:click.stop
    >

        <!-- Modal -->
        <div 
            class="inline-block align-top bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full relative"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        >
            <form wire:submit.prevent="save">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">
                        {{ $promotionId ? 'Modifier la promotion' : 'Créer une promotion' }}
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nom de la promotion <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="name"
                                wire:model="name"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                required
                            >
                            @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="startDate" class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de début <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="startDate"
                                    wire:model="startDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    required
                                >
                                @error('startDate') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="endDate" class="block text-sm font-medium text-gray-700 mb-1">
                                    Date de fin <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="date"
                                    id="endDate"
                                    wire:model="endDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    required
                                >
                                @error('endDate') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                                Pays <span class="text-red-500">*</span>
                            </label>
                            <select
                                id="country"
                                wire:model.live="country"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                required
                            >
                                <option value="">Sélectionner un pays</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country['name'] }}">{{ $country['flag'] }} {{ $country['name'] }}</option>
                                @endforeach
                            </select>
                            @error('country') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Organisations
                            </label>
                            
                            <!-- Affichage des organisations sélectionnées -->
                            @if(count($selectedOrganizations) > 0)
                                <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedOrganizationsData as $org)
                                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-red-100 text-red-800 text-sm font-medium rounded-lg border border-red-200">
                                                {{ $org->name }}
                                                <button
                                                    type="button"
                                                    wire:click="removeOrganization('{{ $org->id }}')"
                                                    class="hover:text-red-900 transition-colors"
                                                    title="Retirer"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                    <p class="mt-2 text-xs text-gray-500">
                                        {{ count($selectedOrganizations) }} organisation(s) sélectionnée(s)
                                    </p>
                                </div>
                            @endif

                            <!-- Dropdown pour sélectionner -->
                            <div 
                                x-data="{ open: false, search: '' }"
                                class="relative"
                                @click.away="open = false"
                            >
                                <button
                                    type="button"
                                    @click="open = !open"
                                    class="w-full px-3 py-2 text-left border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white min-h-[42px] flex items-center justify-between hover:border-gray-400 transition-colors"
                                >
                                    <span class="text-gray-700">
                                        @if(count($selectedOrganizations) > 0)
                                            Ajouter d'autres organisations
                                        @else
                                            Sélectionner des organisations
                                        @endif
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Dropdown -->
                                <div
                                    x-show="open"
                                    x-transition:enter="transition ease-out duration-100"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    x-transition:leave="transition ease-in duration-75"
                                    x-transition:leave-start="opacity-100 scale-100"
                                    x-transition:leave-end="opacity-0 scale-95"
                                    class="absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-hidden"
                                    style="display: none;"
                                >
                                    <!-- Recherche -->
                                    <div class="p-2 border-b border-gray-200">
                                        <input
                                            type="text"
                                            x-model="search"
                                            placeholder="Rechercher une organisation..."
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            @click.stop
                                        >
                                    </div>
                                    <!-- Liste des organisations -->
                                    <div class="overflow-y-auto max-h-48">
                                        @foreach($organizations as $organization)
                                            @php
                                                $isSelected = in_array($organization->id, $selectedOrganizations);
                                            @endphp
                                            <label 
                                                class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer {{ $isSelected ? 'bg-red-50 border-l-2 border-red-500' : '' }}"
                                                x-show="!search || '{{ strtolower($organization->name) }}'.includes(search.toLowerCase())"
                                                @click.stop
                                            >
                                                <input
                                                    type="checkbox"
                                                    value="{{ $organization->id }}"
                                                    wire:model.live="selectedOrganizations"
                                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                    @click.stop
                                                >
                                                <span class="ml-2 text-sm text-gray-700">{{ $organization->name }}</span>
                                                @if($organization->country)
                                                    <span class="ml-auto text-xs text-gray-500">{{ $organization->country }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @error('selectedOrganizations') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                            @error('selectedOrganizations.*') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="numberOfLearners" class="block text-sm font-medium text-gray-700 mb-1">
                                    Nombre d'apprenants <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="number"
                                    id="numberOfLearners"
                                    wire:model="numberOfLearners"
                                    min="1"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                    required
                                >
                                @error('numberOfLearners') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Formateur associé <span class="text-red-500">*</span>
                                </label>
                                
                                <!-- Affichage des formateurs sélectionnés -->
                                @if(count($selectedFormateurs) > 0)
                                    <div class="mb-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($selectedFormateursData as $formateur)
                                                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-100 text-blue-800 text-sm font-medium rounded-lg border border-blue-200">
                                                    {{ $formateur->first_name }} {{ $formateur->name }}
                                                    <button
                                                        type="button"
                                                        wire:click="removeFormateur('{{ $formateur->id }}')"
                                                        class="hover:text-blue-900 transition-colors"
                                                        title="Retirer"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                        </svg>
                                                    </button>
                                                </span>
                                            @endforeach
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            {{ count($selectedFormateurs) }} formateur(s) sélectionné(s)
                                        </p>
                                    </div>
                                @endif

                                <!-- Dropdown pour sélectionner -->
                                <div 
                                    x-data="{ open: false, search: '' }"
                                    class="relative"
                                    @click.away="open = false"
                                >
                                    <button
                                        type="button"
                                        @click="open = !open"
                                        class="w-full px-3 py-2 text-left border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white min-h-[42px] flex items-center justify-between hover:border-gray-400 transition-colors"
                                    >
                                        <span class="text-gray-700">
                                            @if(count($selectedFormateurs) > 0)
                                                Ajouter d'autres formateurs
                                            @else
                                                Sélectionner des formateurs
                                            @endif
                                        </span>
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>

                                    <div
                                        x-show="open"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute z-[100] w-full bottom-full mb-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-[200px] overflow-hidden"
                                        style="display: none;"
                                    >
                                        <div class="p-2 border-b border-gray-200">
                                            <input
                                                type="text"
                                                x-model="search"
                                                placeholder="Rechercher un formateur..."
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                                @click.stop
                                            >
                                        </div>
                                        <div class="overflow-y-auto max-h-[160px]">
                                            @foreach($formateurs as $formateur)
                                                @php
                                                    $isSelected = in_array($formateur->id, $selectedFormateurs);
                                                @endphp
                                                <label 
                                                    class="flex items-center px-3 py-2 hover:bg-gray-50 cursor-pointer {{ $isSelected ? 'bg-blue-50 border-l-2 border-blue-500' : '' }}"
                                                    x-show="!search || '{{ strtolower($formateur->first_name . ' ' . $formateur->name . ' ' . $formateur->email) }}'.includes(search.toLowerCase())"
                                                    @click.stop
                                                >
                                                    <input
                                                        type="checkbox"
                                                        value="{{ $formateur->id }}"
                                                        wire:model.live="selectedFormateurs"
                                                        class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                                        @click.stop
                                                    >
                                                    <div class="ml-2 flex-1">
                                                        <span class="text-sm text-gray-900 font-medium">{{ $formateur->first_name }} {{ $formateur->name }}</span>
                                                        <span class="text-xs text-gray-500 block">{{ $formateur->email }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @error('selectedFormateurs') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                                @error('selectedFormateurs.*') <span class="mt-1 text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
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
                        <span wire:loading.remove wire:target="save">
                            @if($promotionId)
                                Modifier la promotion
                            @else
                                Créer la promotion
                            @endif
                        </span>
                        <span wire:loading wire:target="save">Enregistrement...</span>
                    </button>
                    <button
                        type="button"
                        wire:click="cancel"
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

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('promotion-saved', () => {
            // Le modal se fermera automatiquement via l'événement close-promotion-form
        });
    });
</script>
