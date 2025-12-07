@extends('layouts.app')

@section('title', 'Évaluer une étape - Simplon Africa')

@section('page-title', 'Évaluation')

@section('content')
    @livewire('jury.evaluate-step', ['candidatureId' => $candidatureId, 'stepId' => $stepId])
@endsection





