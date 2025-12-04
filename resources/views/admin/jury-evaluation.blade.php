@extends('layouts.app')

@section('title', 'Évaluation - Simplon Africa')

@section('page-title', 'Évaluation - ' . $candidature->user->name)

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <div class="space-y-6">
        <!-- En-tête -->
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-semibold text-gray-900">Évaluation : {{ $candidature->user->name }}</h2>
                @if(in_array($candidature->id, $evaluatedCandidatureIds))
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Déjà évalué
                    </span>
                @endif
            </div>
            <p class="text-gray-600 mt-1">Jury : {{ $jury->name }}</p>
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

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulaire d'évaluation -->
        <form action="{{ route('admin.jury.evaluation.save', ['jury' => $jury->id, 'candidature' => $candidature->id]) }}" method="POST">
            @csrf

            <div class="space-y-6">
                @foreach($categories as $category)
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
                        <!-- En-tête de la catégorie -->
                        <div class="bg-gradient-to-r from-red-500 to-red-600 px-6 py-4">
                            <h5 class="text-lg font-semibold text-white mb-1">
                                {{ $category->name }}
                            </h5>
                            @if($category->description)
                                <p class="text-sm text-red-100 mt-1">{{ $category->description }}</p>
                            @endif
                        </div>

                        <!-- Tableau des critères -->
                        @if($category->criteria->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-2/5">
                                                Critère
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                                                Poids
                                            </th>
                                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">
                                                Note brute<br><span class="text-xs font-normal">(0-20)</span>
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
                                        @foreach($category->criteria->sortBy('display_order') as $criterion)
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
                                                            name="scores[{{ $criterion->id }}]"
                                                            id="score_{{ $criterion->id }}"
                                                            value="{{ $scores[$criterion->id] ?? '' }}"
                                                            min="0"
                                                            max="20"
                                                            step="0.1"
                                                            class="w-24 px-3 py-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 font-medium bg-white"
                                                            placeholder="0.0"
                                                            data-weight="{{ $criterion->weight }}"
                                                            oninput="calculateWeightedScore(this, '{{ $criterion->id }}', {{ $criterion->weight }})"
                                                            onchange="calculateWeightedScore(this, '{{ $criterion->id }}', {{ $criterion->weight }})"
                                                        >
                                                    </div>
                                                </td>

                                                <!-- Note pondérée -->
                                                <td class="px-4 py-4 text-center">
                                                    <div class="flex items-center justify-center">
                                                        <input
                                                            type="number"
                                                            name="weighted_scores[{{ $criterion->id }}]"
                                                            id="weighted_score_{{ $criterion->id }}"
                                                            value="{{ isset($weightedScores[$criterion->id]) && $weightedScores[$criterion->id] !== null ? number_format($weightedScores[$criterion->id], 3) : (isset($scores[$criterion->id]) && $scores[$criterion->id] !== null && $scores[$criterion->id] !== '' ? number_format($scores[$criterion->id] * ($criterion->weight / 100), 3) : '') }}"
                                                            min="0"
                                                            step="0.001"
                                                            class="w-32 px-3 py-2 text-center border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 font-medium bg-white"
                                                            placeholder="0.000"
                                                        >
                                                    </div>
                                                </td>

                                                <!-- Commentaire -->
                                                <td class="px-6 py-4">
                                                    <textarea
                                                        name="comments[{{ $criterion->id }}]"
                                                        rows="2"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 resize-none bg-white"
                                                        placeholder="Votre commentaire..."
                                                    >{{ $comments[$criterion->id] ?? '' }}</textarea>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach

                <!-- Résumé et actions -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                        <!-- Résumé des notes -->
                        <div class="flex-1">
                            <div class="bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-lg p-4">
                                <div class="space-y-3">
                                    <!-- Somme des notes pondérées -->
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 mb-1">
                                            Somme totale des notes pondérées
                                        </p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-bold text-blue-700" id="total_weighted_score">0.000</p>
                                            <p class="text-lg text-blue-600">/ <span id="max_weighted_score">{{ number_format($maxWeightedScore, 3) }}</span></p>
                                        </div>
                                    </div>
                                    
                                    <!-- Séparateur -->
                                    <div class="border-t border-blue-300"></div>
                                    
                                    <!-- Moyenne -->
                                    <div>
                                        <p class="text-sm font-medium text-blue-900 mb-1">
                                            Moyenne
                                            <span class="text-xs font-normal text-blue-700" id="criteria_count_info">(0 critère noté)</span>
                                        </p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-3xl font-bold text-green-700" id="average_score">0.00</p>
                                            <p class="text-lg text-green-600">/ <span id="max_average_score">0.00</span></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bouton de sauvegarde -->
                        <div class="flex-shrink-0">
                            <button
                                type="submit"
                                class="w-full md:w-auto px-8 py-3 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition font-semibold shadow-lg hover:shadow-xl flex items-center justify-center gap-2"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Enregistrer l'évaluation
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function calculateWeightedScore(input, criterionId, weight) {
            // Récupérer le poids depuis l'attribut data-weight si disponible
            if (!weight && input.dataset.weight) {
                weight = parseFloat(input.dataset.weight);
            }
            
            let rawScore = parseFloat(input.value);
            
            // Si le champ est vide, mettre la note pondérée à vide aussi
            if (input.value === '' || isNaN(rawScore)) {
                const weightedInput = document.getElementById('weighted_score_' + criterionId);
                if (weightedInput) {
                    weightedInput.value = '';
                }
                updateTotalWeightedScore();
                return;
            }
            
            // Valider que la note est entre 0 et 20
            if (rawScore < 0) {
                rawScore = 0;
                input.value = 0;
            } else if (rawScore > 20) {
                rawScore = 20;
                input.value = 20;
            }
            
            // S'assurer que le poids est un nombre valide
            weight = parseFloat(weight) || 0;
            
            // Calculer la note pondérée : note_brute × (poids / 100)
            const weightedScore = (rawScore * (weight / 100)).toFixed(3);
            const weightedInput = document.getElementById('weighted_score_' + criterionId);
            
            if (weightedInput) {
                weightedInput.value = weightedScore;
            }
            
            // Mettre à jour le total
            updateTotalWeightedScore();
        }

        function updateTotalWeightedScore() {
            let totalWeighted = 0;
            let totalWeight = 0;
            let criteriaCount = 0;
            let sumRawScores = 0;
            
            // Parcourir tous les champs de note brute pour trouver ceux qui sont remplis
            document.querySelectorAll('input[name^="scores"]').forEach(scoreInput => {
                const rawScore = parseFloat(scoreInput.value);
                
                if (scoreInput.value !== '' && !isNaN(rawScore)) {
                    // Récupérer le poids du critère
                    const weight = parseFloat(scoreInput.dataset.weight) || 0;
                    
                    // Récupérer la note pondérée correspondante
                    const match = scoreInput.name.match(/\[([^\]]+)\]/);
                    if (match) {
                        const criterionId = match[1];
                        const weightedInput = document.getElementById('weighted_score_' + criterionId);
                        
                        if (weightedInput) {
                            const weightedScore = parseFloat(weightedInput.value) || 0;
                            totalWeighted += weightedScore;
                        }
                        
                        totalWeight += weight;
                        sumRawScores += rawScore;
                        criteriaCount++;
                    }
                }
            });
            
            // Afficher la somme des notes pondérées
            const totalElement = document.getElementById('total_weighted_score');
            if (totalElement) {
                totalElement.textContent = totalWeighted.toFixed(3);
            }
            
            // Calculer le maximum possible pour les critères notés
            // Maximum = (somme_des_poids_des_critères_notés / 100) × 20
            const maxPossibleForNotedCriteria = (totalWeight / 100) * 20;
            
            // Calculer la moyenne des notes brutes des critères notés
            let average = 0;
            if (criteriaCount > 0) {
                average = sumRawScores / criteriaCount;
            }
            
            const averageElement = document.getElementById('average_score');
            if (averageElement) {
                averageElement.textContent = average.toFixed(2);
            }
            
            // Afficher le maximum possible pour les critères notés (20 si tous les critères sont notés avec note max)
            const maxAverageElement = document.getElementById('max_average_score');
            if (maxAverageElement) {
                // Le maximum de la moyenne est toujours 20 (note maximale sur chaque critère)
                maxAverageElement.textContent = '20.00';
            }
            
            // Afficher le nombre de critères notés
            const criteriaCountElement = document.getElementById('criteria_count_info');
            if (criteriaCountElement) {
                const text = criteriaCount === 1 ? 'critère noté' : 'critères notés';
                criteriaCountElement.textContent = `(${criteriaCount} ${text})`;
            }
        }

        // Initialiser au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // Calculer les notes pondérées pour tous les champs de note brute qui ont une valeur
            document.querySelectorAll('input[name^="scores"]').forEach(input => {
                const match = input.name.match(/\[([^\]]+)\]/);
                if (match) {
                    const criterionId = match[1];
                    const weight = parseFloat(input.dataset.weight) || parseFloat(input.getAttribute('data-weight'));
                    
                    // Si on a une valeur initiale, calculer la note pondérée
                    if (input.value && !isNaN(parseFloat(input.value))) {
                        if (weight && !isNaN(weight)) {
                            calculateWeightedScore(input, criterionId, weight);
                        }
                    }
                }
            });
            
            // Écouter les changements sur les champs de note pondérée pour recalculer le total
            document.querySelectorAll('input[name^="weighted_scores"]').forEach(input => {
                input.addEventListener('input', updateTotalWeightedScore);
                input.addEventListener('change', updateTotalWeightedScore);
            });
            
            // Calculer le total initial
            updateTotalWeightedScore();
        });
    </script>
@endsection

