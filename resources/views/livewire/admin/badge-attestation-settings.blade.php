<div class="space-y-8">
    @if (session()->has('success'))
        <div class="p-4 bg-green-50 border border-green-200 rounded-lg text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <!-- Section 1: Configuration des images de badges -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                </svg>
                Images des Badges
            </h2>
            <p class="text-red-100 text-sm mt-1">Uploadez les images pour chaque niveau de badge</p>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($badges as $badge)
                    <div class="border border-gray-200 rounded-xl p-4 hover:shadow-md transition">
                        <!-- En-tête du badge -->
                        <div class="flex items-center gap-3 mb-4">
                            <span class="text-3xl">{{ $badge->getEmoji() }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $badge->label }}</h3>
                                <p class="text-xs text-gray-500">{{ $badge->min_score }} - {{ $badge->max_score }} points</p>
                            </div>
                        </div>

                        <!-- Image actuelle -->
                        <div class="mb-4">
                            @if(isset($badgeImages[$badge->id]) && $badgeImages[$badge->id])
                                <div class="relative">
                                    <img 
                                        src="{{ $badgeImages[$badge->id] }}" 
                                        alt="{{ $badge->label }}"
                                        class="w-full h-32 object-contain bg-gray-50 rounded-lg border"
                                    >
                                    <button
                                        wire:click="removeBadgeImage('{{ $badge->id }}')"
                                        class="absolute top-2 right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                                        title="Supprimer"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @elseif(isset($tempBadgeImages[$badge->id]))
                                <img 
                                    src="{{ $tempBadgeImages[$badge->id]->temporaryUrl() }}" 
                                    alt="Aperçu"
                                    class="w-full h-32 object-contain bg-gray-50 rounded-lg border"
                                >
                            @else
                                <div class="w-full h-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center">
                                    <span class="text-gray-400 text-sm">Aucune image</span>
                                </div>
                            @endif
                        </div>

                        <!-- Upload -->
                        <div class="space-y-2">
                            <input
                                type="file"
                                wire:model="tempBadgeImages.{{ $badge->id }}"
                                accept="image/*"
                                class="w-full text-sm text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200"
                            >
                            @error("tempBadgeImages.{$badge->id}")
                                <p class="text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            @if(isset($tempBadgeImages[$badge->id]))
                                <button
                                    wire:click="saveBadgeImage('{{ $badge->id }}')"
                                    class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium"
                                >
                                    Enregistrer
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Section 2: Paramètres de l'attestation -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Modèle d'Attestation
            </h2>
            <p class="text-red-100 text-sm mt-1">Configurez le contenu et les éléments visuels de l'attestation</p>
        </div>

        <form wire:submit="saveAttestationSettings" class="p-6 space-y-6">
            <!-- Informations de l'organisation -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom de l'organisation <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="organizationName"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Simplon Africa"
                    >
                    @error('organizationName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nom du directeur <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="directorName"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Prénom NOM"
                    >
                    @error('directorName')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Titre du directeur <span class="text-red-600">*</span>
                    </label>
                    <input
                        type="text"
                        wire:model="directorTitle"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Directeur Général"
                    >
                    @error('directorTitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Texte de l'attestation -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Texte de l'attestation <span class="text-red-600">*</span>
                </label>
                <textarea
                    wire:model="attestationText"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Nous certifions que le/la formateur(trice) mentionné(e) ci-dessus..."
                ></textarea>
                @error('attestationText')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">
                    Ce texte apparaîtra dans le corps de l'attestation.
                </p>
            </div>

            <!-- Logo et Signature -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Logo -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Logo de l'organisation
                    </label>
                    <div class="space-y-3">
                        @if($logoPreview)
                            <div class="relative inline-block">
                                <img 
                                    src="{{ $logoPreview }}" 
                                    alt="Logo"
                                    class="h-20 object-contain bg-gray-50 rounded-lg border p-2"
                                >
                                <button
                                    type="button"
                                    wire:click="removeLogo"
                                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                                    title="Supprimer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                        <input
                            type="file"
                            wire:model="logo"
                            accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        >
                        @error('logo')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Signature -->
<div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Signature du directeur
                    </label>
                    <div class="space-y-3">
                        @if($signaturePreview)
                            <div class="relative inline-block">
                                <img 
                                    src="{{ $signaturePreview }}" 
                                    alt="Signature"
                                    class="h-20 object-contain bg-gray-50 rounded-lg border p-2"
                                >
                                <button
                                    type="button"
                                    wire:click="removeSignature"
                                    class="absolute -top-2 -right-2 p-1 bg-red-500 text-white rounded-full hover:bg-red-600 transition"
                                    title="Supprimer"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        @endif
                        <input
                            type="file"
                            wire:model="signature"
                            accept="image/*"
                            class="w-full text-sm text-gray-500 file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                        >
                        @error('signature')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bouton de sauvegarde -->
            <div class="flex justify-end pt-4 border-t">
                <button
                    type="submit"
                    class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Enregistrer les paramètres
                </button>
            </div>
        </form>
    </div>

    <!-- Section 3: Aperçu de l'attestation -->
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-red-600 to-red-700 px-6 py-4">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Aperçu de l'Attestation
            </h2>
            <p class="text-red-100 text-sm mt-1">Visualisez le rendu final de l'attestation de labellisation</p>
        </div>

        <div class="p-6 bg-gray-100">
            <!-- Attestation en format paysage -->
            <div class="mx-auto" style="max-width: 900px; aspect-ratio: 1.414/1;">
                <!-- Cadre rouge extérieur (Simplon) -->
                <div class="w-full h-full p-3 rounded-lg shadow-2xl" style="background-color: #dc2626;">
                    <!-- Conteneur intérieur beige -->
                    <div class="w-full h-full relative overflow-hidden" style="background-color: #f8f5f0;">
                        
                        <!-- Bordure dorée intérieure -->
                        <div class="absolute inset-4 border-2 pointer-events-none" style="border-color: #c9a227;"></div>
                        
                        <!-- Coins décoratifs -->
                        <div class="absolute top-6 left-6 w-12 h-12 border-t-2 border-l-2" style="border-color: #c9a227;"></div>
                        <div class="absolute top-6 right-6 w-12 h-12 border-t-2 border-r-2" style="border-color: #c9a227;"></div>
                        <div class="absolute bottom-6 left-6 w-12 h-12 border-b-2 border-l-2" style="border-color: #c9a227;"></div>
                        <div class="absolute bottom-6 right-6 w-12 h-12 border-b-2 border-r-2" style="border-color: #c9a227;"></div>
                        
                        <!-- Points décoratifs aux coins -->
                        <div class="absolute top-7 left-7 w-2 h-2 rounded-full" style="background-color: #c9a227;"></div>
                        <div class="absolute top-7 right-7 w-2 h-2 rounded-full" style="background-color: #c9a227;"></div>
                        <div class="absolute bottom-7 left-7 w-2 h-2 rounded-full" style="background-color: #c9a227;"></div>
                        <div class="absolute bottom-7 right-7 w-2 h-2 rounded-full" style="background-color: #c9a227;"></div>
                        
                        <!-- Filigrane - Logo en arrière-plan (AGRANDI) -->
                        @if($logoPreview)
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                                <img src="{{ $logoPreview }}" alt="" class="object-contain opacity-10" style="width: 350px; height: 350px;">
                            </div>
                        @else
                            <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-10">
                                <svg class="text-gray-400" style="width: 350px; height: 350px;" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                </svg>
                            </div>
                        @endif
                        
                        <!-- Contenu de l'attestation -->
                        <div class="relative z-10 h-full flex flex-col items-center py-5 px-8">
                            
                            <!-- Titre -->
                            <div class="text-center">
                                <h1 class="text-3xl font-bold tracking-widest mb-1" style="color: #2c3e50;">ATTESTATION</h1>
                                <h2 class="text-lg tracking-wider mb-3" style="color: #2c3e50;">DE LABELLISATION</h2>
                            </div>
                            
                            <!-- Corps -->
                            <div class="text-center flex-1 flex flex-col justify-center py-2">
                                <p class="text-sm uppercase tracking-wider mb-2" style="color: #5a6a7a;">Cette attestation est décernée à :</p>
                                
                                <!-- Nom du formateur -->
                                <p class="text-3xl italic mb-1" style="color: #2c3e50; font-family: Georgia, serif;">
                                    [Prénom NOM du formateur]
                                </p>
                                
                                <!-- Ligne sous le nom -->
                                <div class="h-0.5 mx-auto mb-4" style="width: 280px; background: linear-gradient(90deg, transparent, #c9a227, #c9a227, transparent);"></div>
                                
                                <!-- Texte de certification -->
                                <p class="text-sm leading-relaxed mx-auto mb-4" style="color: #5a6a7a; max-width: 480px;">
                                    {{ $attestationText ?: 'Nous certifions que le/la formateur(trice) mentionné(e) ci-dessus a satisfait aux exigences du processus de labellisation et s\'est vu attribuer le badge correspondant à son niveau de compétences.' }}
                                </p>
                                
                                <!-- Badge attribué -->
                                @if($previewBadge && isset($badgeImages[$previewBadge->id]) && $badgeImages[$previewBadge->id])
                                    <div class="flex flex-col items-center mb-4">
                                        <img 
                                            src="{{ $badgeImages[$previewBadge->id] }}" 
                                            alt="{{ $previewBadge->label }}"
                                            class="h-20 object-contain"
                                        >
                                    </div>
                                @endif
                            </div>
                            
                            <!-- Pied - Signature à droite avec date au-dessus -->
                            <div class="w-full flex justify-end px-6 -mt-2">
                                <!-- Bloc signature à droite -->
                                <div class="text-center">
                                    <!-- Date et lieu au-dessus de la signature -->
                                    <p class="text-xs mb-2" style="color: #5a6a7a;">
                                        Fait à <span class="font-semibold" style="color: #2c3e50;">Dakar</span>, 
                                        le <span class="font-semibold" style="color: #2c3e50;">{{ now()->format('d/m/Y') }}</span>
                                    </p>
                                    <p class="text-xs italic mb-1" style="color: #5a6a7a;">Signature</p>
                                    @if($signaturePreview)
                                        <img src="{{ $signaturePreview }}" alt="Signature" class="h-8 mx-auto mb-1 object-contain">
                                    @else
                                        <div class="h-8 mb-1"></div>
                                    @endif
                                    <div class="w-36 border-t mx-auto mb-1" style="border-color: #2c3e50;"></div>
                                    <p class="text-xs font-bold uppercase" style="color: #2c3e50;">{{ $directorName ?: 'MIHIN Hugues Aimé' }}</p>
                                    <p class="text-xs" style="color: #5a6a7a;">{{ $directorTitle ?: 'Directeur Simplon Afrique' }}</p>
                                </div>
                            </div>
                            
                        </div>
                        
                    </div>
                </div>
            </div>
            
            <!-- Note explicative -->
            <p class="text-center text-sm text-gray-500 mt-4">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Cet aperçu représente l'attestation qui sera générée en PDF pour chaque formateur labellisé.
            </p>
        </div>
    </div>
</div>
