<div>
    @if($jury)
        <!-- Message de feedback -->
        @if($message)
            <div class="mb-6 rounded-lg px-4 py-3 text-sm font-medium {{ $messageType === 'success' ? 'bg-green-100 border border-green-300 text-green-800' : 'bg-red-100 border border-red-300 text-red-800' }}">
                {{ $message }}
            </div>
        @endif

        <!-- En-tête -->
        <div class="mb-6">
            <a href="{{ route('admin.jury.detail', $jury->id) }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Retour au jury
            </a>
            <h2 class="text-2xl font-semibold text-gray-900">Ajouter des membres au jury : {{ $jury->name }}</h2>
        </div>

        @if($canManage)
            <!-- Formulaire d'ajout (super administrateur uniquement) -->
            <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ajouter un nouveau membre (administrateur)</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Sélection utilisateur -->
                    <div>
                        <label for="selectedUserId" class="block text-sm font-medium text-gray-700 mb-2">
                            Administrateur
                        </label>
                        <select
                            id="selectedUserId"
                            wire:model.live="selectedUserId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                        >
                            <option value="">-- Choisir un administrateur --</option>
                            @foreach($eligibleUsers as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            Seuls les utilisateurs avec le rôle <strong>admin</strong> peuvent être ajoutés comme membres de ce jury.
                        </p>
                    </div>

                    <!-- Sélection profil (masqué si l'utilisateur est déjà référent pédagogique) -->
                    @if(!$this->selectedUserIsReferent)
                        <div>
                            <label for="selectedRole" class="block text-sm font-medium text-gray-700 mb-2">
                                Profil dans le jury
                            </label>
                            <select
                                id="selectedRole"
                                wire:model="selectedRole"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                            >
                                @foreach($roleOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Profil dans le jury
                            </label>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                                Référent Pédagogique (déjà référent)
                            </div>
                            <p class="mt-1 text-xs text-gray-500">
                                Cet administrateur est déjà référent pédagogique, son profil est automatiquement défini.
                            </p>
                            <input type="hidden" wire:model="selectedRole" value="referent_pedagogique">
                        </div>
                    @endif

                    <!-- Bouton -->
                    <div class="flex items-end">
                        <button
                            type="button"
                            wire:click="storeMember"
                            class="w-full px-4 py-2 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
                        >
                            <span wire:loading.remove wire:target="storeMember">
                                Enregistrer le membre
                            </span>
                            <span wire:loading wire:target="storeMember">
                                Enregistrement...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Info pour les autres administrateurs : vue seule -->
            <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-lg px-4 py-3 mb-6">
                Seul le <strong>super administrateur</strong> peut constituer ou modifier les membres du jury.
                Vous pouvez uniquement consulter la composition actuelle.
            </div>
        @endif

        <!-- Liste des membres actuels en cartes -->
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">
                Membres du jury ({{ $jury->members->count() }})
            </h3>

            @if($jury->members->isEmpty())
                <div class="text-center py-12 text-gray-500">
                    <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <p class="font-medium">Aucun membre dans ce jury pour le moment.</p>
                    <p class="text-sm mt-2">Utilisez le formulaire ci-dessus pour ajouter des membres.</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($jury->members as $member)
                        <div wire:key="member-{{ $member->id }}" class="relative bg-white border border-gray-200 rounded-xl p-4 hover:shadow-md transition-shadow">
                            <!-- Badge Président en haut à droite -->
                            @if($member->is_president)
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                                        </svg>
                                        Président
                                    </span>
                                </div>
                            @endif

                            <!-- Avatar et nom -->
                            <div class="flex flex-col items-center text-center mb-4">
                                <div class="h-16 w-16 rounded-full bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center mb-3 ring-4 ring-red-100">
                                    <span class="text-2xl font-bold text-white">
                                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                                    </span>
                                </div>
                                <h4 class="font-semibold text-gray-900 text-sm mb-1">
                                    {{ $member->user->name }}
                                </h4>
                                <p class="text-xs text-gray-500 truncate w-full px-2">
                                    {{ $member->user->email }}
                                </p>
                            </div>

                            <!-- Profil -->
                            <div class="space-y-2">
                                <div class="text-center">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ $roleOptions[$member->role] ?? $member->role }}
                                    </span>
                                </div>

                                <!-- Définir comme président (super admin uniquement) -->
                                @if($canManage && !$member->is_president)
                                    <div class="text-center pt-2">
                                        <label class="inline-flex items-center cursor-pointer group">
                                            <input
                                                type="radio"
                                                name="president"
                                                value="{{ $member->id }}"
                                                wire:click="togglePresident('{{ $member->id }}')"
                                                class="w-4 h-4 text-yellow-600 border-gray-300 focus:ring-yellow-500 cursor-pointer"
                                            >
                                            <span class="ml-2 text-xs font-medium text-gray-600 group-hover:text-yellow-700 transition">
                                                Définir président
                                            </span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500">Jury non trouvé.</p>
        </div>
    @endif
</div>
