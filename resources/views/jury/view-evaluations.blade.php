@extends('layouts.app')

@section('title', 'Voir les évaluations - Simplon Africa')

@section('page-title', 'Évaluations')

@section('content')
    @livewire('jury.view-evaluations', ['candidatureId' => $candidatureId])
@endsection













