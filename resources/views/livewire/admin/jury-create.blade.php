<div>
    <div class="mb-6">
        <a
            href="{{ route('admin.juries') }}"
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste
        </a>
        <h2 class="text-2xl font-semibold text-gray-900">Créer un nouveau jury</h2>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6">
        <form wire:submit.prevent="submit" class="space-y-6">
            <!-- Nom du jury -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                    Nom du jury <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    placeholder="Ex: Jury Labellisation Janvier 2024"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    required
                >
                @error('name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Donnez un nom clair et identifiable à ce jury. Vous pourrez ensuite ajouter des membres et assigner ce jury à des candidatures.
                </p>
            </div>

            <!-- Boutons -->
            <div class="flex justify-end gap-4">
                <a
                    href="{{ route('admin.juries') }}"
                    class="inline-flex items-center px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                >
                    Annuler
                </a>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="submit">Créer le jury</span>
                    <span wire:loading wire:target="submit">Création...</span>
                </button>
            </div>
        </form>
    </div>
</div>
