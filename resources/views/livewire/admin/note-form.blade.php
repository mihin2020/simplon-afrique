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
                            <div>
                                <label for="promotionId" class="block text-sm font-medium text-gray-700 mb-1">
                                    Promotion associée (optionnel)
                                </label>
                                <select
                                    id="promotionId"
                                    wire:model="promotionId"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                >
                                    <option value="">Aucune promotion</option>
                                    @foreach($promotions as $promotion)
                                        <option value="{{ $promotion->id }}">{{ $promotion->name }}</option>
                                    @endforeach
                                </select>
                                @error('promotionId') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>
                        @endif

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

