@extends('layouts.app')

@section('title', 'Ajouter un membre au jury - Simplon Africa')

@section('page-title', 'Ajouter un membre au jury')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.jury-add-member', ['juryId' => $juryId])
@endsection



