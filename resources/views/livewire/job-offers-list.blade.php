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

    <!-- En-tête -->
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Offres d'emploi disponibles</h2>
        <p class="text-gray-600">
            Découvrez les opportunités de carrière chez Simplon Africa et postulez en un clic.
        </p>
    </div>

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

            <!-- Filtre Télétravail -->
            <div>
                <label for="remotePolicyFilter" class="block text-sm font-medium text-gray-700 mb-2">
                    Télétravail
                </label>
                <select
                    id="remotePolicyFilter"
                    wire:model.live="remotePolicyFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                >
                    <option value="">Toutes les options</option>
                    @foreach($remotePolicyOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Liste des offres -->
    @if($jobOffers->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune offre disponible</h3>
            <p class="text-gray-500">Aucune offre d'emploi ne correspond à vos critères de recherche.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($jobOffers as $offer)
                <div class="bg-white rounded-xl shadow-sm overflow-hidden hover:shadow-md transition">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">
                                    {{ $offer->title }}
                                </h3>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $offer->location }}
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap gap-2 mb-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $offer->contract_type_label }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $offer->remote_policy_label }}
                            </span>
                        </div>

                        <p class="text-sm text-gray-600 line-clamp-3 mb-4">
                            {{ Str::limit($offer->description, 150) }}
                        </p>

                        <div class="flex flex-wrap gap-1 mb-4">
                            @foreach(array_slice($offer->required_skills, 0, 3) as $skill)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-red-50 text-red-700">
                                    {{ $skill }}
                                </span>
                            @endforeach
                            @if(count($offer->required_skills) > 3)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-gray-50 text-gray-600">
                                    +{{ count($offer->required_skills) - 3 }}
                                </span>
                            @endif
                        </div>

                        <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Date limite : {{ $offer->application_deadline->format('d/m/Y') }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button
                                wire:click="showOfferDetail('{{ $offer->id }}')"
                                class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
                            >
                                Voir détails
                            </button>
                            @if(in_array($offer->id, $appliedOfferIds))
                                <span class="flex-1 px-4 py-2 text-sm font-medium text-center text-green-700 bg-green-100 rounded-lg">
                                    Déjà postulé
                                </span>
                            @else
                                <button
                                    wire:click="apply('{{ $offer->id }}')"
                                    wire:confirm="Êtes-vous sûr de vouloir postuler à cette offre ? {{ $isFormateur ? 'Votre CV et profil seront automatiquement transmis.' : 'Vos informations de profil seront transmises.' }}"
                                    wire:loading.attr="disabled"
                                    wire:target="apply('{{ $offer->id }}')"
                                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="apply('{{ $offer->id }}')">Postuler</span>
                                    <span wire:loading wire:target="apply('{{ $offer->id }}')">Envoi...</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $jobOffers->links() }}
        </div>
    @endif

    <!-- Modal de détail de l'offre -->
    @if($selectedOffer)
        <div 
            class="fixed inset-0 z-50 overflow-y-auto"
            x-data="{ show: true }"
            x-show="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        >
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <!-- Overlay -->
                <div 
                    class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                    wire:click="closeOfferDetail"
                ></div>

                <!-- Modal -->
                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex items-start justify-between mb-6">
                            <div>
                                <h3 class="text-2xl font-semibold text-gray-900 mb-2">
                                    {{ $selectedOffer->title }}
                                </h3>
                                <div class="flex items-center gap-4 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        </svg>
                                        {{ $selectedOffer->location }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $selectedOffer->contract_type_label }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ $selectedOffer->remote_policy_label }}
                                    </span>
                                </div>
                            </div>
                            <button
                                wire:click="closeOfferDetail"
                                class="text-gray-400 hover:text-gray-600 transition"
                            >
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-500 mb-1">Expérience requise</div>
                                <div class="text-gray-900">{{ $selectedOffer->experience_years }}</div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="text-sm font-medium text-gray-500 mb-1">Formation minimale</div>
                                <div class="text-gray-900">{{ $selectedOffer->minimum_education }}</div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Compétences requises</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($selectedOffer->required_skills as $skill)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-red-100 text-red-800">
                                        {{ $skill }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Description du poste</h4>
                            <div class="prose prose-sm max-w-none text-gray-600 max-h-48 overflow-y-auto">
                                {!! nl2br(e($selectedOffer->description)) !!}
                            </div>
                        </div>

                        @if($selectedOffer->additional_info)
                            <div class="mb-6">
                                <h4 class="text-sm font-medium text-gray-700 mb-2">Informations complémentaires</h4>
                                <div class="prose prose-sm max-w-none text-gray-600">
                                    {!! nl2br(e($selectedOffer->additional_info)) !!}
                                </div>
                            </div>
                        @endif

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center gap-2 text-yellow-800">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium">Date limite de candidature :</span>
                                <span>{{ $selectedOffer->application_deadline->format('d/m/Y') }}</span>
                                <span class="text-sm">({{ $selectedOffer->application_deadline->diffForHumans() }})</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        @if(in_array($selectedOffer->id, $appliedOfferIds))
                            <span class="w-full inline-flex justify-center items-center rounded-lg px-4 py-2 bg-green-100 text-green-700 font-medium sm:w-auto">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Vous avez déjà postulé
                            </span>
                        @else
                            <button
                                wire:click="apply('{{ $selectedOffer->id }}')"
                                wire:confirm="Êtes-vous sûr de vouloir postuler à cette offre ? {{ $isFormateur ? 'Votre CV et profil seront automatiquement transmis.' : 'Vos informations de profil seront transmises.' }}"
                                wire:loading.attr="disabled"
                                wire:target="apply('{{ $selectedOffer->id }}')"
                                class="w-full inline-flex justify-center rounded-lg px-4 py-2 bg-red-600 text-white font-medium hover:bg-red-700 transition sm:w-auto disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="apply('{{ $selectedOffer->id }}')">
                                    Postuler maintenant
                                </span>
                                <span wire:loading wire:target="apply('{{ $selectedOffer->id }}')">
                                    Envoi en cours...
                                </span>
                            </button>
                        @endif
                        <button
                            wire:click="closeOfferDetail"
                            class="mt-3 w-full inline-flex justify-center rounded-lg px-4 py-2 bg-white text-gray-700 font-medium border border-gray-300 hover:bg-gray-50 transition sm:mt-0 sm:w-auto"
                        >
                            Fermer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
