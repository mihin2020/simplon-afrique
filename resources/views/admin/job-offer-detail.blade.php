@extends('layouts.app')

@section('title', 'Détail de l\'offre - Simplon Africa')

@section('page-title', 'Détail de l\'offre d\'emploi')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.job-offer-detail', ['jobOfferId' => $jobOfferId])
@endsection


