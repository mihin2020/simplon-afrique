@extends('layouts.app')

@section('title', 'Validation président - Simplon Africa')

@section('page-title', 'Validation président')

@section('content')
    @livewire('jury.president-validation', ['candidatureId' => $candidatureId])
@endsection


