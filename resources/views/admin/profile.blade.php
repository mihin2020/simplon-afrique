@extends('layouts.app')

@php
    $user = auth()->user()->load('roles');
    $isSuperAdmin = $user->roles->contains('name', 'super_admin');
    $pageTitle = 'Mon Profil';
    $title = 'Mon Profil - Simplon Africa';
@endphp

@section('title', $title)

@section('page-title', $pageTitle)

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    @livewire('admin.profile')
@endsection


