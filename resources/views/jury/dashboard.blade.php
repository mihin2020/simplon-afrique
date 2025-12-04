@extends('layouts.app')

@section('title', 'Dashboard Jury - Simplon Africa')

@section('page-title', 'Dashboard Jury')

@section('navigation')
    <a href="{{ route('jury.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 rounded-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
        </svg>
        Tableau de Bord
    </a>
@endsection

@section('content')
    @livewire('jury.dashboard')
@endsection

