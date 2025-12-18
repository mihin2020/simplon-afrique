<div>
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-2">Gestion des Jurys</h2>
                <p class="text-gray-600">
                    Créez et gérez les jurys pour l'évaluation des candidatures.
                </p>
            </div>
            @if($isSuperAdmin)
                <a
                    href="{{ route('admin.jury.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Créer un jury
                </a>
            @endif
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <!-- Filtres -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Recherche -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher
                </label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nom du jury ou formateur..."
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
        </div>
    </div>

    <!-- Liste des Jurys -->
    @if($juries->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucun jury</h3>
            <p class="text-gray-500 mb-6">Aucun jury ne correspond à vos critères de recherche.</p>
            @if($isSuperAdmin)
                <a
                    href="{{ route('admin.jury.create') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Créer un jury
                </a>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach($juries as $jury)
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-3">
                                <h3 class="text-xl font-semibold text-gray-900">{{ $jury->name }}</h3>
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

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">
                                        Candidature assignée
                                        @if($jury->all_candidatures->isNotEmpty())
                                            <span class="text-gray-500 font-normal">({{ $jury->all_candidatures->count() }})</span>
                                        @endif
                                    </div>
                                    @if($jury->all_candidatures->isNotEmpty())
                                        @php
                                            $maxDisplay = 3; // Nombre maximum de candidatures à afficher par défaut
                                            $candidatures = $jury->all_candidatures;
                                            $totalCount = $candidatures->count();
                                        @endphp
                                        <div x-data="{ showAll: false }" class="space-y-2">
                                            @foreach($candidatures as $candidature)
                                                <div x-show="showAll || {{ $loop->index }} < {{ $maxDisplay }}" 
                                                     x-transition
                                                     class="text-sm text-gray-900">
                                                    @php
                                                        $firstName = $candidature->user->first_name ?? '';
                                                        $lastName = $candidature->user->name ?? '';
                                                        $fullName = trim($firstName . ' ' . $lastName);
                                                    @endphp
                                                    <div class="font-medium">{{ $fullName ?: $candidature->user->name }}</div>
                                                    <div class="text-xs text-gray-500 mt-0.5">
                                                        {{ $candidature->user->email }}
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($totalCount > $maxDisplay)
                                                <button 
                                                    @click="showAll = !showAll"
                                                    class="text-xs text-red-600 hover:text-red-700 font-medium mt-1 focus:outline-none"
                                                >
                                                    <span x-show="!showAll">Voir toutes les candidatures ({{ $totalCount - $maxDisplay }} de plus)</span>
                                                    <span x-show="showAll">Voir moins</span>
                                                </button>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">Aucune candidature assignée</span>
                                    @endif
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">Nombre de membres</div>
                                    <div class="text-sm text-gray-900">
                                        {{ $jury->members->count() }} membre(s)
                                    </div>
                                </div>

                                <div>
                                    <div class="text-sm font-medium text-gray-700 mb-1">Grille d'évaluation</div>
                                    @if($jury->evaluationGrid)
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-green-100 text-green-800">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $jury->evaluationGrid->name }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">Aucune grille associée</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Membres du jury -->
                            @if($jury->members->isNotEmpty())
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="text-sm font-medium text-gray-700 mb-2">Membres du jury :</div>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($jury->members as $member)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs bg-gray-100 text-gray-700">
                                                {{ $member->user->name }}
                                                @if($member->is_president)
                                                    <span class="ml-1 text-red-600 font-medium">(Président)</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            <a
                                href="{{ route('admin.jury.detail', $jury->id) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                Gérer
                            </a>
                            @if($isSuperAdmin)
                                <button
                                    wire:click="deleteJury('{{ $jury->id }}')"
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer ce jury ?"
                                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-6">
                {{ $juries->links() }}
            </div>
        </div>
    @endif
</div>
