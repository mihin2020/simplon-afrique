<div class="space-y-6">
    <!-- Welcome Section -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    @php
                        $user = auth()->user();
                        $firstName = trim($user->first_name ?? '');
                        $lastName = trim($user->name ?? '');
                        
                        if (!empty($firstName) && !empty($lastName)) {
                            $fullName = $firstName . ' ' . $lastName;
                        } elseif (!empty($firstName)) {
                            $fullName = $firstName;
                        } elseif (!empty($lastName)) {
                            $fullName = $lastName;
                        } else {
                            $fullName = 'Formateur';
                        }
                    @endphp
                    Bienvenue, {{ $fullName }} !
                </h2>
                <p class="text-gray-600">
                    Voici votre progression et les dernières opportunités.
                </p>
            </div>
            @php
                $user = auth()->user();
                $hasActiveCandidature = $user->candidatures()
                    ->whereIn('status', ['draft', 'submitted', 'in_review'])
                    ->exists();
            @endphp
            @if(!$hasActiveCandidature)
                <a
                    href="{{ route('formateur.create-candidature') }}"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Déposer une candidature
                </a>
            @endif
        </div>
    </div>

    <!-- Badge Status Card -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center gap-4">
            @php
                $statusConfig = $candidature ? match($candidature->status) {
                    'draft' => ['label' => 'Brouillon', 'color' => 'gray', 'bg' => 'bg-gray-100', 'icon' => 'draft'],
                    'submitted' => ['label' => 'Soumise', 'color' => 'blue', 'bg' => 'bg-blue-100', 'icon' => 'submitted'],
                    'in_review' => ['label' => 'En examen', 'color' => 'yellow', 'bg' => 'bg-yellow-100', 'icon' => 'in_review'],
                    'validated' => ['label' => 'Validée', 'color' => 'green', 'bg' => 'bg-green-100', 'icon' => 'validated'],
                    'rejected' => ['label' => 'Rejetée', 'color' => 'red', 'bg' => 'bg-red-100', 'icon' => 'rejected'],
                    default => ['label' => $candidature->status, 'color' => 'gray', 'bg' => 'bg-gray-100', 'icon' => 'draft'],
                } : ['label' => 'Aucune candidature', 'color' => 'gray', 'bg' => 'bg-gray-100', 'icon' => 'none'];
            @endphp
            <div class="h-16 w-16 rounded-full {{ $statusConfig['bg'] }} flex items-center justify-center">
                @if($isCertified)
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                @elseif($candidature && $candidature->status === 'in_review')
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @elseif($candidature && $candidature->status === 'submitted')
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
            </div>
            <div class="flex-1">
                <div class="text-sm text-gray-500 mb-1">Statut de la candidature</div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="text-2xl font-bold text-gray-900">
                        @if($isCertified)
                            {{ $currentBadge ? str_replace('Label ', '', $currentBadge->label) : 'Certifié' }}
                        @else
                            {{ $statusConfig['label'] }}
                        @endif
                    </div>
                    @if($candidature)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                            bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                            {{ $statusConfig['label'] }}
                        </span>
                    @endif
                </div>
                @if($candidature && $candidature->currentStep)
                    <p class="text-sm text-gray-600 mb-1">
                        <span class="font-medium">Étape actuelle :</span> {{ $candidature->currentStep->label }}
                    </p>
                @endif
                <p class="text-sm text-gray-600">
                    @if($isCertified)
                        Félicitations ! Votre candidature a été validée et vous êtes maintenant certifié.
                    @elseif($candidature && $candidature->status === 'in_review')
                        Votre candidature est actuellement en cours d'examen. Un jury a été assigné pour l'évaluer.
                    @elseif($candidature && $candidature->status === 'submitted')
                        Votre candidature a été soumise avec succès et est en attente de validation.
                    @elseif($candidature && $candidature->status === 'draft')
                        Votre candidature est encore en brouillon. Complétez-la et soumettez-la pour qu'elle soit examinée.
                    @elseif($candidature && $candidature->status === 'rejected')
                        Votre candidature a été rejetée. Vous pouvez créer une nouvelle candidature si vous le souhaitez.
                    @else
                        Vous n'avez pas encore de candidature active.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Certification Journey Timeline -->
    <div class="bg-white rounded-xl shadow-sm p-6" x-data="{ openStep: null }">
        <h3 class="text-lg font-semibold text-gray-900 mb-6">
            Votre parcours de certification
        </h3>
        
        @if($stepsWithStatus->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <p>Aucune étape de certification définie pour le moment.</p>
                <p class="text-sm mt-2">Déposez votre candidature pour commencer le parcours.</p>
            </div>
        @else
            <div class="relative">
                <div class="flex items-start justify-between gap-4 overflow-x-auto pb-4">
                    @foreach($stepsWithStatus as $index => $item)
                        @php
                            $status = $item['status'];
                            $isCompleted = $status === 'completed';
                            $isInProgress = $status === 'in_progress';
                            $isRejected = $status === 'rejected';
                            $isPending = $status === 'pending';
                            $hasComments = isset($item['comments']) && count($item['comments']) > 0;
                        @endphp
                        
                        <div class="flex items-start flex-1 min-w-[140px] relative">
                            <div class="flex flex-col items-center w-full">
                                <!-- Icon cliquable -->
                                <button 
                                    type="button"
                                    @click="openStep = openStep === {{ $index }} ? null : {{ $index }}"
                                    class="relative z-10 flex-shrink-0 mb-3 cursor-pointer transition-transform hover:scale-110 focus:outline-none"
                                    title="Cliquez pour voir les détails"
                                >
                                    @if($isCompleted)
                                        <div class="h-12 w-12 rounded-full bg-green-500 flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    @elseif($isRejected)
                                        <div class="h-12 w-12 rounded-full bg-red-500 flex items-center justify-center shadow-lg">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </div>
                                    @elseif($isInProgress)
                                        <div class="h-12 w-12 rounded-full bg-yellow-500 flex items-center justify-center shadow-lg animate-pulse">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                            </svg>
                                        </div>
                                    @endif
                                    @if($hasComments)
                                        <span class="absolute -top-1 -right-1 h-4 w-4 bg-blue-500 rounded-full flex items-center justify-center">
                                            <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </span>
                                    @endif
                                </button>
                                
                                <!-- Connecting Line -->
                                @if(!$loop->last)
                                    <div class="absolute top-6 left-1/2 h-0.5 w-full {{ $isCompleted ? 'bg-green-500' : ($isInProgress ? 'bg-yellow-500' : 'bg-gray-300') }}" style="left: calc(50% + 24px); width: calc(100% - 48px);"></div>
                                @endif
                                
                                <!-- Content -->
                                <button 
                                    type="button"
                                    @click="openStep = openStep === {{ $index }} ? null : {{ $index }}"
                                    class="text-center w-full cursor-pointer hover:bg-gray-50 rounded-lg p-2 transition focus:outline-none"
                                >
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">{{ $item['label'] }}</h4>
                                    
                                    {{-- Badge de statut --}}
                                    @if($isCompleted)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium text-green-700 bg-green-100 rounded">Terminé</span>
                                    @elseif($isRejected)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium text-red-700 bg-red-100 rounded">Rejeté</span>
                                    @elseif($isInProgress)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium text-yellow-700 bg-yellow-100 rounded">En cours</span>
                                    @else
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium text-gray-700 bg-gray-100 rounded">En attente</span>
                                    @endif
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            {{-- Panneau de commentaires --}}
            @foreach($stepsWithStatus as $index => $item)
                <div 
                    x-show="openStep === {{ $index }}"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform -translate-y-2"
                    x-transition:enter-end="opacity-100 transform translate-y-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 transform translate-y-0"
                    x-transition:leave-end="opacity-0 transform -translate-y-2"
                    class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200"
                    style="display: none;"
                >
                    <div class="flex items-center justify-between mb-3">
                        <h5 class="font-semibold text-gray-900">{{ $item['label'] }}</h5>
                        <button @click="openStep = null" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    
                    @if(isset($item['comments']) && count($item['comments']) > 0)
                        <div class="space-y-3">
                            @foreach($item['comments'] as $comment)
                                <div class="bg-white p-3 rounded-lg border border-gray-100">
                                    <p class="text-sm text-gray-700">{{ $comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500 italic">Aucun commentaire pour cette étape.</p>
                    @endif
                </div>
            @endforeach
        @endif
    </div>

    {{-- Section badge si validé --}}
    @if($candidature && $candidature->status === 'validated')
        <div class="bg-gradient-to-br from-green-50 to-emerald-100 rounded-xl shadow-lg overflow-hidden border border-green-200">
            <!-- En-tête avec confettis virtuels -->
            <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-6 py-4 text-center">
                <h3 class="text-xl font-bold text-white flex items-center justify-center gap-2">
                    Félicitations ! Vous êtes certifié(e) !
                </h3>
            </div>
            
            <div class="p-6">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    @if($currentBadge)
                        <!-- Image du badge ou icône par défaut -->
                        <div class="flex-shrink-0">
                            @if($currentBadge->configuration && $currentBadge->configuration->image_path)
                                <img 
                                    src="{{ asset('storage/' . $currentBadge->configuration->image_path) }}" 
                                    alt="{{ $currentBadge->label }}"
                                    class="w-32 h-32 object-contain drop-shadow-lg"
                                >
                            @else
                                <div class="w-32 h-32 rounded-full bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg">
                                    <span class="text-6xl">{{ $currentBadge->getEmoji() }}</span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Informations du badge -->
                        <div class="flex-1 text-center md:text-left">
                            <p class="text-sm font-medium text-green-700 uppercase tracking-wide mb-1">Badge obtenu</p>
                            <h4 class="text-3xl font-bold text-gray-900 mb-2">{{ $currentBadge->label }}</h4>
                            
                            @if($candidature->badge_awarded_at)
                                <p class="text-sm text-gray-600 mb-4">
                                    Certifié le {{ \Carbon\Carbon::parse($candidature->badge_awarded_at)->format('d/m/Y à H:i') }}
                                </p>
                            @endif
                            
                            <!-- Boutons de téléchargement -->
                            <div class="flex flex-wrap gap-3">
                                <!-- Bouton télécharger attestation -->
                                @if($candidature->attestation_path || ($candidature->status === 'validated' && $currentBadge))
                                    <a 
                                        href="{{ route('formateur.attestation.download', $candidature) }}"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 transition shadow-md hover:shadow-lg"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Télécharger l'attestation
                                    </a>
                                @endif
                                
                                <!-- Bouton télécharger le badge -->
                                @if($currentBadge && $currentBadge->configuration && $currentBadge->configuration->image_path)
                                    <a 
                                        href="{{ route('formateur.badge.download', $candidature) }}"
                                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition shadow-md hover:shadow-lg"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Télécharger le badge
                                    </a>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Message si badge non encore déterminé -->
                        <div class="flex-1 text-center">
                            <p class="text-lg font-semibold text-gray-700 mb-2">Certification validée</p>
                            <p class="text-sm text-gray-600">Le badge est en cours de détermination...</p>
                        </div>
                    @endif
                </div>
                
                <!-- Message de félicitations -->
                @if($currentBadge)
                    <div class="mt-6 p-4 bg-white/60 rounded-lg border border-green-200">
                        <p class="text-gray-700 text-center">
                            @if($currentBadge->name === 'senior')
                                En tant que <strong>Formateur Senior</strong>, vous faites partie de l'élite de nos formateurs. Vous pouvez désormais mentorer d'autres formateurs et participer aux jurys d'évaluation.
                            @elseif($currentBadge->name === 'intermediaire')
                                En tant que <strong>Formateur Intermédiaire</strong>, vous avez démontré des compétences solides. Continuez à progresser pour atteindre le niveau Senior !
                            @else
                                En tant que <strong>Formateur Junior</strong>, vous avez franchi la première étape de votre parcours. Développez vos compétences pour évoluer vers les niveaux supérieurs !
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Latest Job Opportunities -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">
                Dernières offres d'emploi
            </h3>
            <a href="{{ route('job-offers.index') }}" class="text-sm font-medium text-red-600 hover:text-red-700">
                Voir tout
            </a>
        </div>

        <!-- Filtres -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="jobOfferContractFilter" class="block text-sm font-medium text-gray-700 mb-2">
                    Type de contrat
                </label>
                <select
                    id="jobOfferContractFilter"
                    wire:model.live="jobOfferContractFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                >
                    <option value="">Tous les types</option>
                    <option value="cdi">CDI</option>
                    <option value="cdd">CDD</option>
                    <option value="stage">Stage</option>
                    <option value="alternance">Alternance</option>
                    <option value="freelance">Freelance</option>
                </select>
            </div>
            <div>
                <label for="jobOfferRemoteFilter" class="block text-sm font-medium text-gray-700 mb-2">
                    Télétravail
                </label>
                <select
                    id="jobOfferRemoteFilter"
                    wire:model.live="jobOfferRemoteFilter"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 text-sm"
                >
                    <option value="">Toutes les options</option>
                    <option value="sur_site">Sur site</option>
                    <option value="hybride">Hybride</option>
                    <option value="full_remote">Full remote</option>
                </select>
            </div>
        </div>
        
        @if($jobOffers->isEmpty())
            <div class="text-center py-8 text-gray-500">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <p class="text-sm">Aucune offre d'emploi disponible pour le moment.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($jobOffers as $offer)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-red-300 transition">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 mb-1">{{ $offer->title }}</h4>
                                <p class="text-sm text-gray-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    {{ $offer->location }} ({{ $offer->remote_policy_label }})
                                </p>
                            </div>
                            <span class="px-3 py-1 text-xs font-medium text-white bg-red-600 rounded-full">
                                {{ $offer->contract_type_label }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                            {{ Str::limit($offer->description, 120) }}
                        </p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1 text-xs text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Date limite : {{ $offer->application_deadline->format('d/m/Y') }}
                            </div>
                            <div class="flex items-center gap-2">
                                @if(in_array($offer->id, $appliedOfferIds))
                                    <span class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-lg">
                                        Déjà postulé
                                    </span>
                                @else
                                    <a
                                        href="{{ route('job-offers.detail', $offer) }}"
                                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition"
                                    >
                                        Voir les détails
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($jobOffers->hasPages())
                <div class="mt-6">
                    {{ $jobOffers->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
