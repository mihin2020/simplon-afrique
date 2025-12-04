<div>
    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('message'))
        <div class="mb-6 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
            {{ session('message') }}
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Mon Profil</h2>

        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Section Photo de profil -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Photo de profil</h3>
                
                <div class="flex items-center gap-6">
                    @if($photoPreview)
                        <div class="relative">
                            <img src="{{ $photoPreview }}" alt="Photo de profil" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200">
                        </div>
                    @else
                        <div class="w-32 h-32 rounded-full bg-gray-200 flex items-center justify-center border-4 border-gray-300">
                            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    @endif

                    <div class="flex-1">
                        <label for="photo" class="block text-sm font-medium text-gray-700 mb-2">
                            Choisir une photo
                        </label>
                        <input
                            type="file"
                            id="photo"
                            wire:model="photo"
                            accept="image/*"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100"
                        >
                        @error('photo') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                        <p class="mt-1 text-xs text-gray-500">Formats acceptés : JPG, PNG. Taille max : 2MB</p>
                    </div>
                </div>
            </div>

            <!-- Section Informations personnelles -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informations personnelles</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nom -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            placeholder="Votre nom"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            required
                        >
                        @error('name') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Prénom -->
                    <div>
                        <label for="firstName" class="block text-sm font-medium text-gray-700 mb-1">
                            Prénom
                        </label>
                        <input
                            type="text"
                            id="firstName"
                            wire:model="firstName"
                            placeholder="Votre prénom"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        >
                        @error('firstName') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div class="md:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="email"
                            id="email"
                            wire:model="email"
                            placeholder="votre.email@exemple.com"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            required
                        >
                        @error('email') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Téléphone -->
                    <div>
                        <label for="phoneCountryCode" class="block text-sm font-medium text-gray-700 mb-1">
                            Code pays
                        </label>
                        <select
                            id="phoneCountryCode"
                            wire:model="phoneCountryCode"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            size="1"
                        >
                            <option value="">Sélectionner un code</option>
                            @foreach($phoneCountryCodes as $phoneCode)
                                <option value="{{ $phoneCode['code'] }}">
                                    {{ $phoneCode['flag'] }} {{ $phoneCode['code'] }} - {{ $phoneCode['country'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('phoneCountryCode') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="phoneNumber" class="block text-sm font-medium text-gray-700 mb-1">
                            Numéro de téléphone
                        </label>
                        <input
                            type="text"
                            id="phoneNumber"
                            wire:model="phoneNumber"
                            placeholder="06 12 34 56 78"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        >
                        @error('phoneNumber') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Pays -->
                    <div>
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">
                            Pays
                        </label>
                        <select
                            id="country"
                            wire:model="country"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            size="1"
                        >
                            <option value="">Sélectionner un pays</option>
                            @foreach($countries as $countryItem)
                                <option value="{{ $countryItem['name'] }}">
                                    {{ $countryItem['flag'] }} {{ $countryItem['name'] }}
                                </option>
                            @endforeach
                        </select>
                        @error('country') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Années d'expérience -->
                    <div>
                        <label for="yearsOfExperience" class="block text-sm font-medium text-gray-700 mb-1">
                            Années d'expérience
                        </label>
                        <select
                            id="yearsOfExperience"
                            wire:model="yearsOfExperience"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        >
                            <option value="">Sélectionner une expérience</option>
                            @foreach($experienceOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('yearsOfExperience') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Section Profil technique -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Profil technique</h3>
                
                <div>
                    <label for="technicalProfile" class="block text-sm font-medium text-gray-700 mb-1">
                        Profil technique
                    </label>
                    <input
                        type="text"
                        id="technicalProfile"
                        wire:model="technicalProfile"
                        placeholder="Développeur Full Stack, Data Scientist, etc."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    >
                    @error('technicalProfile') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Section Portfolio -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Portfolio</h3>
                
                <div>
                    <label for="portfolioUrl" class="block text-sm font-medium text-gray-700 mb-1">
                        Lien vers votre portfolio
                    </label>
                    <input
                        type="url"
                        id="portfolioUrl"
                        wire:model="portfolioUrl"
                        placeholder="https://votre-portfolio.com"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    >
                    @error('portfolioUrl') <span class="text-sm text-red-600 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Section Certifications -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Certifications</h3>
                
                <!-- Certifications sélectionnées -->
                @if(count($selectedCertificationsList) > 0)
                    <div class="mb-4 flex flex-wrap gap-2">
                        @foreach($selectedCertificationsList as $certification)
                            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                {{ $certification->name }}
                                <button
                                    type="button"
                                    wire:click="toggleCertification('{{ $certification->id }}')"
                                    class="text-red-600 hover:text-red-800"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif

                <!-- Recherche et ajout de certifications -->
                <div class="space-y-3">
                    <div class="flex gap-2">
                        <div class="flex-1 relative">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="certificationSearch"
                                placeholder="Rechercher ou ajouter une certification..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            >
                            @if($certificationSearch)
                                <div class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                                    @forelse($availableCertifications as $certification)
                                        <button
                                            type="button"
                                            wire:click="toggleCertification('{{ $certification->id }}')"
                                            class="w-full text-left px-4 py-2 hover:bg-gray-100 {{ in_array($certification->id, $selectedCertifications) ? 'bg-red-50' : '' }}"
                                        >
                                            <div class="flex items-center justify-between">
                                                <span>{{ $certification->name }}</span>
                                                @if(in_array($certification->id, $selectedCertifications))
                                                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                        </button>
                                    @empty
                                        <div class="px-4 py-2 text-sm text-gray-500">
                                            Aucune certification trouvée. Appuyez sur "Ajouter" pour créer "{{ $certificationSearch }}"
                                        </div>
                                    @endforelse
                                </div>
                            @endif
                        </div>
                        @if($certificationSearch && !$availableCertifications->contains('name', $certificationSearch))
                            <button
                                type="button"
                                wire:click="addNewCertification"
                                class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                            >
                                Ajouter
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="flex justify-end gap-4">
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg wire:loading wire:target="save" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save">Enregistrer</span>
                    <span wire:loading wire:target="save">Enregistrement...</span>
                </button>
            </div>
        </form>
    </div>
</div>
