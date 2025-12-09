<div>
    <div class="mb-6 flex items-center justify-between">
        <a href="{{ route('admin.job-offers') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste
        </a>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('admin.job-offers.edit', $jobOffer) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
                Modifier
            </a>
            @if($jobOffer->status === 'draft')
                <button
                    wire:click="publish"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                    wire:loading.attr="disabled"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="publish">Publier</span>
                    <span wire:loading wire:target="publish">Publication...</span>
                </button>
            @elseif($jobOffer->status === 'published')
                <button
                    wire:click="close"
                    wire:confirm="Êtes-vous sûr de vouloir clôturer cette offre ?"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                    </svg>
                    Clôturer
                </button>
            @elseif($jobOffer->status === 'closed')
                <button
                    wire:click="reopen"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Réouvrir
                </button>
            @endif
        </div>
    </div>

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

    <!-- Détail de l'offre -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-semibold text-gray-900">{{ $jobOffer->title }}</h2>
                    @php
                        $statusConfig = match($jobOffer->status) {
                            'draft' => ['label' => 'Brouillon', 'color' => 'gray'],
                            'published' => ['label' => 'Publiée', 'color' => 'green'],
                            'closed' => ['label' => 'Clôturée', 'color' => 'red'],
                            default => ['label' => $jobOffer->status, 'color' => 'gray'],
                        };
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                        {{ $statusConfig['label'] }}
                    </span>
                </div>
                <p class="text-gray-600">
                    Créée par {{ $jobOffer->creator->name }} le {{ $jobOffer->created_at->format('d/m/Y à H:i') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Type de contrat</div>
                <div class="text-lg font-semibold text-gray-900">{{ $jobOffer->contract_type_label }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Localisation</div>
                <div class="text-lg font-semibold text-gray-900">{{ $jobOffer->location }}</div>
                <div class="text-sm text-gray-600">{{ $jobOffer->remote_policy_label }}</div>
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Date limite</div>
                <div class="text-lg font-semibold text-gray-900">{{ $jobOffer->application_deadline->format('d/m/Y') }}</div>
                @if($jobOffer->application_deadline->isPast())
                    <div class="text-sm text-red-600">Expirée</div>
                @else
                    <div class="text-sm text-green-600">{{ $jobOffer->application_deadline->diffForHumans() }}</div>
                @endif
            </div>
            <div class="bg-gray-50 rounded-lg p-4">
                <div class="text-sm font-medium text-gray-500 mb-1">Candidatures</div>
                <div class="text-lg font-semibold text-gray-900">{{ $jobOffer->applications->count() }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Niveau d'expérience requis</h4>
                <p class="text-gray-900">{{ $jobOffer->experience_years }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Formation minimale</h4>
                <p class="text-gray-900">{{ $jobOffer->minimum_education }}</p>
            </div>
        </div>

        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Compétences requises</h4>
            <div class="flex flex-wrap gap-2">
                @foreach($jobOffer->required_skills as $skill)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-800">
                        {{ $skill }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-2">Description du poste</h4>
            <div class="prose prose-sm max-w-none text-gray-600">
                {!! nl2br(e($jobOffer->description)) !!}
            </div>
        </div>

        @if($jobOffer->additional_info)
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Informations complémentaires</h4>
                <div class="prose prose-sm max-w-none text-gray-600">
                    {!! nl2br(e($jobOffer->additional_info)) !!}
                </div>
            </div>
        @endif

        @if($jobOffer->attachment_path)
            <div>
                <h4 class="text-sm font-medium text-gray-700 mb-2">Pièce jointe</h4>
                <a
                    href="{{ route('admin.job-offers.attachment', $jobOffer) }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Télécharger la pièce jointe
                </a>
            </div>
        @endif
    </div>

    <!-- Liste des candidatures -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Candidatures ({{ $applications->total() }})</h3>
            <div class="flex items-center gap-4">
                <select
                    wire:model.live="applicationStatusFilter"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                >
                    <option value="">Tous les statuts</option>
                    @foreach($applicationStatusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select
                    wire:model.live="applicantTypeFilter"
                    class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                >
                    <option value="">Tous les types</option>
                    @foreach($applicantTypeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($applications->isEmpty())
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune candidature</h3>
                <p class="text-gray-500">Aucune candidature n'a été reçue pour cette offre.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Candidat
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Type
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($applications as $application)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                            <span class="text-gray-600 font-medium">
                                                {{ strtoupper(substr($application->profile_snapshot['first_name'] ?? '', 0, 1)) }}{{ strtoupper(substr($application->profile_snapshot['name'] ?? '', 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $application->profile_snapshot['first_name'] ?? '' }} {{ $application->profile_snapshot['name'] ?? '' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $application->profile_snapshot['email'] ?? '' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $application->isFormateur() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $application->applicant_type_label }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $application->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <select
                                        wire:change="updateApplicationStatus('{{ $application->id }}', $event.target.value)"
                                        class="text-sm border border-gray-300 rounded-lg px-2 py-1 focus:ring-2 focus:ring-red-500 focus:border-red-500
                                            bg-{{ $application->status_color }}-50 text-{{ $application->status_color }}-800"
                                    >
                                        @foreach($applicationStatusOptions as $value => $label)
                                            <option value="{{ $value }}" {{ $application->status === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a
                                        href="{{ route('admin.job-application.show', $application) }}"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Voir le profil
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $applications->links() }}
            </div>
        @endif
    </div>
</div>
