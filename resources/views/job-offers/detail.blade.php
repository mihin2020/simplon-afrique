@extends('layouts.app')

@section('title', $jobOffer->title . ' - Simplon Africa')

@section('page-title', 'Détail de l\'offre')

@section('navigation')
    @php
        $user = auth()->user();
        $isFormateur = $user->roles->contains('name', 'formateur');
    @endphp
    @if($isFormateur)
        @include('components.formateur-navigation')
    @else
        @include('components.admin-navigation')
    @endif
@endsection

@section('content')
    <div class="mb-6">
        <a href="{{ route('job-offers.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour aux offres
        </a>
    </div>

    @livewire('job-offers-list', ['selectedOfferId' => $jobOffer->id])
@endsection






