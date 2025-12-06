<div>
    @if (! $grid)
        <div class="text-center py-12">
            <p class="text-gray-500">Chargement...</p>
        </div>
    @else
        @if (session()->has('success'))
            <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <!-- Grid Header -->
        <div class="mb-6 bg-white rounded-lg shadow p-6">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $grid->name }}</h2>
                    @if ($grid->description)
                        <p class="mt-2 text-gray-600">{{ $grid->description }}</p>
                    @endif
                    <div class="mt-4 flex items-center gap-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $grid->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ $grid->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <span class="text-sm text-gray-500">
                            {{ $grid->categories->count() }} catégorie{{ $grid->categories->count() > 1 ? 's' : '' }}
                        </span>
                    </div>
                </div>
                <a
                    href="{{ route('admin.evaluation-grids') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Retour
                </a>
            </div>
        </div>

        <!-- Add Category Button -->
        <div class="mb-6">
            <button
                wire:click="openCategoryModal()"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Ajouter une catégorie
            </button>
        </div>

        <!-- Categories -->
        <div class="space-y-4">
            @forelse ($grid->categories as $category)
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <!-- Category Header -->
                    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900">{{ $category->name }}</h3>
                                @if ($category->description)
                                    <p class="mt-1 text-sm text-gray-600">{{ $category->description }}</p>
                                @endif
                            </div>
                            <div class="flex items-center gap-2 ml-4">
                                <!-- Move Up -->
                                <button
                                    wire:click="moveCategoryUp('{{ $category->id }}')"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition"
                                    title="Déplacer vers le haut"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                </button>
                                <!-- Move Down -->
                                <button
                                    wire:click="moveCategoryDown('{{ $category->id }}')"
                                    class="p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition"
                                    title="Déplacer vers le bas"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <!-- Edit -->
                                <button
                                    wire:click="openCategoryModal('{{ $category->id }}')"
                                    class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition"
                                    title="Modifier"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <!-- Delete -->
                                <button
                                    wire:click="deleteCategory('{{ $category->id }}')"
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cette catégorie ? Tous ses critères seront également supprimés."
                                    class="p-2 text-red-600 hover:text-red-900 hover:bg-red-50 rounded-lg transition"
                                    title="Supprimer"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Criteria Section -->
                    <div class="p-6">
                        <!-- Add Criterion Button -->
                        <div class="mb-4">
                            <button
                                wire:click="openCriterionModal('{{ $category->id }}')"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Ajouter un critère
                            </button>
                        </div>

                        <!-- Criteria Table -->
                        @php
                            $totalWeight = $category->criteria->sum('weight');
                            $isValidWeight = abs($totalWeight - 100) < 0.01; // Tolérance pour les décimales
                        @endphp

                        @if ($category->criteria->count() > 0)
                            <!-- Weight Total Indicator -->
                            <div class="mb-4 flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-700">Total des poids :</span>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $isValidWeight ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ number_format($totalWeight, 2) }}%
                                </span>
                                @if (! $isValidWeight)
                                    <span class="text-sm text-red-600">
                                        (Le total doit être égal à 100%)
                                    </span>
                                @endif
                            </div>

                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Ordre
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Critère
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Description
                                            </th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Poids (%)
                                            </th>
                                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($category->criteria as $criterion)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $criterion->display_order }}
                                                </td>
                                                <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                    {{ $criterion->name }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-500">
                                                    {{ $criterion->description ?: '-' }}
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($criterion->weight, 2) }}%
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <!-- Move Up -->
                                                        <button
                                                            wire:click="moveCriterionUp('{{ $criterion->id }}')"
                                                            class="p-1 text-gray-600 hover:text-gray-900"
                                                            title="Déplacer vers le haut"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                            </svg>
                                                        </button>
                                                        <!-- Move Down -->
                                                        <button
                                                            wire:click="moveCriterionDown('{{ $criterion->id }}')"
                                                            class="p-1 text-gray-600 hover:text-gray-900"
                                                            title="Déplacer vers le bas"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                            </svg>
                                                        </button>
                                                        <!-- Edit -->
                                                        <button
                                                            wire:click="openCriterionModal('{{ $category->id }}', '{{ $criterion->id }}')"
                                                            class="p-1 text-blue-600 hover:text-blue-900"
                                                            title="Modifier"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                            </svg>
                                                        </button>
                                                        <!-- Delete -->
                                                        <button
                                                            wire:click="deleteCriterion('{{ $criterion->id }}')"
                                                            wire:confirm="Êtes-vous sûr de vouloir supprimer ce critère ?"
                                                            class="p-1 text-red-600 hover:text-red-900"
                                                            title="Supprimer"
                                                        >
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-sm text-gray-500 text-center py-4">Aucun critère défini pour cette catégorie.</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <p class="mt-4 text-sm text-gray-500">Aucune catégorie définie pour cette grille.</p>
                    <button
                        wire:click="openCategoryModal()"
                        class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajouter la première catégorie
                    </button>
                </div>
            @endforelse
        </div>

        <!-- Category Modal -->
        @if ($showCategoryModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showCategoryModal') }" x-show="show" x-transition>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" x-on:click="show = false"></div>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form wire:submit.prevent="saveCategory">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    {{ $categoryId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                                </h3>

                                <div class="space-y-4">
                                    <div>
                                        <label for="categoryName" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nom <span class="text-red-600">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="categoryName"
                                            wire:model="categoryName"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            placeholder="Ex: Entretien Technique"
                                        >
                                        @error('categoryName')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="categoryDescription" class="block text-sm font-medium text-gray-700 mb-1">
                                            Description
                                        </label>
                                        <textarea
                                            id="categoryDescription"
                                            wire:model="categoryDescription"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            placeholder="Description de la catégorie..."
                                        ></textarea>
                                        @error('categoryDescription')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    {{ $categoryId ? 'Modifier' : 'Créer' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="closeCategoryModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <!-- Criterion Modal -->
        @if ($showCriterionModal)
            <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showCriterionModal') }" x-show="show" x-transition>
                <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" x-on:click="show = false"></div>

                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <form wire:submit.prevent="saveCriterion">
                            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">
                                    {{ $criterionId ? 'Modifier le critère' : 'Nouveau critère' }}
                                </h3>

                                <div class="space-y-4">
                                    <div>
                                        <label for="criterionName" class="block text-sm font-medium text-gray-700 mb-1">
                                            Nom <span class="text-red-600">*</span>
                                        </label>
                                        <input
                                            type="text"
                                            id="criterionName"
                                            wire:model="criterionName"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            placeholder="Ex: Compétences techniques"
                                        >
                                        @error('criterionName')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="criterionDescription" class="block text-sm font-medium text-gray-700 mb-1">
                                            Description
                                        </label>
                                        <textarea
                                            id="criterionDescription"
                                            wire:model="criterionDescription"
                                            rows="3"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            placeholder="Description du critère..."
                                        ></textarea>
                                        @error('criterionDescription')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label for="criterionWeight" class="block text-sm font-medium text-gray-700 mb-1">
                                            Poids (%) <span class="text-red-600">*</span>
                                        </label>
                                        <input
                                            type="number"
                                            id="criterionWeight"
                                            wire:model="criterionWeight"
                                            step="0.01"
                                            min="0"
                                            max="100"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                            placeholder="Ex: 25.00"
                                        >
                                        @error('criterionWeight')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                                <button
                                    type="submit"
                                    class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    {{ $criterionId ? 'Modifier' : 'Créer' }}
                                </button>
                                <button
                                    type="button"
                                    wire:click="closeCriterionModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                                >
                                    Annuler
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>




