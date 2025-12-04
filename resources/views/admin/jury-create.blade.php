@extends('layouts.app')

@section('title', 'Créer un jury - Simplon Africa')

@section('page-title', 'Créer un jury')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.jury-create')
@endsection

