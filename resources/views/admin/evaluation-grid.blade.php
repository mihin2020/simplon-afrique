@extends('layouts.app')

@section('title', 'Détail de la Grille d\'Évaluation - Simplon Africa')

@section('page-title', 'Détail de la Grille d\'Évaluation')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.evaluation-grid-detail', ['gridId' => $gridId])
@endsection

