<div>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Gestion des Dossiers</h2>
        <p class="text-gray-600">
            Consultez et gérez toutes les candidatures déposées par les formateurs.
        </p>
    </div>

    <!-- Filtres et Recherche -->
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <!-- Recherche -->
            <div class="lg:col-span-2">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher
                </label>
                <input
                    type="text"
                    id="search"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Nom ou email du formateur..."
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

        <!-- Bouton Réinitialiser -->
        @if($search || $statusFilter || $badgeFilter || $stepFilter)
            <div class="mt-4">
                <button
                    wire:click="resetFilters"
                    class="text-sm text-red-600 hover:text-red-700 font-medium"
                >
                    Réinitialiser les filtres
                </button>
            </div>
        @endif
    </div>

    <!-- Liste des Candidatures -->
    @if($candidatures->isEmpty())
        <div class="bg-white rounded-xl shadow-sm p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Aucune candidature</h3>
            <p class="text-gray-500">Aucune candidature ne correspond à vos critères de recherche.</p>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Formateur
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Étape actuelle
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Statut
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Badge attribué
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date de dépôt
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($candidatures as $candidature)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
                                            <span class="text-sm font-medium text-red-600">
                                                {{ strtoupper(substr($candidature->user->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $candidature->user->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $candidature->user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $currentStepNumber = $candidature->getCurrentStepNumber();
                                        $totalSteps = $candidature->getTotalSteps();
                                        $currentStepLabel = $candidature->getCurrentStepLabel();
                                        
                                        // Si la candidature est validée, la progression est à 100%
                                        if ($candidature->status === 'validated') {
                                            $progressPercent = 100;
                                        } else {
                                            $progressPercent = $totalSteps > 0 ? (($currentStepNumber - 1) / $totalSteps) * 100 : 0;
                                        }
                                        
                                        // Couleur selon l'avancement
                                        $stepColor = match(true) {
                                            $candidature->status === 'validated' => 'green',
                                            $candidature->status === 'rejected' => 'red',
                                            $currentStepNumber === 1 => 'blue',
                                            $currentStepNumber === $totalSteps => 'green',
                                            default => 'yellow',
                                        };
                                    @endphp
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold bg-{{ $stepColor }}-100 text-{{ $stepColor }}-700">
                                                {{ $currentStepNumber }}
                                            </span>
                                            <span class="text-sm text-gray-900">{{ $currentStepLabel }}</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-1.5">
                                            <div class="bg-{{ $stepColor }}-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $progressPercent }}%"></div>
                                        </div>
                                        <span class="text-xs text-gray-500">Étape {{ $currentStepNumber }}/{{ $totalSteps }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusConfig = match($candidature->status) {
                                            'draft' => ['label' => 'Brouillon', 'color' => 'gray'],
                                            'submitted' => ['label' => 'Soumise', 'color' => 'blue'],
                                            'in_review' => ['label' => 'En examen', 'color' => 'yellow'],
                                            'validated' => ['label' => 'Validée', 'color' => 'green'],
                                            'rejected' => ['label' => 'Rejetée', 'color' => 'red'],
                                            default => ['label' => $candidature->status, 'color' => 'gray'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                                        {{ $statusConfig['label'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($candidature->status === 'validated' && $candidature->badge)
                                        <div class="flex items-center gap-2">
                                            <span class="text-xl">{{ $candidature->badge->getEmoji() }}</span>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ str_replace('Label ', '', $candidature->badge->label) }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $candidature->created_at->format('d/m/Y à H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a
                                        href="{{ route('admin.candidature.show', $candidature->id) }}"
                                        class="text-red-600 hover:text-red-900"
                                    >
                                        Voir le dossier
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $candidatures->links() }}
            </div>
        </div>
    @endif
</div>
