@extends('layouts.app')

@section('title', 'Dashboard Formateur - Simplon Africa')

@section('page-title', 'Dashboard')

@section('navigation')
    @include('components.formateur-navigation')
@endsection

@section('content')
    @livewire('formateur.dashboard')
@endsection

