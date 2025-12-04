<div class="space-y-6">
    <!-- En-tête -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                Bienvenue, {{ auth()->user()->name }} !
            </h2>
            <p class="text-gray-600">
                Voici les candidatures que vous devez évaluer.
            </p>
        </div>
    </div>

    <!-- Candidatures à évaluer -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            Candidatures en attente d'évaluation
        </h3>

        @if($candidaturesToEvaluate->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <p class="mt-4 text-sm font-medium text-gray-900">Aucune candidature en attente d'évaluation pour le moment.</p>
                <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg max-w-2xl mx-auto text-left">
                    <p class="text-sm text-blue-800 mb-2"><strong>Pourquoi ne voyez-vous rien ?</strong></p>
                    <ul class="text-sm text-blue-700 space-y-1 list-disc list-inside">
                        <li>Vous n'êtes peut-être pas encore membre d'un jury</li>
                        <li>Aucune candidature n'a été assignée à votre jury</li>
                        <li>Les candidatures assignées ne sont pas encore en statut "en examen"</li>
                    </ul>
                    <p class="text-xs text-blue-600 mt-3"><strong>Contactez l'administrateur</strong> pour être ajouté à un jury ou pour qu'une candidature vous soit assignée.</p>
                </div>
            </div>
        @else
            <div class="space-y-6">
                @foreach($candidaturesToEvaluate as $item)
                    @php
                        $candidature = $item['candidature'];
                        $jury = $item['jury'];
                        $stepsWithStatus = $item['stepsWithStatus'];
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-6 hover:border-red-300 transition">
                        <!-- En-tête de la candidature -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                            <div>
                                <div class="flex items-center gap-3 mb-1">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ $candidature->user->name }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $jury->name }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500">Candidature soumise le {{ $candidature->created_at->format('d/m/Y') }}</p>
                            </div>
                        </div>

                        <!-- Liste des étapes avec boutons d'évaluation -->
                        <div class="space-y-3">
                            <h5 class="text-sm font-medium text-gray-700 mb-3">Étapes d'évaluation :</h5>
                            @foreach($stepsWithStatus as $stepData)
                                @php
                                    $step = $stepData['step'];
                                    $status = $stepData['status'];
                                    $hasEvaluated = $stepData['hasEvaluated'];
                                    $canEvaluate = $stepData['canEvaluate'];
                                @endphp
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center gap-3 flex-1">
                                        <!-- Icône de statut -->
                                        @if($status === 'completed')
                                            <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </div>
                                        @elseif($status === 'in_progress')
                                            <div class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-white animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-gray-300 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                            </div>
                                        @endif

                                        <!-- Informations de l'étape -->
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900">{{ $step->label }}</span>
                                                @if($status === 'in_progress')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        En cours
                                                    </span>
                                                @elseif($status === 'completed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        Terminée
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                        En attente
                                                    </span>
                                                @endif
                                            </div>
                                            @if($hasEvaluated)
                                                <p class="text-xs text-green-600 mt-1">✓ Vous avez déjà évalué cette étape</p>
                                            @elseif($canEvaluate)
                                                <p class="text-xs text-amber-600 mt-1">⏱ En attente de votre évaluation</p>
                                            @else
                                                <p class="text-xs text-gray-500 mt-1">Cette étape n'est pas encore accessible</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Bouton d'action -->
                                    <div class="ml-4">
                                        @if($hasEvaluated)
                                            <a
                                                href="{{ route('jury.evaluate-step', ['candidature' => $candidature->id, 'step' => $step->id]) }}"
                                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium text-sm"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                Voir/Modifier
                                            </a>
                                        @elseif($canEvaluate)
                                            <a
                                                href="{{ route('jury.evaluate-step', ['candidature' => $candidature->id, 'step' => $step->id]) }}"
                                                class="inline-flex items-center gap-2 px-5 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium text-sm"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Évaluer
                                            </a>
                                        @else
                                            <span class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-400 rounded-lg font-medium text-sm cursor-not-allowed">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                                </svg>
                                                Indisponible
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Candidatures prêtes pour validation président -->
    @if($candidaturesReadyForValidation->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">
                    Candidatures prêtes pour validation président
                </h3>
            </div>

            <div class="space-y-4">
                @foreach($candidaturesReadyForValidation as $item)
                    @php
                        $candidature = $item['candidature'];
                        $jury = $item['jury'];
                    @endphp
                    <div class="border border-yellow-300 rounded-lg p-5 bg-yellow-50">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ $candidature->user->name }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Toutes les étapes terminées
                                    </span>
                                </div>
                                
                                <p class="text-sm text-gray-600">
                                    Jury : {{ $jury->name }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    Toutes les évaluations ont été soumises. Vous pouvez maintenant valider ou rejeter cette candidature.
                                </p>
                            </div>

                            <div class="ml-4">
                                <a
                                    href="{{ route('jury.president-validation', $candidature->id) }}"
                                    class="inline-flex items-center gap-2 px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition font-medium"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Valider/Rejeter
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Candidatures terminées -->
    @if($completedCandidatures->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                Candidatures terminées
            </h3>

            <div class="space-y-4">
                @foreach($completedCandidatures as $item)
                    @php
                        $candidature = $item['candidature'];
                        $jury = $item['jury'];
                    @endphp
                    <div class="border border-gray-200 rounded-lg p-5">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <h4 class="text-lg font-semibold text-gray-900">
                                        {{ $candidature->user->name }}
                                    </h4>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($candidature->status === 'validated') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800
                                        @endif">
                                        @if($candidature->status === 'validated')
                                            Validée
                                        @else
                                            Rejetée
                                        @endif
                                    </span>
                                    @if($candidature->badge)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $candidature->badge->label }}
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-sm text-gray-600">
                                    Jury : {{ $jury->name }}
                                </p>
                            </div>

                            <div class="ml-4">
                                <a
                                    href="{{ route('jury.view-evaluations', $candidature->id) }}"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Voir les évaluations
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

