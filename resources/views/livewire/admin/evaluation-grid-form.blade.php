<div>
    @if (session()->has('success'))
        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h2 class="text-xl font-bold text-gray-900">
                {{ $gridId ? 'Modifier la grille d\'évaluation' : 'Nouvelle grille d\'évaluation' }}
            </h2>
        </div>

        <div class="space-y-6">
            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                    Nom <span class="text-red-600">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    wire:model="name"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Ex: Grille d'évaluation formateur"
                >
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                    Description
                </label>
                <textarea
                    id="description"
                    wire:model="description"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Description de la grille d'évaluation..."
                ></textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Active Status -->
            <div>
                <label class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        wire:model="isActive"
                        class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500"
                    >
                    <span class="text-sm font-medium text-gray-700">Activer cette grille d'évaluation</span>
                </label>
                <p class="mt-1 text-sm text-gray-500">Seules les grilles actives peuvent être utilisées dans les évaluations.</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex items-center justify-end gap-4">
            <a
                href="{{ route('admin.evaluation-grids') }}"
                class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
            >
                Annuler
            </a>
            <button
                type="submit"
                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
            >
                {{ $gridId ? 'Modifier' : 'Créer' }}
            </button>
        </div>
    </form>
</div>




