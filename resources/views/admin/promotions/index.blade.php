@extends('layouts.app')

@section('title', 'Gestion des Promotions - Simplon Africa')

@section('page-title', 'Gestion des Promotions')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.promotions-management')
@endsection



