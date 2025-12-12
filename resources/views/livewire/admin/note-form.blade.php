<div 
    class="fixed inset-0 z-50 overflow-y-auto" 
    x-data="{ show: true }" 
    x-show="show"
    x-transition:enter="ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    wire:ignore.self
    @close-note-form.window="show = false; setTimeout(() => $dispatch('note-form-closed'), 200)"
>
    <div class="flex items-start justify-center min-h-screen px-4 pt-16 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div 
            class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" 
            x-on:click="$dispatch('close-note-form')"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
        ></div>

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
                        Ajouter une note pour {{ $admin->first_name }} {{ $admin->name }}
                    </h3>

                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Titre de la note <span class="text-red-500">*</span>
                            </label>
                            <input
                                type="text"
                                id="title"
                                wire:model="title"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                required
                            >
                            @error('title') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        @if($promotions->count() > 0)
                            {{-- Section pour afficher toutes les promotions affiliées (lecture seule) --}}
                            <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Promotions affiliées ({{ $promotions->count() }})
                                </label>
                                
                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                    @foreach($promotions as $promotion)
                                        <div 
                                            x-data="{ open: false }"
                                            class="bg-white border border-gray-300 rounded-lg p-3 hover:border-red-300 transition-colors"
                                        >
                                            <div class="flex items-center justify-between">
                                                <div class="flex-1">
                                                    <span class="text-sm font-medium text-gray-900">{{ $promotion->name }}</span>
                                                    @if($promotion->country)
                                                        <span class="text-xs text-gray-500 ml-2">• {{ $promotion->country }}</span>
                                                    @endif
                                                </div>
                                                <button
                                                    type="button"
                                                    @click="open = !open"
                                                    class="ml-2 p-1 text-gray-400 hover:text-gray-600 transition-colors"
                                                    title="Voir les détails"
                                                >
                                                    <svg 
                                                        class="w-5 h-5 transition-transform duration-200"
                                                        :class="{ 'rotate-180': open }"
                                                        fill="none" 
                                                        stroke="currentColor" 
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                            
                                            {{-- Dropdown avec les détails de la promotion --}}
                                            <div
                                                x-show="open"
                                                x-collapse
                                                class="mt-2 pt-2 border-t border-gray-200"
                                            >
                                                <div class="space-y-2 text-xs text-gray-600">
                                                    @if($promotion->start_date && $promotion->end_date)
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                            </svg>
                                                            <span>
                                                                {{ $promotion->start_date->format('d/m/Y') }} - {{ $promotion->end_date->format('d/m/Y') }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                    @if($promotion->number_of_learners)
                                                        <div class="flex items-center gap-2">
                                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                            </svg>
                                                            <span>{{ $promotion->number_of_learners }} apprenant(s)</span>
                                                        </div>
                                                    @endif
                                                    @if($promotion->organizations && $promotion->organizations->count() > 0)
                                                        <div class="flex items-start gap-2">
                                                            <svg class="w-4 h-4 text-gray-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                            </svg>
                                                            <div class="flex-1">
                                                                <span class="font-medium">Organisations:</span>
                                                                <div class="flex flex-wrap gap-1 mt-1">
                                                                    @foreach($promotion->organizations as $org)
                                                                        <span class="inline-block px-2 py-0.5 bg-gray-100 rounded text-xs">
                                                                            {{ $org->name }}
                                                                        </span>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            {{-- Si aucune promotion, informer l'utilisateur --}}
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <p class="text-sm text-yellow-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Cet utilisateur n'est associé à aucune promotion.
                                </p>
                            </div>
                        @endif

                        <div>
                            <label for="training_curriculum" class="block text-sm font-medium text-gray-700 mb-1">
                                Déroulé de la formation
                            </label>
                            <textarea
                                id="training_curriculum"
                                wire:model="training_curriculum"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Décrivez le déroulé de la formation..."
                            ></textarea>
                            @error('training_curriculum') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="difficulties" class="block text-sm font-medium text-gray-700 mb-1">
                                Difficultés rencontrées
                            </label>
                            <textarea
                                id="difficulties"
                                wire:model="difficulties"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Décrivez les difficultés rencontrées..."
                            ></textarea>
                            @error('difficulties') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="recommendations" class="block text-sm font-medium text-gray-700 mb-1">
                                Recommandations
                            </label>
                            <textarea
                                id="recommendations"
                                wire:model="recommendations"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Ajoutez vos recommandations..."
                            ></textarea>
                            @error('recommendations') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="other" class="block text-sm font-medium text-gray-700 mb-1">
                                Autre
                            </label>
                            <textarea
                                id="other"
                                wire:model="other"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                placeholder="Tout autre élément à noter..."
                            ></textarea>
                            @error('other') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
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
                        <span wire:loading.remove wire:target="save">Enregistrer</span>
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
</div>

