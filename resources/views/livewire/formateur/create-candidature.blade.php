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

    @if (session()->has('info'))
        <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
            {{ session('info') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Déposer une candidature</h2>
        
        <!-- Message d'avertissement -->
        <div class="mb-6 rounded-lg bg-yellow-50 border border-yellow-200 px-4 py-3">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800 mb-1">Important - À lire avant de candidater</h3>
                    <ul class="text-sm text-yellow-700 list-disc list-inside space-y-1">
                        <li>Une fois votre dossier soumis, vous ne pourrez plus le modifier.</li>
                        <li><strong>Assurez-vous que votre CV est à jour dans votre profil</strong> avant de candidater, car il sera automatiquement utilisé et ne pourra plus être modifié une fois le processus enclenché.</li>
                        <li>Vous pouvez mettre à jour votre CV dans <a href="{{ route('formateur.profile') }}" class="font-medium underline">Mon Profil</a>.</li>
                    </ul>
                </div>
            </div>
        </div>

        <p class="text-gray-600 mb-6">
            Complétez le formulaire ci-dessous pour soumettre votre candidature à la labellisation Simplon Africa.
        </p>

        @if($hasActiveCandidature)
            <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800 mb-1">Candidature en cours</h3>
                        <p class="text-sm text-blue-700">
                            Vous avez déjà une candidature en cours d'examen. Vous ne pouvez pas déposer une nouvelle candidature tant que celle-ci n'est pas terminée. Vous pouvez consulter votre candidature dans <a href="{{ route('formateur.candidatures') }}" class="font-medium underline">Mes Candidatures</a>.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit.prevent="submit" class="space-y-6" @if($hasActiveCandidature) onsubmit="return false;" @endif>
            <!-- Information sur l'attribution du badge -->
            <div class="rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h3 class="text-sm font-medium text-blue-800 mb-1">Attribution automatique du badge</h3>
                        <p class="text-sm text-blue-700">
                            Le badge (Junior, Intermédiaire ou Senior) vous sera automatiquement attribué à la fin du processus de labellisation, en fonction de la moyenne obtenue lors de l'évaluation par le jury.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Section Documents obligatoires -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Documents obligatoires</h3>
                
                <div class="space-y-6">
                    <!-- CV depuis le profil -->
                    @php
                        $userProfile = auth()->user()->formateurProfile;
                        $hasCv = $userProfile && $userProfile->cv_path;
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Curriculum Vitae (CV) <span class="text-red-600">*</span>
                        </label>
                        @if($hasCv)
                            @php
                                $cvFilename = basename($userProfile->cv_path);
                                $extension = pathinfo($cvFilename, PATHINFO_EXTENSION);
                                
                                // Gérer le format avec triple underscore (___)
                                if (strpos($cvFilename, '___') !== false) {
                                    $parts = explode('___', $cvFilename);
                                    // Prendre tout sauf la dernière partie (le hash) et ajouter l'extension
                                    $nameWithoutHash = implode('___', array_slice($parts, 0, -1));
                                    $cvDisplayName = $nameWithoutHash . '.' . $extension;
                                }
                                // Gérer le format avec double underscore (__)
                                elseif (strpos($cvFilename, '__') !== false) {
                                    $parts = explode('__', $cvFilename);
                                    $nameWithoutHash = $parts[0];
                                    $cvDisplayName = $nameWithoutHash . '.' . $extension;
                                }
                                // Format sans underscore (ancien format)
                                else {
                                    $cvDisplayName = $cvFilename;
                                }
                            @endphp
                            <div class="flex items-center gap-3 p-4 bg-green-50 border border-green-200 rounded-lg">
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-green-800">CV disponible dans votre profil</p>
                                    <p class="text-xs text-green-600">{{ $cvDisplayName }}</p>
                                </div>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">
                                Ce CV sera automatiquement utilisé pour votre candidature. 
                                <a href="{{ route('formateur.profile') }}" class="text-red-600 hover:underline">Modifier dans mon profil</a>
                            </p>
                        @else
                            <div class="flex items-center gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-red-800">CV manquant</p>
                                    <p class="text-xs text-red-600">Vous devez d'abord téléverser votre CV dans votre profil pour pouvoir candidater.</p>
                                </div>
                            </div>
                            <a href="{{ route('formateur.profile') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Téléverser mon CV dans mon profil
                            </a>
                        @endif
                    </div>

                    <!-- Lettre de motivation -->
                    <div>
                        <label for="motivationLetter" class="block text-sm font-medium text-gray-700 mb-2">
                            Lettre de motivation <span class="text-red-600">*</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="flex-1">
                                <input
                                    type="file"
                                    id="motivationLetter"
                                    wire:model="motivationLetter"
                                    accept=".pdf"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 @if($hasActiveCandidature) opacity-50 cursor-not-allowed @endif"
                                    required
                                    @if($hasActiveCandidature) disabled @endif
                                >
                                @error('motivationLetter') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                                <p class="mt-1 text-xs text-gray-500">Format accepté : PDF. Taille max : 5MB</p>
                            </div>
                            @if($motivationLetterPreview)
                                <div class="flex items-center gap-2 text-sm text-green-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $motivationLetterPreview }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section Informations complémentaires -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations complémentaires</h3>
                
                <div class="space-y-6">
                    <!-- Pièces jointes supplémentaires -->
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <label class="block text-sm font-medium text-gray-700">
                                Pièces jointes supplémentaires (optionnel)
                            </label>
                            <button
                                type="button"
                                wire:click="addAttachment"
                                class="text-sm text-red-600 hover:text-red-700 font-medium @if($hasActiveCandidature) opacity-50 cursor-not-allowed @endif"
                                @if($hasActiveCandidature) disabled @endif
                            >
                                + Ajouter une pièce jointe
                            </button>
                        </div>
                        
                        @if(count($additionalAttachments) > 0)
                            <div class="space-y-3">
                                @foreach($additionalAttachments as $index => $attachment)
                                    <div class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg">
                                        <div class="flex-1">
                                            <input
                                                type="file"
                                                wire:model="additionalAttachments.{{ $index }}"
                                                accept=".pdf,.doc,.docx"
                                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-gray-50 file:text-gray-700 hover:file:bg-gray-100 @if($hasActiveCandidature) opacity-50 cursor-not-allowed @endif"
                                                @if($hasActiveCandidature) disabled @endif
                                            >
                                            @error("additionalAttachments.{$index}") 
                                                <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> 
                                            @enderror
                                        </div>
                                        @if(isset($attachmentPreviews[$index]) && $attachmentPreviews[$index])
                                            <div class="flex items-center gap-2 text-sm text-green-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ $attachmentPreviews[$index] }}
                                            </div>
                                        @endif
                                        <button
                                            type="button"
                                            wire:click="removeAttachment({{ $index }})"
                                            class="text-red-600 hover:text-red-800"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-sm text-gray-500 italic">Aucune pièce jointe ajoutée. Vous pouvez ajouter des attestations, certifications, etc.</p>
                        @endif
                        <p class="mt-2 text-xs text-gray-500">Formats acceptés : PDF, DOC, DOCX. Taille max par fichier : 5MB</p>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end gap-4">
                <a
                    href="{{ route('formateur.dashboard') }}"
                    class="inline-flex items-center px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition"
                >
                    Annuler
                </a>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    @if($hasActiveCandidature) disabled @endif
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-gray-400 disabled:hover:bg-gray-400"
                >
                    <svg wire:loading wire:target="submit" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="submit">
                        @if($hasActiveCandidature)
                            Candidature en cours
                        @else
                            Déposer la candidature
                        @endif
                    </span>
                    <span wire:loading wire:target="submit">Dépôt en cours...</span>
                </button>
            </div>
        </form>
    </div>
</div>

            </div>
        </form>
    </div>
</div>
