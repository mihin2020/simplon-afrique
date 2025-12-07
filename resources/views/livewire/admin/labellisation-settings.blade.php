<div>
    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="space-y-6">
        <!-- Section Échelle de notation -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Échelle de notation</h2>
                <div class="flex items-center gap-2 px-4 py-2 bg-green-100 border border-green-300 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold text-green-800">Échelle actuelle : 0 à {{ $noteScale }}</span>
                </div>
            </div>
            <p class="text-gray-600 mb-6">
                Définissez l'échelle maximale des notes que les membres du jury peuvent attribuer lors des évaluations.
                Les notes seront ensuite normalisées sur 20 pour l'attribution des badges.
            </p>

            <div class="flex items-end gap-4">
                <div class="flex-1 max-w-xs">
                    <label for="noteScale" class="block text-sm font-medium text-gray-700 mb-2">
                        Modifier l'échelle
                    </label>
                    <select
                        id="noteScale"
                        wire:model="noteScale"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    >
                        <option value="5" @selected($noteScale == 5)>0 à 5</option>
                        <option value="10" @selected($noteScale == 10)>0 à 10</option>
                        <option value="20" @selected($noteScale == 20)>0 à 20</option>
                        <option value="100" @selected($noteScale == 100)>0 à 100</option>
                    </select>
                    @error('noteScale') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <button
                    wire:click="saveNoteScale"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    Enregistrer
                </button>
            </div>

            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Exemple :</strong> Si l'échelle est de 0 à {{ $noteScale }}, une note de {{ $noteScale == 5 ? '4' : ($noteScale == 10 ? '8' : ($noteScale == 100 ? '80' : '16')) }}/{{ $noteScale }} sera convertie en {{ $noteScale == 5 ? '16' : ($noteScale == 10 ? '16' : ($noteScale == 100 ? '16' : '16')) }}/20 pour le calcul de la moyenne finale.
                </p>
            </div>
        </div>

        <!-- Section Seuils des badges -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Seuils d'attribution des badges</h2>
            <p class="text-gray-600 mb-6">
                Définissez les plages de moyennes (sur 20) pour l'attribution automatique de chaque badge.
            </p>

            <div class="space-y-4">
                @foreach($badges as $index => $badge)
                    <div class="flex items-center gap-4 p-4 border border-gray-200 rounded-lg" wire:key="badge-{{ $badge['id'] }}">
                        <div class="flex-shrink-0 w-8 text-2xl">
                            @if($badge['name'] === 'junior')
                                🥉
                            @elseif($badge['name'] === 'intermediaire')
                                🥈
                            @elseif($badge['name'] === 'senior')
                                🥇
                            @else
                                🏅
                            @endif
                        </div>
                        <div class="flex-1">
                            <h3 class="font-medium text-gray-900">{{ $badge['label'] }}</h3>
                        </div>
                        <div class="flex items-center gap-2">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Min</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="badges.{{ $index }}.min_score"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-center"
                                >
                            </div>
                            <span class="text-gray-400 mt-5">à</span>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Max</label>
                                <input
                                    type="number"
                                    step="0.01"
                                    wire:model="badges.{{ $index }}.max_score"
                                    class="w-20 px-2 py-1 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-center"
                                >
                            </div>
                        </div>
                        @error("badges.{$index}.min_score") 
                            <span class="text-sm text-red-600">{{ $message }}</span> 
                        @enderror
                        @error("badges.{$index}.max_score") 
                            <span class="text-sm text-red-600">{{ $message }}</span> 
                        @enderror
                    </div>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button
                    wire:click="saveBadgeThresholds"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    Enregistrer les seuils
                </button>
            </div>

            <div class="mt-4 p-4 bg-amber-50 rounded-lg">
                <p class="text-sm text-amber-800">
                    <strong>Note :</strong> Les seuils sont basés sur la moyenne normalisée sur 20. 
                    Si la moyenne d'un candidat est inférieure au seuil minimum du badge Junior, la candidature sera rejetée.
                </p>
            </div>
        </div>
    </div>
</div>
