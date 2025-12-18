@extends('layouts.app')

@section('title', 'Gestion des Offres d\'emploi - Simplon Africa')

@section('page-title', 'Gestion des Offres d\'emploi')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.job-offers-management')
@endsection










