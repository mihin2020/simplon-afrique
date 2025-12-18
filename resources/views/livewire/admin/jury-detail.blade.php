<div>
    <div class="space-y-6">
        <!-- Informations générales -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations générales</h3>
            
            <div class="space-y-4">
                <!-- Statut -->
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Statut</div>
                    @php
                        $statusConfig = match($jury->status) {
                            'constituted' => ['label' => 'Constitué', 'color' => 'blue'],
                            'in_progress' => ['label' => 'En cours', 'color' => 'yellow'],
                            'completed' => ['label' => 'Terminé', 'color' => 'green'],
                            default => ['label' => $jury->status, 'color' => 'gray'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Membres du jury -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Membres du jury ({{ $jury->members->count() }})</h3>
                @if($isSuperAdmin)
                    <a
                        href="{{ route('admin.jury.add-member', $jury->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajouter un membre
                    </a>
                @endif
            </div>

            @if($jury->members->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="font-medium">Aucun membre dans ce jury.</p>
                    <p class="text-sm mt-2">Cliquez sur "Ajouter un membre" pour commencer.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($jury->members as $member)
                        <div class="relative bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <!-- Badge Président en haut à droite -->
                            @if($member->is_president)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                        </svg>
                                        Président
                                    </span>
                                </div>
                            @endif

                            <!-- Avatar et nom -->
                            <div class="flex flex-col items-center text-center mb-4">
                                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center mb-3 ring-4 ring-red-100">
                                    <span class="text-2xl font-bold text-white">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <h4 class="font-semibold text-gray-900 text-sm mb-1">
                                    {{ $member->user->name }}
                                </h4>
                                <p class="text-xs text-gray-500 truncate w-full px-2">
                                    {{ $member->user->email }}
                                </p>
                            </div>

                            <!-- Profil -->
                            <div class="text-center mb-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $roleOptions[$member->role] ?? $member->role }}
                                </span>
                            </div>

                            <!-- Actions -->
                            @if($isSuperAdmin)
                                <div class="flex items-center justify-center gap-2 mt-3 pt-3 border-t border-gray-200">
                                    @if(!$member->is_president)
                                        <button
                                            wire:click="setPresident('{{ $member->id }}')"
                                            class="flex-1 px-3 py-1.5 text-xs font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition"
                                            title="Définir comme président"
                                        >
                                            Président
                                        </button>
                                    @endif
                                    <form
                                        action="{{ route('admin.jury.remove-member', ['jury' => $jury->id, 'member' => $member->id]) }}"
                                        method="POST"
                                        class="inline"
                                        onsubmit="return confirm('Êtes-vous sûr de vouloir retirer {{ $member->user->name }} de ce jury ?');"
                                    >
                                        @csrf
                                        <button
                                            type="submit"
                                            class="px-3 py-1.5 text-xs font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition"
                                            title="Retirer du jury"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Grille d'évaluation -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Grille d'évaluation</h3>

            @if($isSuperAdmin)
                <!-- Formulaire pour associer une grille -->
                <div class="space-y-4">
                    <div>
                        <label for="evaluation_grid_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Sélectionner une grille d'évaluation
                        </label>
                        <form
                            action="{{ route('admin.jury.update-evaluation-grid', $jury->id) }}"
                            method="POST"
                            class="flex items-center gap-4"
                        >
                            @csrf
                            <select
                                id="evaluation_grid_id"
                                name="evaluation_grid_id"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            >
                                <option value="">-- Aucune grille --</option>
                                @foreach($availableGrids as $grid)
                                    <option value="{{ $grid->id }}" {{ $jury->evaluation_grid_id === $grid->id ? 'selected' : '' }}>
                                        {{ $grid->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button
                                type="submit"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-sm font-medium"
                            >
                                Appliquer
                            </button>
                            @if($jury->evaluation_grid_id)
                                <button
                                    type="button"
                                    onclick="document.getElementById('remove_grid_form').submit();"
                                    class="px-4 py-2 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition"
                                >
                                    Retirer
                                </button>
                            @endif
                        </form>
                        @if($jury->evaluation_grid_id)
                            <form
                                id="remove_grid_form"
                                action="{{ route('admin.jury.update-evaluation-grid', $jury->id) }}"
                                method="POST"
                                class="hidden"
                                onsubmit="return confirm('Êtes-vous sûr de vouloir retirer la grille d\'évaluation de ce jury ?');"
                            >
                                @csrf
                                <input type="hidden" name="evaluation_grid_id" value="">
                            </form>
                        @endif
                        <p class="mt-2 text-sm text-gray-500">
                            Cette grille sera utilisée pour l'évaluation des formateurs par les membres du jury.
                        </p>
                    </div>
                </div>
            @endif

            <!-- Affichage de la grille actuelle -->
            @if($jury->evaluationGrid)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h4 class="text-lg font-semibold text-gray-900">{{ $jury->evaluationGrid->name }}</h4>
                            @if($jury->evaluationGrid->description)
                                <p class="text-sm text-gray-600 mt-1">{{ $jury->evaluationGrid->description }}</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                            @if($isSuperAdmin)
                                <a
                                    href="{{ route('admin.evaluation-grids.detail', $jury->evaluationGrid->id) }}"
                                    target="_blank"
                                    class="text-sm text-red-600 hover:text-red-700 font-medium inline-flex items-center gap-1"
                                >
                                    Modifier la grille
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Lien vers l'évaluation -->
                    @if(isset($availableCandidatures) && $availableCandidatures->isNotEmpty())
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                            <h5 class="text-sm font-semibold text-blue-900 mb-3">Évaluer une candidature</h5>
                            <div class="space-y-2">
                                @foreach($availableCandidatures as $candidature)
                                    @php
                                        $evaluationsData = $evaluationsData ?? [];
                                        $evaluationData = $evaluationsData[$candidature->id] ?? ['evaluated' => false];
                                        $isEvaluated = $evaluationData['evaluated'] ?? false;
                                    @endphp
                                    <a
                                        href="{{ route('admin.jury.evaluation', ['jury' => $jury->id, 'candidature' => $candidature->id]) }}"
                                        class="block px-4 py-2 border rounded-lg transition cursor-pointer {{ $isEvaluated ? 'bg-green-50 border-green-300 hover:bg-green-100 hover:border-green-400' : 'bg-white border-blue-200 hover:bg-blue-50 hover:border-blue-300' }}"
                                        title="{{ $isEvaluated ? 'Cliquez pour voir/modifier l\'évaluation' : 'Cliquez pour évaluer' }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3 flex-1">
                                                <span class="font-medium {{ $isEvaluated ? 'text-green-900' : 'text-gray-900' }} hover:underline">
                                                    @php
                                                        $firstName = $candidature->user->first_name ?? '';
                                                        $lastName = $candidature->user->name ?? '';
                                                        $fullName = trim($firstName . ' ' . $lastName);
                                                    @endphp
                                                    {{ $fullName ?: $candidature->user->name }}
                                                </span>
                                                @if($isEvaluated && isset($evaluationData['average_score']))
                                                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800 font-semibold">
                                                        Moyenne: {{ number_format($evaluationData['average_score'], 2) }}/20
                                                    </span>
                                                    @if(isset($evaluationData['category_count']))
                                                        <span class="text-xs px-2 py-1 rounded-full bg-blue-100 text-blue-800 font-medium">
                                                            {{ $evaluationData['category_count'] }} catégorie(s)
                                                        </span>
                                                    @endif
                                                    @if(isset($evaluationData['criteria_count']))
                                                        <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 font-medium">
                                                            {{ $evaluationData['criteria_count'] }} critère(s)
                                                        </span>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2">
                                                @if($candidature->currentStep)
                                                    <span class="text-sm text-gray-500">{{ $candidature->currentStep->label }}</span>
                                                @endif
                                                @if($isEvaluated)
                                                    <span class="text-xs text-green-700 font-medium">Voir/Modifier</span>
                                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                       
                    </div>
                </div>
            @else
                <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Aucune grille d'évaluation associée</p>
                            <p class="text-sm text-yellow-700 mt-1">
                                @if($isSuperAdmin)
                                    Sélectionnez une grille d'évaluation ci-dessus pour permettre aux membres du jury d'évaluer les formateurs.
                                @else
                                    Aucune grille d'évaluation n'a été associée à ce jury. Contactez le super administrateur pour en associer une.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Section Président du Jury -->
        @if(isset($isPresident) && ($isPresident || $isSuperAdmin) && isset($presidentData) && count($presidentData) > 0)
            @php
                $candidaturesWithEvaluations = $availableCandidatures->filter(function($c) use ($presidentData) {
                    $info = $presidentData[$c->id] ?? null;
                    return $info && count($info['members_evaluations']) > 0;
                });
                $readyCount = $candidaturesWithEvaluations->filter(function($c) use ($presidentData) {
                    return ($presidentData[$c->id]['all_members_evaluated'] ?? false);
                })->count();
                $pendingCount = $candidaturesWithEvaluations->count() - $readyCount;
            @endphp

            <div class="bg-white rounded-xl shadow-sm border-2 border-yellow-400" x-data="{ activeTab: null }">
                <!-- En-tête avec statistiques -->
                <div class="p-6 border-b border-yellow-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="w-7 h-7 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">Espace Président du Jury</h3>
                                <p class="text-sm text-gray-600">Récapitulatif des évaluations et validation des badges</p>
                            </div>
                        </div>
                        
                        <!-- Statistiques rapides -->
                        <div class="flex items-center gap-4">
                            <div class="text-center px-4 py-2 bg-white rounded-lg shadow-sm">
                                <p class="text-2xl font-bold text-green-600">{{ $readyCount }}</p>
                                <p class="text-xs text-gray-500">Prêt(s)</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-white rounded-lg shadow-sm">
                                <p class="text-2xl font-bold text-yellow-600">{{ $pendingCount }}</p>
                                <p class="text-xs text-gray-500">En attente</p>
                            </div>
                            <div class="text-center px-4 py-2 bg-white rounded-lg shadow-sm">
                                <p class="text-2xl font-bold text-gray-700">{{ $candidaturesWithEvaluations->count() }}</p>
                                <p class="text-xs text-gray-500">Total</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des formateurs en accordéon -->
                <div class="divide-y divide-gray-200">
                    @foreach($availableCandidatures as $index => $candidature)
                        @php
                            $presidentInfo = $presidentData[$candidature->id] ?? null;
                        @endphp

                        @if($presidentInfo && count($presidentInfo['members_evaluations']) > 0)
                            <div class="border-l-4 {{ $presidentInfo['all_members_evaluated'] ? 'border-l-green-500' : 'border-l-yellow-500' }}">
                                <!-- En-tête cliquable -->
                                <button 
                                    type="button"
                                    @click="activeTab = activeTab === {{ $index }} ? null : {{ $index }}"
                                    class="w-full px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition focus:outline-none"
                                >
                                    <div class="flex items-center gap-4">
                                        <!-- Avatar -->
                                        <div class="h-12 w-12 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-lg font-bold text-white">
                                                {{ strtoupper(substr($candidature->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        
                                        <!-- Infos -->
                                        <div class="text-left">
                                            @php
                                                $firstName = $candidature->user->first_name ?? '';
                                                $lastName = $candidature->user->name ?? '';
                                                $fullName = trim($firstName . ' ' . $lastName);
                                            @endphp
                                            <h4 class="font-semibold text-gray-900">{{ $fullName ?: $candidature->user->name }}</h4>
                                            <div class="flex items-center gap-3 mt-1">
                                                <span class="text-sm text-gray-500">
                                                    {{ $presidentInfo['members_count'] }}/{{ $presidentInfo['total_members'] }} évaluations
                                                </span>
                                                @if($presidentInfo['global_average'] > 0)
                                                    <span class="text-sm font-medium text-blue-600">
                                                        Moy: {{ number_format($presidentInfo['global_average'], 2) }}/20
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <!-- Badge décerné selon la moyenne -->
                                        @if($presidentInfo['awarded_badge'])
                                            @php
                                                $badgeColors = match($presidentInfo['awarded_badge']->name) {
                                                    'senior' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
                                                    'intermediaire' => 'bg-gray-100 text-gray-800 border-gray-300',
                                                    'junior' => 'bg-orange-100 text-orange-800 border-orange-300',
                                                    default => 'bg-blue-100 text-blue-800 border-blue-300',
                                                };
                                            @endphp
                                            <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium border {{ $badgeColors }}">
                                                {{ $presidentInfo['awarded_badge']->getEmoji() }}
                                                {{ $presidentInfo['awarded_badge']->label ?? $presidentInfo['awarded_badge']->name }}
                                            </span>
                                        @elseif($presidentInfo['global_average'] > 0 && $presidentInfo['global_average'] < 10)
                                            <span class="hidden sm:inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Non éligible
                                            </span>
                                        @endif

                                        <!-- Statut -->
                                        @if($presidentInfo['president_decision'])
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $presidentInfo['president_decision'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $presidentInfo['president_decision'] === 'approved' ? '✓ Validé' : '✗ Rejeté' }}
                                            </span>
                                        @elseif($presidentInfo['all_members_evaluated'])
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                                <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                                Prêt
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                                En attente
                                            </span>
                                        @endif

                                        <!-- Chevron -->
                                        <svg 
                                            class="w-5 h-5 text-gray-400 transition-transform duration-200"
                                            :class="{ 'rotate-180': activeTab === {{ $index }} }"
                                            fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Contenu dépliable -->
                                <div 
                                    x-show="activeTab === {{ $index }}"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 -translate-y-2"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="opacity-100 translate-y-0"
                                    x-transition:leave-end="opacity-0 -translate-y-2"
                                    class="px-6 pb-6 bg-gray-50"
                                    style="display: none;"
                                >
                                    <!-- Tableau récapitulatif -->
                                    <div class="overflow-x-auto mb-4 mt-2">
                                        <table class="min-w-full divide-y divide-gray-200 bg-white rounded-lg overflow-hidden shadow-sm">
                                            <thead class="bg-gray-100">
                                                <tr>
                                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Membre</th>
                                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Catégories</th>
                                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Critères</th>
                                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Moyenne /20</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-gray-200">
                                                @foreach($presidentInfo['members_evaluations'] as $memberEval)
                                                    <tr class="hover:bg-gray-50">
                                                        <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                            {{ $memberEval['member_name'] }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                                                            {{ $memberEval['category_count'] ?? '-' }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                                                            {{ $memberEval['criteria_count'] }}
                                                        </td>
                                                        <td class="px-4 py-3 text-sm text-center">
                                                            <span class="font-semibold text-green-700">{{ number_format($memberEval['average_score'], 2) }}/20</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                <tr class="bg-gray-100 font-bold">
                                                    <td class="px-4 py-3 text-sm text-gray-900">Moyenne globale</td>
                                                    <td class="px-4 py-3 text-sm text-center text-gray-600">-</td>
                                                    <td class="px-4 py-3 text-sm text-center text-gray-600">-</td>
                                                    <td class="px-4 py-3 text-sm text-center text-green-800">{{ number_format($presidentInfo['global_average'], 2) }}/20</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Badge décerné selon la moyenne -->
                                    @if($presidentInfo['awarded_badge'])
                                        @php
                                            $badgeColors = match($presidentInfo['awarded_badge']->name) {
                                                'senior' => 'bg-yellow-50 border-yellow-300',
                                                'intermediaire' => 'bg-gray-50 border-gray-300',
                                                'junior' => 'bg-orange-50 border-orange-300',
                                                default => 'bg-blue-50 border-blue-300',
                                            };
                                            $badgeTextColors = match($presidentInfo['awarded_badge']->name) {
                                                'senior' => 'text-yellow-800',
                                                'intermediaire' => 'text-gray-800',
                                                'junior' => 'text-orange-800',
                                                default => 'text-blue-800',
                                            };
                                        @endphp
                                        <div class="mb-4 p-3 {{ $badgeColors }} border rounded-lg flex items-center justify-between">
                                            <span class="text-sm {{ $badgeTextColors }} font-medium">Badge décerné :</span>
                                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-semibold {{ $badgeTextColors }} bg-white border">
                                                {{ $presidentInfo['awarded_badge']->getEmoji() }}
                                                {{ $presidentInfo['awarded_badge']->label ?? $presidentInfo['awarded_badge']->name }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-500 mb-4">
                                            Ce badge est attribué automatiquement selon la moyenne ({{ number_format($presidentInfo['global_average'], 2) }}/20) et les seuils configurés.
                                        </p>
                                    @elseif($presidentInfo['global_average'] > 0 && $presidentInfo['global_average'] < 10)
                                        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg flex items-center justify-between">
                                            <span class="text-sm text-red-800 font-medium">Badge décerné :</span>
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                                Non éligible (moyenne &lt; 10)
                                            </span>
                                        </div>
                                    @endif

                                    <!-- Formulaire de validation -->
                                    @if($presidentInfo['all_members_evaluated'])
                                        <div class="relative" x-data="{ submitting: false }">
                                            <!-- Overlay de chargement -->
                                            <div 
                                                x-show="submitting"
                                                x-transition:enter="transition ease-out duration-200"
                                                x-transition:enter-start="opacity-0"
                                                x-transition:enter-end="opacity-100"
                                                x-transition:leave="transition ease-in duration-150"
                                                x-transition:leave-start="opacity-100"
                                                x-transition:leave-end="opacity-0"
                                                class="absolute inset-0 bg-white bg-opacity-90 rounded-lg flex items-center justify-center z-10"
                                                style="display: none;"
                                            >
                                                <div class="flex flex-col items-center gap-3">
                                                    <svg class="animate-spin h-8 w-8 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <span class="text-sm text-gray-600 font-medium">Validation en cours...</span>
                                                </div>
                                            </div>
                                            
                                            <form 
                                                action="{{ route('admin.jury.president-validate', ['jury' => $jury->id, 'candidature' => $candidature->id]) }}" 
                                                method="POST" 
                                                class="space-y-4"
                                                @submit="submitting = true"
                                            >
                                                @csrf
                                            
                                            <div>
                                                <label for="president_comment_{{ $candidature->id }}" class="block text-sm font-medium text-gray-700 mb-2">
                                                    Commentaire général
                                                </label>
                                                <textarea
                                                    id="president_comment_{{ $candidature->id }}"
                                                    name="president_comment"
                                                    rows="2"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 bg-white"
                                                    placeholder="Votre commentaire..."
                                                    :disabled="submitting"
                                                >{{ $presidentInfo['president_comment'] ?? '' }}</textarea>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-3">
                                                <button
                                                    type="submit"
                                                    name="decision"
                                                    value="approved"
                                                    :disabled="submitting"
                                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    <svg 
                                                        x-show="submitting" 
                                                        class="w-4 h-4 animate-spin" 
                                                        fill="none" 
                                                        viewBox="0 0 24 24"
                                                        style="display: none;"
                                                    >
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <svg 
                                                        x-show="!submitting" 
                                                        class="w-4 h-4" 
                                                        fill="none" 
                                                        stroke="currentColor" 
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                    <span x-text="submitting ? 'Validation...' : 'Valider'">Valider</span>
                                                </button>
                                                <button
                                                    type="submit"
                                                    name="decision"
                                                    value="rejected"
                                                    :disabled="submitting"
                                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-semibold text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                                                >
                                                    <svg 
                                                        x-show="submitting" 
                                                        class="w-4 h-4 animate-spin" 
                                                        fill="none" 
                                                        viewBox="0 0 24 24"
                                                        style="display: none;"
                                                    >
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                    <svg 
                                                        x-show="!submitting" 
                                                        class="w-4 h-4" 
                                                        fill="none" 
                                                        stroke="currentColor" 
                                                        viewBox="0 0 24 24"
                                                    >
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                    <span x-text="submitting ? 'Rejet...' : 'Rejeter'">Rejeter</span>
                                                </button>

                                                @if($presidentInfo['president_decision'])
                                                    <span class="ml-auto text-sm {{ $presidentInfo['president_decision'] === 'approved' ? 'text-green-700' : 'text-red-700' }}">
                                                        Décision actuelle : <strong>{{ $presidentInfo['president_decision'] === 'approved' ? 'Approuvé' : 'Rejeté' }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                            </form>
                                        </div>
                                    @else
                                        <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg flex items-center gap-2">
                                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <p class="text-sm text-yellow-800">
                                                En attente des évaluations de tous les membres du jury.
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Message si aucun formateur -->
                @if($candidaturesWithEvaluations->isEmpty())
                    <div class="p-8 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <p class="text-gray-500">Aucune évaluation n'a encore été soumise.</p>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
