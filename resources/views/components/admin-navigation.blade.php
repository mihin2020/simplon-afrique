@php
    $currentRoute = request()->route()->getName();
    $user = auth()->user()->load('roles');
    $isSuperAdmin = $user->roles->contains('name', 'super_admin');
@endphp

<a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'admin.dashboard' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Tableau de bord
</a>

<a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'admin.users' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
    </svg>
    Gestion des utilisateurs
</a>

<a href="{{ route('admin.candidatures') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'admin.candidatures' || $currentRoute === 'admin.candidature.show' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    Gestion des candidatures
</a>

<a href="{{ route('admin.juries') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ str_starts_with($currentRoute, 'admin.jury') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
    </svg>
    Jurys
</a>

<a href="{{ route('admin.certifications') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'admin.certifications' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
    </svg>
    Certifications
</a>

@if($isSuperAdmin)
    <a href="{{ route('admin.evaluation-grids') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ str_starts_with($currentRoute, 'admin.evaluation-grid') ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
        </svg>
        Grilles d'évaluation
    </a>
@endif


