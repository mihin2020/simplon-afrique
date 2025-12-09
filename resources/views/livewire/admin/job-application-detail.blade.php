<div>
    <div class="mb-6">
        <a href="{{ route('admin.job-offers.show', $application->jobOffer) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à l'offre
        </a>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne principale - Profil du candidat -->
        <div class="lg:col-span-2 space-y-6">
            <!-- En-tête du candidat -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <div class="flex items-start justify-between mb-6">
                    <div class="flex items-center gap-4">
                        <div class="h-16 w-16 bg-gray-200 rounded-full flex items-center justify-center">
                            <span class="text-2xl text-gray-600 font-medium">
                                {{ strtoupper(substr($application->profile_snapshot['first_name'] ?? '', 0, 1)) }}{{ strtoupper(substr($application->profile_snapshot['name'] ?? '', 0, 1)) }}
                            </span>
                        </div>
                        <div>
                            <h2 class="text-2xl font-semibold text-gray-900">
                                {{ $application->profile_snapshot['first_name'] ?? '' }} {{ $application->profile_snapshot['name'] ?? '' }}
                            </h2>
                            <p class="text-gray-600">{{ $application->profile_snapshot['email'] ?? '' }}</p>
                            <div class="mt-2 flex items-center gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $application->isFormateur() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $application->applicant_type_label }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    bg-{{ $application->status_color }}-100 text-{{ $application->status_color }}-800">
                                    {{ $application->status_label }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 pt-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informations de contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm font-medium text-gray-500">Email</div>
                            <div class="text-gray-900">{{ $application->profile_snapshot['email'] ?? 'N/A' }}</div>
                        </div>
                        @if(isset($application->profile_snapshot['phone']))
                            <div>
                                <div class="text-sm font-medium text-gray-500">Téléphone</div>
                                <div class="text-gray-900">{{ $application->profile_snapshot['phone'] }}</div>
                            </div>
                        @endif
                        @if(isset($application->profile_snapshot['country']))
                            <div>
                                <div class="text-sm font-medium text-gray-500">Pays</div>
                                <div class="text-gray-900">{{ $application->profile_snapshot['country'] }}</div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($application->isFormateur())
                <!-- Profil Formateur -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Profil Formateur</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        @if(isset($application->profile_snapshot['technical_profile']))
                            <div>
                                <div class="text-sm font-medium text-gray-500">Profil technique</div>
                                <div class="text-gray-900">{{ $application->profile_snapshot['technical_profile'] }}</div>
                            </div>
                        @endif
                        @if(isset($application->profile_snapshot['years_of_experience']))
                            <div>
                                <div class="text-sm font-medium text-gray-500">Expérience</div>
                                <div class="text-gray-900">
                                    @php
                                        $exp = $application->profile_snapshot['years_of_experience'];
                                        $expLabel = match($exp) {
                                            'moins_de_2_ans' => 'Moins de 2 ans',
                                            'entre_2_et_5_ans' => 'Entre 2 et 5 ans',
                                            'plus_de_5_ans' => 'Plus de 5 ans',
                                            default => $exp,
                                        };
                                    @endphp
                                    {{ $expLabel }}
                                </div>
                            </div>
                        @endif
                        @if(isset($application->profile_snapshot['organization']))
                            <div>
                                <div class="text-sm font-medium text-gray-500">Organisation</div>
                                <div class="text-gray-900">{{ $application->profile_snapshot['organization'] }}</div>
                            </div>
                        @endif
                        @if(isset($application->profile_snapshot['training_type']))
                            <div>
                                <div class="text-sm font-medium text-gray-500">Type de formation</div>
                                <div class="text-gray-900">{{ $application->profile_snapshot['training_type'] }}</div>
                            </div>
                        @endif
                        @if(isset($application->profile_snapshot['portfolio_url']))
                            <div class="md:col-span-2">
                                <div class="text-sm font-medium text-gray-500">Portfolio</div>
                                <a href="{{ $application->profile_snapshot['portfolio_url'] }}" target="_blank" class="text-red-600 hover:text-red-800">
                                    {{ $application->profile_snapshot['portfolio_url'] }}
                                </a>
                            </div>
                        @endif
                    </div>

                    @if(isset($application->profile_snapshot['certifications']) && count($application->profile_snapshot['certifications']) > 0)
                        <div>
                            <div class="text-sm font-medium text-gray-500 mb-2">Certifications</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($application->profile_snapshot['certifications'] as $certification)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-green-100 text-green-800">
                                        {{ $certification }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @if($application->cv_path)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">CV du candidat</h3>
                        <a
                            href="{{ route('admin.job-application.cv', $application) }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Télécharger le CV
                        </a>
                    </div>
                @endif
            @endif
        </div>

        <!-- Colonne latérale - Actions et offre -->
        <div class="space-y-6">
            <!-- Actions sur la candidature -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Gérer la candidature</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($statusOptions as $value => $label)
                                <button
                                    wire:click="updateStatus('{{ $value }}')"
                                    class="px-3 py-2 text-sm rounded-lg transition
                                        {{ $status === $value 
                                            ? 'bg-red-600 text-white' 
                                            : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Notes internes</label>
                        <textarea
                            id="notes"
                            wire:model="notes"
                            rows="4"
                            placeholder="Ajoutez vos notes sur ce candidat..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        ></textarea>
                        <button
                            wire:click="saveNotes"
                            class="mt-2 w-full px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                        >
                            Enregistrer les notes
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informations sur l'offre -->
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Offre concernée</h3>
                
                <div class="space-y-3">
                    <div>
                        <div class="text-sm font-medium text-gray-500">Titre</div>
                        <div class="text-gray-900 font-medium">{{ $application->jobOffer->title }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Type de contrat</div>
                        <div class="text-gray-900">{{ $application->jobOffer->contract_type_label }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Localisation</div>
                        <div class="text-gray-900">{{ $application->jobOffer->location }}</div>
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-500">Date de candidature</div>
                        <div class="text-gray-900">{{ $application->created_at->format('d/m/Y à H:i') }}</div>
                    </div>
                </div>

                <a
                    href="{{ route('admin.job-offers.show', $application->jobOffer) }}"
                    class="mt-4 inline-flex items-center gap-2 text-red-600 hover:text-red-800 text-sm"
                >
                    Voir l'offre complète
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>
