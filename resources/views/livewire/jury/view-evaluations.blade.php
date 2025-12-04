<div class="space-y-6">
    <!-- Header -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    Évaluations validées - Candidature
                </h2>
                <p class="text-gray-600">
                    Candidature de {{ $candidature->user->name }}
                </p>
            </div>
            @if($candidature->status === 'validated' && $candidature->badge)
                <div class="text-right">
                    <p class="text-sm text-gray-600 mb-1">Badge attribué</p>
                    <p class="text-lg font-semibold text-green-600">{{ $candidature->badge->label }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Résumé des évaluations par étape -->
    <div class="bg-white rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Résumé des évaluations par étape</h3>

        <div class="space-y-6">
            @foreach($evaluationsByStep as $stepData)
                <div class="border border-gray-200 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900 mb-3">{{ $stepData['step']->label }}</h4>

                    <div class="overflow-x-auto mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Membre</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Note totale</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Détails des critères</th>
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
                                                        @if($score->comment)
                                                            <div class="text-gray-500 italic mt-0.5">"{{ $score->comment }}"</div>
                                                        @endif
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

                    @if($stepData['president_comment'])
                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                            <p class="text-xs font-medium text-yellow-900 mb-1">Commentaire du président :</p>
                            <p class="text-sm text-yellow-800">{{ $stepData['president_comment'] }}</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <!-- Décision finale du président -->
    @php
        $finalDecision = $evaluationsByStep->firstWhere('president_decision')['president_decision'] ?? null;
        $finalComment = $evaluationsByStep->firstWhere('president_comment')['president_comment'] ?? null;
    @endphp

    @if($finalDecision && $finalComment)
        <div class="bg-white rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Décision finale du président</h3>
            <div class="p-4 rounded-lg @if($finalDecision === 'approved') bg-green-50 border border-green-200 @else bg-red-50 border border-red-200 @endif">
                <div class="flex items-center gap-3 mb-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                        @if($finalDecision === 'approved') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                        @if($finalDecision === 'approved')
                            Approuvé
                        @else
                            Rejeté
                        @endif
                    </span>
                </div>
                <p class="text-sm @if($finalDecision === 'approved') text-green-800 @else text-red-800 @endif">
                    {{ $finalComment }}
                </p>
            </div>
        </div>
    @endif
</div>
