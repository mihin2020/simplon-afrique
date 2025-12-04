@extends('layouts.app')

@section('title', 'Gestion des Jurys - Simplon Africa')

@section('page-title', 'Gestion des Jurys')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.juries-management')
@endsection

