@extends('layouts.app')

@section('title', 'Gestion des Candidatures - Simplon Africa')

@section('page-title', 'Gestion des Candidatures')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.candidatures-management')
@endsection

