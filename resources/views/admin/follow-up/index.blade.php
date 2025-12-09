@extends('layouts.app')

@section('title', 'Suivi des Administrateurs - Simplon Africa')

@section('page-title', 'Suivi des Administrateurs')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.follow-up-management')
    @if(isset($selectedAdmin))
        @livewire('admin.admin-notes-detail', ['admin' => $selectedAdmin], key('admin-notes-'.$selectedAdmin->id))
    @endif
@endsection


