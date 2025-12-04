@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs - Simplon Africa')

@section('page-title', 'Gestion des Utilisateurs')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.user-management')
@endsection

