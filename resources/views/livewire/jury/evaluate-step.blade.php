<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Évaluation - {{ $step->label }}
                </h2>
                <p class="text-gray-600">
                    Candidature de {{ $candidature->user->name }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Indicateur de l'échelle de notation -->
                <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-blue-800">Échelle : 0 à {{ $noteScale }}</span>
                </div>
                @if($isReadOnly)
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-yellow-100 text-yellow-800">
                        Lecture seule - Le président a déjà validé cette étape
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if($isReadOnly)
        <!-- Mode lecture seule -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-gray-600 mb-4">Les évaluations ont été validées par le président. Vous pouvez consulter les notes ci-dessous.</p>
        </div>
    @else
        <!-- Guide de notation pour les membres du jury -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Guide de notation</h3>
                    <div class="text-sm text-blue-800 space-y-2">
                        <p>
                            <strong>Échelle de notation :</strong> Les notes vont de <span class="font-bold">0 à {{ $noteScale }}</span>.
                        </p>
                        <p>
                            <strong>Calcul de la moyenne :</strong> Vos notes seront automatiquement normalisées sur 20 pour le calcul final.
                            @if($noteScale != 20)
                                <br><span class="text-blue-600 text-xs">Exemple : une note de {{ round($noteScale * 0.8) }}/{{ $noteScale }} équivaut à 16/20</span>
                            @endif
                        </p>
                        <p>
                            <strong>Attribution du badge :</strong> Le badge sera attribué automatiquement selon la moyenne finale (sur 20) :
                        </p>
                        <div class="flex flex-wrap gap-3 mt-2">
                            @foreach($badgeThresholds as $badge)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-white rounded border border-blue-200 text-xs">
                                    {{ $badge['emoji'] }} <span class="font-medium">{{ str_replace('Label Formateur ', '', $badge['label']) }}</span> : {{ number_format($badge['min_score'], 0) }} à {{ number_format($badge['max_score'], 2) }}/20
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire d'évaluation -->
    <form wire:submit="submit">
        <div class="space-y-6">
            @foreach($categories as $category)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- En-tête de la catégorie -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ $category->name }}</h3>
                        @if($category->description)
                            <p class="text-sm text-red-100">{{ $category->description }}</p>
                        @endif
                    </div>

                    <!-- Tableau des critères -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">
                                        Critère
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                        Poids
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Note brute<br><span class="text-xs font-normal">(0-{{ $noteScale }})</span>
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Note pondérée
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Commentaire
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($category->criteria as $criterion)
                                    <tr class="hover:bg-gray-50 transition">
                                        <!-- Critère -->
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $criterion->name }}</div>
                                            @if($criterion->description)
                                                <div class="text-sm text-gray-500 mt-1">{{ $criterion->description }}</div>
                                            @endif
                                        </td>

                                        <!-- Poids -->
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                {{ number_format($criterion->weight, 1) }}%
                                            </span>
                                        </td>

                                        <!-- Note brute -->
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-center">
                                                <input
                                                    type="number"
                                                    id="score_{{ $criterion->id }}"
                                                    wire:model.live="scores.{{ $criterion->id }}"
                                                    min="0"
                                                    max="{{ $noteScale }}"
                                                    step="0.1"
                                                    @if($isReadOnly) disabled @endif
                                                    class="w-24 px-3 py-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @if($isReadOnly) bg-gray-100 cursor-not-allowed @endif font-medium"
                                                    placeholder="0.0"
                                                    required
                                                >
                                            </div>
                                        </td>

                                        <!-- Note pondérée (calculée automatiquement, normalisée sur 20) -->
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex items-center justify-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg min-w-[100px]">
                                                <span class="text-sm font-bold text-gray-900">
                                                    @if(isset($scores[$criterion->id]) && $scores[$criterion->id] !== null && $scores[$criterion->id] !== '')
                                                        @php
                                                            $normalizedScore = ($scores[$criterion->id] / $noteScale) * 20;
                                                            $weightedScore = $normalizedScore * ($criterion->weight / 100);
                                                        @endphp
                                                        {{ number_format($weightedScore, 3) }}
                                                    @else
                                                        0.000
                                                    @endif
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Commentaire -->
                                        <td class="px-6 py-4">
                                            <textarea
                                                id="comment_{{ $criterion->id }}"
                                                wire:model="comments.{{ $criterion->id }}"
                                                rows="2"
                                                @if($isReadOnly) disabled @endif
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @if($isReadOnly) bg-gray-100 cursor-not-allowed @endif resize-none"
                                                placeholder="Votre commentaire..."
                                            ></textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <!-- Résumé et actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Résumé des notes -->
                    <div class="flex-1">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-3">
                                    <!-- Moyenne finale sur 20 -->
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 mb-1">
                                            Moyenne finale (sur 20)
                                        </p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-bold text-green-700">{{ number_format($this->totalWeightedScore, 2) }}</p>
                                            <p class="text-sm text-green-600">/ 20</p>
                                        </div>
                                        <p class="text-xs text-blue-600 mt-1">
                                            = Moyenne des notes de chaque catégorie
                                        </p>
                                    </div>
                                    
                                    <!-- Détail par catégorie -->
                                    @php
                                        $notedCriteria = collect($scores)->filter(fn($s) => $s !== null && $s !== '');
                                        $categoriesCount = $categories->count();
                                    @endphp
                                    <div class="border-t border-blue-200 pt-3">
                                        <p class="text-xs text-blue-600">
                                            {{ $notedCriteria->count() }} critère(s) noté(s) sur {{ $categoriesCount }} catégorie(s)
                                        </p>
                                        <p class="text-xs text-blue-500 mt-1">
                                            Chaque catégorie = somme des notes pondérées (max 20)
                                        </p>
                                    </div>
                                </div>
                                <div class="hidden md:block">
                                    <div class="h-16 w-16 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    @if(!$isReadOnly)
                        <div class="flex-shrink-0">
                            <button
                                type="submit"
                                class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition font-semibold shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Soumettre l'évaluation
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>

            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Évaluation - {{ $step->label }}
                </h2>
                <p class="text-gray-600">
                    Candidature de {{ $candidature->user->name }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Indicateur de l'échelle de notation -->
                <div class="flex items-center gap-2 px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium text-blue-800">Échelle : 0 à {{ $noteScale }}</span>
                </div>
                @if($isReadOnly)
                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium bg-yellow-100 text-yellow-800">
                        Lecture seule - Le président a déjà validé cette étape
                    </span>
                @endif
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    @if($isReadOnly)
        <!-- Mode lecture seule -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <p class="text-gray-600 mb-4">Les évaluations ont été validées par le président. Vous pouvez consulter les notes ci-dessous.</p>
        </div>
    @else
        <!-- Guide de notation pour les membres du jury -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-5">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900 mb-2">Guide de notation</h3>
                    <div class="text-sm text-blue-800 space-y-2">
                        <p>
                            <strong>Échelle de notation :</strong> Les notes vont de <span class="font-bold">0 à {{ $noteScale }}</span>.
                        </p>
                        <p>
                            <strong>Calcul de la moyenne :</strong> Vos notes seront automatiquement normalisées sur 20 pour le calcul final.
                            @if($noteScale != 20)
                                <br><span class="text-blue-600 text-xs">Exemple : une note de {{ round($noteScale * 0.8) }}/{{ $noteScale }} équivaut à 16/20</span>
                            @endif
                        </p>
                        <p>
                            <strong>Attribution du badge :</strong> Le badge sera attribué automatiquement selon la moyenne finale (sur 20) :
                        </p>
                        <div class="flex flex-wrap gap-3 mt-2">
                            @foreach($badgeThresholds as $badge)
                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-white rounded border border-blue-200 text-xs">
                                    {{ $badge['emoji'] }} <span class="font-medium">{{ str_replace('Label Formateur ', '', $badge['label']) }}</span> : {{ number_format($badge['min_score'], 0) }} à {{ number_format($badge['max_score'], 2) }}/20
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Formulaire d'évaluation -->
    <form wire:submit="submit">
        <div class="space-y-6">
            @foreach($categories as $category)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <!-- En-tête de la catégorie -->
                    <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                        <h3 class="text-lg font-semibold text-white mb-1">{{ $category->name }}</h3>
                        @if($category->description)
                            <p class="text-sm text-red-100">{{ $category->description }}</p>
                        @endif
                    </div>

                    <!-- Tableau des critères -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-1/3">
                                        Critère
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                        Poids
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Note brute<br><span class="text-xs font-normal">(0-{{ $noteScale }})</span>
                                    </th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                        Note pondérée
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Commentaire
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($category->criteria as $criterion)
                                    <tr class="hover:bg-gray-50 transition">
                                        <!-- Critère -->
                                        <td class="px-6 py-4">
                                            <div class="font-medium text-gray-900">{{ $criterion->name }}</div>
                                            @if($criterion->description)
                                                <div class="text-sm text-gray-500 mt-1">{{ $criterion->description }}</div>
                                            @endif
                                        </td>

                                        <!-- Poids -->
                                        <td class="px-4 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                                {{ number_format($criterion->weight, 1) }}%
                                            </span>
                                        </td>

                                        <!-- Note brute -->
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-center">
                                                <input
                                                    type="number"
                                                    id="score_{{ $criterion->id }}"
                                                    wire:model.live="scores.{{ $criterion->id }}"
                                                    min="0"
                                                    max="{{ $noteScale }}"
                                                    step="0.1"
                                                    @if($isReadOnly) disabled @endif
                                                    class="w-24 px-3 py-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @if($isReadOnly) bg-gray-100 cursor-not-allowed @endif font-medium"
                                                    placeholder="0.0"
                                                    required
                                                >
                                            </div>
                                        </td>

                                        <!-- Note pondérée (calculée automatiquement, normalisée sur 20) -->
                                        <td class="px-4 py-4 text-center">
                                            <div class="inline-flex items-center justify-center px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg min-w-[100px]">
                                                <span class="text-sm font-bold text-gray-900">
                                                    @if(isset($scores[$criterion->id]) && $scores[$criterion->id] !== null && $scores[$criterion->id] !== '')
                                                        @php
                                                            $normalizedScore = ($scores[$criterion->id] / $noteScale) * 20;
                                                            $weightedScore = $normalizedScore * ($criterion->weight / 100);
                                                        @endphp
                                                        {{ number_format($weightedScore, 3) }}
                                                    @else
                                                        0.000
                                                    @endif
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Commentaire -->
                                        <td class="px-6 py-4">
                                            <textarea
                                                id="comment_{{ $criterion->id }}"
                                                wire:model="comments.{{ $criterion->id }}"
                                                rows="2"
                                                @if($isReadOnly) disabled @endif
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @if($isReadOnly) bg-gray-100 cursor-not-allowed @endif resize-none"
                                                placeholder="Votre commentaire..."
                                            ></textarea>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach

            <!-- Résumé et actions -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <!-- Résumé des notes -->
                    <div class="flex-1">
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-3">
                                    <!-- Moyenne finale sur 20 -->
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 mb-1">
                                            Moyenne finale (sur 20)
                                        </p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-bold text-green-700">{{ number_format($this->totalWeightedScore, 2) }}</p>
                                            <p class="text-sm text-green-600">/ 20</p>
                                        </div>
                                        <p class="text-xs text-blue-600 mt-1">
                                            = Moyenne des notes de chaque catégorie
                                        </p>
                                    </div>
                                    
                                    <!-- Détail par catégorie -->
                                    @php
                                        $notedCriteria = collect($scores)->filter(fn($s) => $s !== null && $s !== '');
                                        $categoriesCount = $categories->count();
                                    @endphp
                                    <div class="border-t border-blue-200 pt-3">
                                        <p class="text-xs text-blue-600">
                                            {{ $notedCriteria->count() }} critère(s) noté(s) sur {{ $categoriesCount }} catégorie(s)
                                        </p>
                                        <p class="text-xs text-blue-500 mt-1">
                                            Chaque catégorie = somme des notes pondérées (max 20)
                                        </p>
                                    </div>
                                </div>
                                <div class="hidden md:block">
                                    <div class="h-16 w-16 rounded-full bg-green-500 flex items-center justify-center">
                                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton de soumission -->
                    @if(!$isReadOnly)
                        <div class="flex-shrink-0">
                            <button
                                type="submit"
                                class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition font-semibold shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Soumettre l'évaluation
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>
</div>
