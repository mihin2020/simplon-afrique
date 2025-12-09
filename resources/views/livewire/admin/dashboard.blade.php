<div class="space-y-6">
    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Formateurs -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Total Formateurs</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalFormateurs }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Badges Décernés -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Badges Décernés</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalBadges }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Dossiers en Cours -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Dossiers en Cours</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $totalDossiersEnCours }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Jurys Constitués -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 mb-1">Jurys Constitués</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $jurysConstitués }}</p>
                </div>
                <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Répartition des Badges -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Répartition des Badges</h3>
            <div class="space-y-4">
                @foreach($badgeDistribution as $badge)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full 
                                @if($badge['name'] === 'Formateur Junior') bg-blue-500
                                @elseif($badge['name'] === 'Formateur Intermédiaire') bg-green-500
                                @else bg-red-500
                                @endif"></div>
                            <span class="text-sm font-medium text-gray-700">{{ $badge['name'] }}</span>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-semibold text-gray-900">{{ $badge['count'] }}</div>
                            <div class="text-xs text-gray-500">{{ $badge['percentage'] }}%</div>
                        </div>
                    </div>
                @endforeach
                @if($totalBadges > 0)
                    <div class="pt-4 border-t border-gray-200">
                        <div class="text-sm font-semibold text-gray-900">Total : {{ $totalBadges }}</div>
                    </div>
                @else
                    <div class="text-sm text-gray-500 text-center py-4">Aucun badge décerné pour le moment</div>
                @endif
            </div>
        </div>

        <!-- Alertes et Tâches en Attente -->
        <div class="space-y-4">
            @if($nouveauxFormateurs > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-full bg-yellow-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-1">Nouveaux Formateurs à Valider</h4>
                            <p class="text-sm text-gray-600 mb-3">
                                {{ $nouveauxFormateurs }} formateur(s) attendent votre approbation.
                            </p>
                            <a href="#" class="text-sm font-medium text-red-600 hover:text-red-700">
                                Voir la liste →
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            @if($dossiersEnAttente > 0)
                <div class="bg-pink-50 border border-pink-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="h-10 w-10 rounded-full bg-pink-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-1">Dossiers en Attente</h4>
                            <p class="text-sm text-gray-600 mb-3">
                                {{ $dossiersEnAttente }} dossier(s) nécessitent votre validation finale.
                            </p>
                            <a href="#" class="text-sm font-medium text-red-600 hover:text-red-700">
                                Traiter les dossiers →
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Activité Récente -->
    @if($activiteRecente->count() > 0)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Activité Récente</h3>
            <div class="space-y-4">
                @foreach($activiteRecente as $candidature)
                    <div class="flex items-start gap-3 pb-4 border-b border-gray-200 last:border-0 last:pb-0">
                        <div class="h-8 w-8 rounded-full 
                            @if($candidature->badge) bg-green-100 @else bg-blue-100 @endif
                            flex items-center justify-center flex-shrink-0">
                            @if($candidature->badge)
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                @if($candidature->badge)
                                    <strong>{{ $candidature->user->name }}</strong> a obtenu le badge <strong>{{ $candidature->badge->label }}</strong>.
                                @else
                                    Jury pour <strong>{{ $candidature->user->name }}</strong> a été constitué.
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $candidature->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
