@extends('layouts.app')

@section('title', 'Offres d\'emploi - Simplon Africa')

@section('page-title', 'Offres d\'emploi')

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
    @livewire('job-offers-list')
@endsection


