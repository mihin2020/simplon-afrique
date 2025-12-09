<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">
                    Créez et gérez les offres d'emploi pour les formateurs et administrateurs.
                </p>
            </div>
            <a
                href="{{ route('admin.job-offers.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nouvelle offre
            </a>
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

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Recherche -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher
                </label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Titre, localisation..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                >
            </div>

            <!-- Filtre Statut -->
            <div>
                <label for="statusFilter" class="block text-sm font-medium text-gray-700 mb-2">
                    Statut
                </label>
                <select
                    id="statusFilter"
                    wire:model.live="statusFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                >
                    <option value="">Tous les statuts</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtre Type de contrat -->
            <div>
                <label for="contractTypeFilter" class="block text-sm font-medium text-gray-700 mb-2">
                    Type de contrat
                </label>
                <select
                    id="contractTypeFilter"
                    wire:model.live="contractTypeFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                >
                    <option value="">Tous les types</option>
                    @foreach($contractTypeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Liste des Offres -->
    @if($jobOffers->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune offre d'emploi</h3>
            <p class="text-gray-500 mb-6">Aucune offre ne correspond à vos critères de recherche.</p>
            <a
                href="{{ route('admin.job-offers.create') }}"
                class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Créer une offre
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($jobOffers as $offer)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $offer->title }}</h3>
                                @php
                                    $statusConfig = match($offer->status) {
                                        'draft' => ['label' => 'Brouillon', 'color' => 'gray'],
                                        'published' => ['label' => 'Publiée', 'color' => 'green'],
                                        'closed' => ['label' => 'Clôturée', 'color' => 'red'],
                                        default => ['label' => $offer->status, 'color' => 'gray'],
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                                    {{ $statusConfig['label'] }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ $offer->contract_type_label }}
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">Localisation</div>
                                    <div class="text-sm text-gray-900 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $offer->location }} ({{ $offer->remote_policy_label }})
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">Date limite</div>
                                    <div class="text-sm text-gray-900 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ $offer->application_deadline->format('d/m/Y') }}
                                        @if($offer->application_deadline->isPast())
                                            <span class="text-red-600 text-xs">(expirée)</span>
                                        @endif
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">Candidatures</div>
                                    <div class="text-sm text-gray-900 flex items-center gap-1">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                        </svg>
                                        {{ $offer->applications->count() }} candidature(s)
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">Publiée le</div>
                                    <div class="text-sm text-gray-900">
                                        @if($offer->published_at)
                                            {{ $offer->published_at->format('d/m/Y à H:i') }}
                                        @else
                                            <span class="text-gray-400">Non publiée</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-gray-600 line-clamp-2">{{ Str::limit($offer->description, 200) }}</p>
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            <a
                                href="{{ route('admin.job-offers.show', $offer) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                Voir
                            </a>
                            <a
                                href="{{ route('admin.job-offers.edit', $offer) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Modifier
                            </a>
                            @if($offer->status === 'published')
                                <button
                                    wire:click="closeOffer('{{ $offer->id }}')"
                                    wire:confirm="Êtes-vous sûr de vouloir clôturer cette offre ?"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                    </svg>
                                    Clôturer
                                </button>
                            @endif
                            <button
                                wire:click="deleteOffer('{{ $offer->id }}')"
                                wire:confirm="{{ $offer->applications->count() > 0 ? 'Attention ! Cette offre a ' . $offer->applications->count() . ' candidature(s). Êtes-vous sûr de vouloir supprimer cette offre et toutes ses candidatures ? Cette action est irréversible.' : 'Êtes-vous sûr de vouloir supprimer cette offre ? Cette action est irréversible.' }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-6">
                {{ $jobOffers->links() }}
            </div>
        </div>
    @endif
</div>
