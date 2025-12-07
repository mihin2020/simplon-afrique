@extends('layouts.app')

@section('title', 'Paramètres de labellisation - Simplon Africa')

@section('page-title', 'Paramètres de labellisation')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <livewire:admin.labellisation-settings />
@endsection

