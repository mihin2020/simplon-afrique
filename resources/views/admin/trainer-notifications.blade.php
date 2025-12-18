@extends('layouts.app')

@php
    $user = auth()->user()->load('roles');
    $isSuperAdmin = $user->roles->contains('name', 'super_admin');
@endphp

@section('title', 'Notifications formateurs - Simplon Africa')

@section('page-title', 'Notifications formateurs')

@section('navigation')
    @include('components.admin-navigation')
@endsection

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-gray-600">
                    Créez une information qui sera affichée sur le dashboard des formateurs, sous leur nom et prénom.
                </p>
                <p class="text-sm text-gray-500">
                    Une seule notification active est prise en compte : la plus récente marquée comme active et encore dans sa date limite.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Nouvelle notification
            </h2>

            <form action="{{ route('admin.trainer-notifications.store') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                        Titre
                    </label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="{{ old('title') }}"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500"
                        required
                    >
                    @error('title')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description / Message
                    </label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500"
                        required
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="deadline_at" class="block text-sm font-medium text-gray-700 mb-1">
                            Date limite (optionnel)
                        </label>
                        <input
                            type="date"
                            id="deadline_at"
                            name="deadline_at"
                            value="{{ old('deadline_at') }}"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500"
                        >
                        @error('deadline_at')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center mt-6">
                        <input
                            type="checkbox"
                            id="is_active"
                            name="is_active"
                            value="1"
                            class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                            {{ old('is_active', true) ? 'checked' : '' }}
                        >
                        <label for="is_active" class="ml-2 text-sm text-gray-700">
                            Activer immédiatement cette notification
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 transition"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Créer la notification
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">
                Historique des notifications
            </h2>

            @if($notifications->isEmpty())
                <p class="text-sm text-gray-500">
                    Aucune notification n'a encore été créée.
                </p>
            @else
                <div class="space-y-3">
                    @foreach($notifications as $notification)
                        <div class="border border-gray-200 rounded-lg p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-sm font-semibold text-gray-900">
                                        {{ $notification->title }}
                                    </h3>
                                    @if($notification->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                            Inactive
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-700 mb-1">
                                    {{ $notification->description }}
                                </p>
                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500">
                                    <span>Créée le {{ $notification->created_at->format('d/m/Y H:i') }}</span>
                                    @if($notification->deadline_at)
                                        <span>· Date limite : {{ $notification->deadline_at->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <form
                                    action="{{ route('admin.trainer-notifications.toggle', $notification) }}"
                                    method="POST"
                                    class="inline"
                                >
                                    @csrf
                                    @method('PATCH')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border
                                            {{ $notification->is_active ? 'border-gray-300 text-gray-700 hover:bg-gray-50' : 'border-green-500 text-green-700 hover:bg-green-50' }}"
                                    >
                                        @if($notification->is_active)
                                            Désactiver
                                        @else
                                            Activer
                                        @endif
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
@endsection


