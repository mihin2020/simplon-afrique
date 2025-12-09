@extends('layouts.app')

@section('title', 'Mes Notes - Simplon Africa')

@section('page-title', 'Mes Notes')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900">Notes me concernant</h2>
        <p class="text-sm text-gray-600 mt-1">Consultez les notes que le super administrateur a rédigées à votre sujet</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        @php
            $notes = auth()->user()->promotionNotes()->with(['promotion', 'createdBy'])->latest()->paginate(10);
        @endphp

        @forelse($notes as $note)
            <div 
                x-data="{ open: false }"
                class="border-b border-gray-200 hover:bg-gray-50 transition-colors"
            >
                <!-- En-tête cliquable -->
                <button
                    @click="open = !open"
                    class="w-full text-left p-6 flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-inset"
                >
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $note->title }}</h3>
                        <div class="flex items-center gap-4 mt-1">
                            <span class="text-sm text-gray-500">
                                {{ $note->created_at->format('d/m/Y à H:i') }}
                            </span>
                            @if($note->promotion)
                                <span class="text-sm text-gray-500">
                                    Promotion: {{ $note->promotion->name }}
                                </span>
                            @endif
                            <span class="text-xs text-gray-400">
                                Créé par {{ $note->createdBy->first_name }} {{ $note->createdBy->name }}
                            </span>
                        </div>
                    </div>
                    <!-- Icône de flèche -->
                    <svg 
                        class="w-5 h-5 text-gray-400 transition-transform duration-200"
                        :class="{ 'rotate-180': open }"
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                <!-- Contenu pliable -->
                <div 
                    x-show="open"
                    x-collapse
                    class="px-6 pb-6"
                >
                    <div class="space-y-4 pt-2 border-t border-gray-100">
                        @if($note->difficulties)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-1">Difficultés rencontrées</h4>
                                <div class="text-sm text-gray-700 whitespace-pre-wrap bg-red-50 p-3 rounded-lg">
                                    {{ $note->difficulties }}
                                </div>
                            </div>
                        @endif

                        @if($note->recommendations)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-1">Recommandations</h4>
                                <div class="text-sm text-gray-700 whitespace-pre-wrap bg-green-50 p-3 rounded-lg">
                                    {{ $note->recommendations }}
                                </div>
                            </div>
                        @endif

                        @if($note->other)
                            <div>
                                <h4 class="text-sm font-semibold text-gray-800 mb-1">Autre</h4>
                                <div class="text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 p-3 rounded-lg">
                                    {{ $note->other }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="p-12 text-center text-sm text-gray-500">
                Aucune note vous concernant.
            </div>
        @endforelse

        <!-- Pagination -->
        @if($notes->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $notes->links() }}
            </div>
        @endif
    </div>
@endsection

