@extends('layouts.app')

@section('title', 'Gestion des Organisations - Simplon Africa')

@section('page-title', 'Gestion des Organisations')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.organizations-management')
@endsection

