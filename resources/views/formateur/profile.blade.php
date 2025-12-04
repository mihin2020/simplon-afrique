@extends('layouts.app')

@section('title', 'Mon Profil - Simplon Africa')

@section('page-title', 'Mon Profil')

@section('navigation')
    @include('components.formateur-navigation')
@endsection

@section('content')
    @livewire('formateur.profile')
@endsection

