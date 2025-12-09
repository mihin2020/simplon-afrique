<div>
    <div class="mb-6">
        <a href="{{ route('admin.job-offers') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste
        </a>
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

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => $el.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100)">
            <div class="flex items-start gap-2">
                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-red-800 mb-2">Veuillez corriger les erreurs suivantes :</h4>
                    <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <button @click="show = false" class="text-red-400 hover:text-red-600 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <div>
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations sur le poste</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Titre du poste -->
                <div class="md:col-span-2">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titre du poste <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="title"
                        wire:model="title"
                        placeholder="Ex: Formateur Développement Web"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('title') border-red-500 @enderror"
                    >
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type de contrat -->
                <div>
                    <label for="contractType" class="block text-sm font-medium text-gray-700 mb-2">
                        Type de contrat <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="contractType"
                        wire:model="contractType"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('contractType') border-red-500 @enderror"
                    >
                        @foreach($contractTypeOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('contractType')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Localisation -->
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                        Localisation <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="location"
                        wire:model="location"
                        placeholder="Ex: Paris, Dakar, Casablanca..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('location') border-red-500 @enderror"
                    >
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Politique de télétravail -->
                <div>
                    <label for="remotePolicy" class="block text-sm font-medium text-gray-700 mb-2">
                        Télétravail <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="remotePolicy"
                        wire:model="remotePolicy"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('remotePolicy') border-red-500 @enderror"
                    >
                        @foreach($remotePolicyOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('remotePolicy')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date limite -->
                <div>
                    <label for="applicationDeadline" class="block text-sm font-medium text-gray-700 mb-2">
                        Date limite de candidature <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="date"
                        id="applicationDeadline"
                        wire:model="applicationDeadline"
                        min="{{ now()->addDay()->format('Y-m-d') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('applicationDeadline') border-red-500 @enderror"
                    >
                    @error('applicationDeadline')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description du poste <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="6"
                        placeholder="Décrivez les missions principales et les responsabilités..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('description') border-red-500 @enderror"
                    ></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Profil recherché</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Niveau d'expérience -->
                <div>
                    <label for="experienceYears" class="block text-sm font-medium text-gray-700 mb-2">
                        Niveau d'expérience requis <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="experienceYears"
                        wire:model="experienceYears"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('experienceYears') border-red-500 @enderror"
                    >
                        <option value="">Sélectionner...</option>
                        @foreach($experienceOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('experienceYears')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Formation minimale -->
                <div>
                    <label for="minimumEducation" class="block text-sm font-medium text-gray-700 mb-2">
                        Diplôme / Formation minimale <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="minimumEducation"
                        wire:model="minimumEducation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('minimumEducation') border-red-500 @enderror"
                    >
                        <option value="">Sélectionner...</option>
                        @foreach($educationOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('minimumEducation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Compétences requises -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Compétences clés requises <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-3">
                        @foreach($requiredSkills as $index => $skill)
                            <div class="flex items-center gap-2">
                                <input
                                    type="text"
                                    wire:model="requiredSkills.{{ $index }}"
                                    placeholder="Ex: PHP, Laravel, JavaScript..."
                                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                                >
                                @if(count($requiredSkills) > 1)
                                    <button
                                        type="button"
                                        wire:click="removeSkill({{ $index }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <button
                        type="button"
                        wire:click="addSkill"
                        class="mt-3 inline-flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Ajouter une compétence
                    </button>
                    @error('requiredSkills')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('requiredSkills.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Informations complémentaires</h3>

            <div class="space-y-6">
                <!-- Informations additionnelles -->
                <div>
                    <label for="additionalInfo" class="block text-sm font-medium text-gray-700 mb-2">
                        Autres informations (optionnel)
                    </label>
                    <textarea
                        id="additionalInfo"
                        wire:model="additionalInfo"
                        rows="4"
                        placeholder="Avantages, conditions particulières, processus de recrutement..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 @error('additionalInfo') border-red-500 @enderror"
                    ></textarea>
                    @error('additionalInfo')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pièce jointe -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pièce jointe (PDF ou Image, optionnel)
                    </label>
                    
                    @if($existingAttachment || $attachment)
                        <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg mb-3">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    @if($attachment)
                                        Nouveau fichier sélectionné
                                    @else
                                        Fichier actuel
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500">
                                    @if($attachment)
                                        {{ $attachment->getClientOriginalName() }}
                                    @else
                                        {{ basename($existingAttachment) }}
                                    @endif
                                </p>
                            </div>
                            @if($existingAttachment && !$attachment)
                                <button
                                    type="button"
                                    wire:click="removeAttachment"
                                    class="px-3 py-1 text-sm text-red-600 hover:bg-red-50 rounded transition"
                                >
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-8 h-8 mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mb-2 text-sm text-gray-500">
                                    <span class="font-semibold">Cliquez pour télécharger</span> ou glissez-déposez
                                </p>
                                <p class="text-xs text-gray-500">PDF, JPG, JPEG ou PNG (max. 10 Mo)</p>
                            </div>
                            <input
                                type="file"
                                wire:model="attachment"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="hidden"
                            >
                        </label>
                    </div>
                    
                    @if($attachment)
                        <div class="mt-3 flex items-center gap-2 text-sm text-green-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Fichier sélectionné : {{ $attachment->getClientOriginalName() }}
                        </div>
                    @endif
                    
                    @error('attachment')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a
                href="{{ route('admin.job-offers') }}"
                class="px-6 py-3 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
                Annuler
            </a>
            <button
                type="button"
                wire:click="saveDraft"
                class="px-6 py-3 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="saveDraft" class="flex items-center gap-2">
                    Enregistrer en brouillon
                </span>
                <span wire:loading wire:target="saveDraft" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-gray-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Enregistrement...
                </span>
            </button>
            <button
                type="button"
                wire:click="publish"
                class="px-6 py-3 text-white bg-red-600 rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="publish" class="flex items-center gap-2">
                    {{ $isEditing && $jobOffer?->isPublished() ? 'Mettre à jour' : 'Publier l\'offre' }}
                </span>
                <span wire:loading wire:target="publish" class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Publication en cours...
                </span>
            </button>
        </div>
    </div>
</div>

<script>
    // Faire défiler vers le premier champ avec erreur quand il y a des erreurs
    document.addEventListener('livewire:init', () => {
        Livewire.on('validation-failed', () => {
            setTimeout(() => {
                const firstError = document.querySelector('.border-red-500');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
            }, 100);
        });
    });
</script>
