@extends('layouts.app')

@section('title', 'Créer une Grille d\'Évaluation - Simplon Africa')

@section('page-title', 'Créer une Grille d\'Évaluation')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.evaluation-grid-form')
@endsection

