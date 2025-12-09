@extends('layouts.app')

@section('title', 'Badges & Attestations - Simplon Africa')

@section('page-title', 'Badges & Attestations')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <livewire:admin.badge-attestation-settings />
@endsection


