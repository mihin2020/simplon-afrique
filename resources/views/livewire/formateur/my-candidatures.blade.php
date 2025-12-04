<div>
    <div class="mb-6">
        <p class="text-gray-600">
            Consultez l'état de vos candidatures et téléchargez vos documents.
        </p>
    </div>

    @if($candidatures->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune candidature</h3>
            <p class="text-gray-500 mb-6">Vous n'avez pas encore déposé de candidature.</p>
            <a
                href="{{ route('formateur.create-candidature') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Déposer une candidature
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach($candidaturesWithSteps as $item)
                @php
                    $candidature = $item['candidature'];
                    $steps = $item['steps'];
                    $currentStepLabel = $item['currentStepLabel'];
                @endphp
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                @if($candidature->badge)
                                    <span class="text-2xl">{{ $candidature->badge->getEmoji() }}</span>
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        {{ str_replace('Label ', '', $candidature->badge->label) }}
                                    </h3>
                                @else
                                    <span class="text-2xl">⭐</span>
                                    <h3 class="text-xl font-semibold text-gray-900">
                                        Badge non défini
                                    </h3>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span>Déposée le {{ $candidature->created_at->format('d/m/Y à H:i') }}</span>
                                @if($currentStepLabel)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">
                                        Étape : {{ $currentStepLabel }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div>
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
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                                {{ $statusConfig['label'] }}
                            </span>
                        </div>
                    </div>

                    <!-- Timeline des étapes dynamiques -->
                    @if($steps->isNotEmpty())
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900 mb-4">Progression</h4>
                            <div class="flex items-center justify-between overflow-x-auto pb-2">
                                @foreach($steps as $index => $step)
                                    @php
                                        $isCompleted = $step['status'] === 'completed';
                                        $isInProgress = $step['status'] === 'in_progress';
                                        $isRejected = $step['status'] === 'rejected';
                                    @endphp
                                    <div class="flex items-center {{ !$loop->last ? 'flex-1' : '' }}">
                                        <div class="flex flex-col items-center">
                                            @if($isCompleted)
                                                <div class="h-8 w-8 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </div>
                                            @elseif($isRejected)
                                                <div class="h-8 w-8 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </div>
                                            @elseif($isInProgress)
                                                <div class="h-8 w-8 rounded-full bg-yellow-500 flex items-center justify-center flex-shrink-0 animate-pulse">
                                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            <span class="text-xs mt-1 text-center max-w-[80px] truncate {{ $isCompleted ? 'text-green-700 font-medium' : ($isInProgress ? 'text-yellow-700 font-medium' : 'text-gray-500') }}">
                                                {{ $step['label'] }}
                                            </span>
                                        </div>
                                        @if(!$loop->last)
                                            <div class="flex-1 h-0.5 mx-2 {{ $isCompleted ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Documents -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h4 class="text-sm font-medium text-gray-900 mb-4">Documents obligatoires</h4>
                        <div class="flex flex-wrap gap-3 mb-6">
                            <a
                                href="{{ route('formateur.candidature.download-cv', $candidature->id) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-50 text-gray-700 rounded-lg hover:bg-gray-100 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Télécharger le CV
                            </a>
                            <a
                                href="{{ route('formateur.candidature.download-motivation', $candidature->id) }}"
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

                        <!-- Pièces jointes supplémentaires -->
                        @if($candidature->attachments && count($candidature->attachments) > 0)
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-4">Pièces jointes supplémentaires</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                    @foreach($candidature->attachments as $index => $attachment)
                                        <a
                                            href="{{ route('formateur.candidature.download-attachment', ['candidature' => $candidature->id, 'index' => $index]) }}"
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
            @endforeach

            <!-- Pagination -->
            <div class="mt-6">
                {{ $candidatures->links() }}
            </div>
        </div>
    @endif
</div>

