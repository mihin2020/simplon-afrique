<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Validation président - Candidature
                </h2>
                <p class="text-gray-600">
                    Candidature de {{ $candidature->user->name }}
                </p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
        </div>
    @endif

    <!-- Résumé des notes -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé des évaluations par étape</h3>

        <div class="space-y-6">
            @foreach($evaluationsByStep as $stepData)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">{{ $stepData['step']->label }}</h4>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Membre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note totale</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Critères</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($stepData['evaluations'] as $evaluation)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            {{ $evaluation->juryMember->user->name }}
                                            @if($evaluation->juryMember->is_president)
                                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Président
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm font-semibold text-gray-900">
                                            {{ number_format($evaluation->member_total_score ?? 0, 3) }}/20
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-600">
                                            <div class="space-y-1 max-h-32 overflow-y-auto">
                                                @foreach($evaluation->scores as $score)
                                                    <div class="text-xs">
                                                        <span class="font-medium">{{ $score->criterion->name }}:</span>
                                                        <span>{{ number_format($score->raw_score, 1) }}/20</span>
                                                        <span class="text-gray-400">(pondéré: {{ number_format($score->weighted_score, 3) }})</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900" colspan="2">Note moyenne de l'étape</td>
                                    <td class="px-4 py-3 text-sm font-bold text-blue-600">
                                        {{ number_format($stepData['average_score'], 3) }}/20
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Note finale et badge proposé -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-blue-900 mb-1">Note finale calculée</p>
                <p class="text-3xl font-bold text-blue-700">{{ number_format($finalScore, 3) }}/20</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-blue-900 mb-1">Badge proposé</p>
                @if($proposedBadge)
                    <p class="text-xl font-semibold text-blue-700">{{ $proposedBadge->label }}</p>
                    <p class="text-xs text-blue-600">Seuil: {{ $proposedBadge->min_score }}-{{ $proposedBadge->max_score }}/20</p>
                @else
                    <p class="text-lg font-semibold text-red-600">Aucun badge (note insuffisante)</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Formulaire de validation -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Commentaire général et décision</h3>

        <form wire:submit="approve">
            <div class="mb-4">
                <label for="presidentComment" class="block text-sm font-medium text-gray-700 mb-2">
                    Commentaire général <span class="text-red-600">*</span>
                </label>
                <textarea
                    id="presidentComment"
                    wire:model="presidentComment"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"
                    placeholder="Ajoutez un commentaire général sur l'évaluation de cette candidature..."
                    required
                ></textarea>
                @error('presidentComment')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-4">
                <button
                    type="button"
                    wire:click="reject"
                    wire:confirm="Êtes-vous sûr de vouloir rejeter cette candidature ?"
                    class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition font-medium"
                >
                    Rejeter
                </button>
                <button
                    type="submit"
                    @if(!$proposedBadge) disabled @endif
                    class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium @if(!$proposedBadge) opacity-50 cursor-not-allowed @endif"
                >
                    Approuver et attribuer le badge
                </button>
            </div>
        </form>
    </div>
</div>
