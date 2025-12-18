@if($candidature)
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

    <!-- En-tête avec bouton retour -->
    <div class="mb-6">
        <a
            href="{{ route('admin.candidatures') }}"
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste
        </a>
        <h2 class="text-2xl font-semibold text-gray-900">Détail du dossier</h2>
    </div>

    <div class="space-y-6">
        <!-- Informations du formateur -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations du formateur</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-4">
                        <div class="h-16 w-16 rounded-full bg-red-100 flex items-center justify-center">
                            <span class="text-xl font-medium text-red-600">
                                {{ strtoupper(substr($candidature->user->name, 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <div class="text-lg font-semibold text-gray-900">{{ $candidature->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ $candidature->user->email }}</div>
                        </div>
                    </div>
                </div>
                <div>
                    @if($candidature->user->formateurProfile)
                        <div class="space-y-2">
                            @if($candidature->user->formateurProfile->phone_number)
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Téléphone :</span>
                                    <span class="text-gray-900">{{ $candidature->user->formateurProfile->phone_country_code }} {{ $candidature->user->formateurProfile->phone_number }}</span>
                                </div>
                            @endif
                            @if($candidature->user->formateurProfile->country)
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Pays :</span>
                                    <span class="text-gray-900">{{ $candidature->user->formateurProfile->country }}</span>
                                </div>
                            @endif
                            @if($candidature->user->formateurProfile->years_of_experience)
                                <div class="text-sm">
                                    <span class="font-medium text-gray-700">Expérience :</span>
                                    <span class="text-gray-900">{{ $candidature->user->formateurProfile->years_of_experience }}</span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Informations de la candidature -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Informations de la candidature</h3>
                @if($isSuperAdmin && $candidature->status === 'submitted')
                    <button
                        type="button"
                        wire:click="validateCandidature"
                        wire:loading.attr="disabled"
                        wire:target="validateCandidature"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="validateCandidature">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Valider la candidature (Étape 1)
                        </span>
                        <span wire:loading wire:target="validateCandidature" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Validation en cours...
                        </span>
                    </button>
                @endif
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Badge visé</div>
                    @if($candidature->badge)
                        <div class="flex items-center gap-2">
                            <span class="text-2xl">{{ $candidature->badge->getEmoji() }}</span>
                            <span class="text-lg text-gray-900">{{ str_replace('Label ', '', $candidature->badge->label) }}</span>
                        </div>
                    @else
                        <span class="text-gray-400">Non défini</span>
                    @endif
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Statut</div>
                    @php
                        $statusConfig = match($candidature->status) {
                            'draft' => ['label' => 'Brouillon', 'color' => 'gray'],
                            'submitted' => ['label' => 'Soumise', 'color' => 'blue'],
                            'in_review' => ['label' => 'En examen', 'color' => 'yellow'],
                            'validated' => ['label' => 'Validée', 'color' => 'green'],
                            'rejected' => ['label' => 'Rejetée', 'color' => 'red'],
                            default => ['label' => $candidature->status, 'color' => 'gray'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Étape actuelle</div>
                    @if($candidature->currentStep)
                        <span class="text-gray-900">{{ $candidature->currentStep->label }}</span>
                    @else
                        <span class="text-gray-400">Non définie</span>
                    @endif
                </div>
                <div>
                    <div class="text-sm font-medium text-gray-700 mb-1">Date de dépôt</div>
                    <span class="text-gray-900">{{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- Assignation du jury -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Désignation du jury</h3>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($canAssignJury)
                            Assignez un jury constitué pour évaluer cette candidature
                        @else
                            <span class="text-amber-600 font-medium">⚠️ Vous devez d'abord valider la candidature (Étape 1) avant d'assigner un jury</span>
                        @endif
                    </p>
                </div>
                @if($candidature->juries->isNotEmpty())
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-500 text-white">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Jury assigné
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-amber-200 text-amber-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        En attente d'assignation
                    </span>
                @endif
            </div>
            <div class="space-y-4">
                <div>
                    <label for="selectedJuryId" class="block text-sm font-medium text-gray-700 mb-2">
                        Sélectionner un jury constitué <span class="text-red-600">*</span>
                    </label>
                    <select
                        id="selectedJuryId"
                        wire:model.live="selectedJuryId"
                        @if(!$canAssignJury || $hasEvaluations) disabled @endif
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition @if(!$canAssignJury || $hasEvaluations) bg-gray-100 cursor-not-allowed opacity-60 @endif"
                    >
                        <option value="">-- Sélectionner un jury --</option>
                        @foreach($availableJuries as $jury)
                            @php
                                $statusColor = match($jury->status) {
                                    'constituted' => 'text-blue-600',
                                    'in_progress' => 'text-yellow-600',
                                    'completed' => 'text-green-600',
                                    default => 'text-gray-600',
                                };
                                $statusLabel = match($jury->status) {
                                    'constituted' => 'Constitué',
                                    'in_progress' => 'En cours',
                                    'completed' => 'Terminé',
                                    default => $jury->status,
                                };
                            @endphp
                            <option value="{{ $jury->id }}">
                                {{ $jury->name }}
                                @if($jury->members->isNotEmpty())
                                    - {{ $jury->members->count() }} membre{{ $jury->members->count() > 1 ? 's' : '' }}
                                @endif
                                - {{ $statusLabel }}
                            </option>
                        @endforeach
                    </select>
                    <div class="mt-2 space-y-1">
                        <p class="text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Seuls les jurys constitués (avec au moins un membre) peuvent être assignés.</span>
                        </p>
                        <p class="text-xs text-gray-500 flex items-center gap-1">
                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>L'assignation est automatique et le statut de la candidature passe en "En examen".</span>
                        </p>
                        @if($hasEvaluations)
                            <p class="text-xs text-amber-600 flex items-center gap-1 font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span>⚠️ Le jury ne peut pas être modifié car des évaluations ont été soumises.</span>
                            </p>
                        @endif
                    </div>
                    @if($availableJuries->isEmpty())
                        <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                            <p class="text-sm text-amber-700 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                Aucun jury constitué disponible. Créez d'abord un jury et ajoutez des membres avant de pouvoir l'assigner.
                            </p>
                        </div>
                    @endif
                </div>

                @if($candidature->juries->isNotEmpty())
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        @if($hasEvaluations)
                            <div class="mb-4 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-amber-800 mb-1">Attention : Évaluations en cours</p>
                                        <p class="text-xs text-amber-700">
                                            Des évaluations ont déjà été soumises par les membres du jury. Vous ne pouvez plus retirer ou modifier le jury assigné. Pour changer de jury, vous devez d'abord supprimer toutes les évaluations existantes.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="flex items-center gap-2 mb-4">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <div class="text-sm font-semibold text-gray-900">Jury désigné avec succès</div>
                                <div class="text-xs text-gray-500">Les membres du jury peuvent maintenant procéder à l'évaluation de cette candidature</div>
                            </div>
                        </div>
                        @foreach($candidature->juries as $jury)
                            @php
                                $juryStatusConfig = match($jury->status) {
                                    'constituted' => ['label' => 'Constitué', 'color' => 'blue', 'bg' => 'bg-blue-50', 'border' => 'border-blue-200'],
                                    'in_progress' => ['label' => 'En cours d\'évaluation', 'color' => 'yellow', 'bg' => 'bg-yellow-50', 'border' => 'border-yellow-200'],
                                    'completed' => ['label' => 'Évaluation terminée', 'color' => 'green', 'bg' => 'bg-green-50', 'border' => 'border-green-200'],
                                    default => ['label' => $jury->status, 'color' => 'gray', 'bg' => 'bg-gray-50', 'border' => 'border-gray-200'],
                                };
                            @endphp
                            <div class="{{ $juryStatusConfig['bg'] }} border {{ $juryStatusConfig['border'] }} rounded-lg p-5 mb-3">
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-semibold text-gray-900 text-lg">{{ $jury->name }}</h4>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                bg-{{ $juryStatusConfig['color'] }}-100 text-{{ $juryStatusConfig['color'] }}-800">
                                                {{ $juryStatusConfig['label'] }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">{{ $jury->members->count() }}</span> membre{{ $jury->members->count() > 1 ? 's' : '' }} assigné{{ $jury->members->count() > 1 ? 's' : '' }} à cette candidature
                                        </p>
                                    </div>
                                    <a
                                        href="{{ route('admin.jury.detail', $jury->id) }}"
                                        class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center gap-1.5 px-3 py-1.5 rounded-lg hover:bg-white transition"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        Gérer le jury
                                    </a>
                                </div>
                                @if($jury->members->isNotEmpty())
                                    <div class="text-xs font-medium text-gray-700 mb-2">Membres du jury :</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($jury->members as $member)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs bg-white border border-gray-200 shadow-sm">
                                                <span class="font-medium text-gray-900">{{ $member->user->name }}</span>
                                                @if($member->is_president)
                                                    <span class="ml-1.5 text-red-600 font-semibold">(Président)</span>
                                                @endif
                                                <span class="ml-1.5 text-gray-500">
                                                    - {{ $roleOptions[$member->role] ?? $member->role }}
                                                </span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Progression -->
        @if($candidature->steps->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Progression</h3>
                <div class="space-y-3">
                    @foreach($candidature->steps->sortBy('labellisationStep.display_order') as $step)
                        @php
                            $isCompleted = $step->status === 'completed';
                            $isInProgress = $step->status === 'in_progress';
                        @endphp
                        <div class="flex items-center gap-3">
                            @if($isCompleted)
                                <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @elseif($isInProgress)
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
                            <div class="flex-1">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $step->labellisationStep->label }}
                                </div>
                                @if($step->completed_at)
                                    <div class="text-xs text-gray-500">
                                        Complétée le {{ $step->completed_at->format('d/m/Y') }}
                                    </div>
                                @endif
                            </div>
                            <span class="text-xs px-2 py-1 rounded
                                @if($isCompleted) bg-green-100 text-green-800
                                @elseif($isInProgress) bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                @if($isCompleted) Terminé
                                @elseif($isInProgress) En cours
                                @else En attente
                                @endif
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Documents -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Documents</h3>
            
            <!-- Documents obligatoires -->
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3">Documents obligatoires</h4>
                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ route('admin.candidature.download-cv', $candidature->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Télécharger le CV
                    </a>
                    <a
                        href="{{ route('admin.candidature.download-motivation', $candidature->id) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Télécharger la lettre de motivation
                    </a>
                    @if($candidature->portfolio_url)
                        <a
                            href="{{ $candidature->portfolio_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            Voir le portfolio
                        </a>
                    @endif
                </div>
            </div>

            <!-- Pièces jointes supplémentaires -->
            @if($candidature->attachments && count($candidature->attachments) > 0)
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-3">Pièces jointes supplémentaires</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($candidature->attachments as $index => $attachment)
                            <a
                                href="{{ route('admin.candidature.download-attachment', ['candidature' => $candidature->id, 'index' => $index]) }}"
                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition border border-gray-200"
                            >
                                <div class="flex-shrink-0">
                                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        {{ $attachment['name'] ?? 'Document ' . ($index + 1) }}
                                    </p>
                                    <p class="text-xs text-gray-500">Cliquez pour télécharger</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@else
<div class="text-center py-12">
    <p class="text-gray-500">Candidature non trouvée.</p>
</div>
@endif