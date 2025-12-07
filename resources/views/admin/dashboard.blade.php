@extends('layouts.app')

@php
    $user = auth()->user()->load('roles');
    $isSuperAdmin = $user->roles->contains('name', 'super_admin');
    $pageTitle = $isSuperAdmin ? 'Tableau de Bord Super Administrateur' : 'Tableau de Bord Administrateur';
    $title = $isSuperAdmin ? 'Dashboard Super Administrateur - Simplon Africa' : 'Dashboard Administrateur - Simplon Africa';
@endphp

@section('title', $title)

@section('page-title', $pageTitle)

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <div class="mb-4">
        <p class="text-gray-600">Vue d'ensemble des activités de la plateforme.</p>
    </div>

    <div class="flex gap-4 mb-6">
        <a href="{{ route('admin.candidatures') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            Voir tous les Dossiers
        </a>
        @if($isSuperAdmin)
            <a href="{{ route('admin.jury.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                + Créer un Jury
            </a>
        @endif
    </div>

    @livewire('admin.dashboard')
@endsection

