@extends('layouts.app')

@section('title', 'Déposer une candidature - Simplon Africa')

@section('page-title', 'Déposer une candidature')

@section('navigation')
    @include('components.formateur-navigation')
@endsection

@section('content')
    @livewire('formateur.create-candidature')
@endsection

