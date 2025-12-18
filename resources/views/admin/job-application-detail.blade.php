@extends('layouts.app')

@section('title', 'Détail de la candidature - Simplon Africa')

@section('page-title', 'Détail de la candidature')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.job-application-detail', ['applicationId' => $applicationId])
@endsection










