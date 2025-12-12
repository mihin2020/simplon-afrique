@extends('layouts.app')

@section('title', isset($jobOfferId) ? 'Modifier l\'offre - Simplon Africa' : 'Nouvelle offre d\'emploi - Simplon Africa')

@section('page-title', isset($jobOfferId) ? 'Modifier l\'offre d\'emploi' : 'Nouvelle offre d\'emploi')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.job-offer-form', ['jobOfferId' => $jobOfferId ?? null])
@endsection









