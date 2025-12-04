@extends('layouts.app')

@section('title', 'Mes Candidatures - Simplon Africa')

@section('page-title', 'Mes Candidatures')

@section('navigation')
    @include('components.formateur-navigation')
@endsection

@section('content')
    @livewire('formateur.my-candidatures')
@endsection

