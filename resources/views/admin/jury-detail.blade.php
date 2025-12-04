@extends('layouts.app')

@section('title', 'Gestion du jury - Simplon Africa')

@section('page-title', 'Gestion du jury')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
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

    <!-- En-tête -->
    <div class="mb-6">
        <a
            href="{{ route('admin.juries') }}"
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-900 mb-4"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Retour à la liste
        </a>
        <h2 class="text-2xl font-semibold text-gray-900">Gestion du jury : {{ $jury->name }}</h2>
    </div>

    @include('livewire.admin.jury-detail', ['jury' => $jury, 'isSuperAdmin' => $isSuperAdmin, 'availableCandidatures' => $availableCandidatures, 'evaluationsData' => $evaluationsData])
@endsection

