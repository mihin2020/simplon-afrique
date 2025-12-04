@extends('layouts.app')

@section('title', 'Gestion des Grilles d\'Évaluation - Simplon Africa')

@section('page-title', 'Gestion des Grilles d\'Évaluation')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <div class="mb-4">
        <p class="text-gray-600">Gérez les grilles d'évaluation et leurs critères pour l'évaluation des formateurs.</p>
    </div>

    @livewire('admin.evaluation-grids-management')
@endsection

