@php
    $currentRoute = request()->route()->getName();
@endphp

<a href="{{ route('formateur.dashboard') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'formateur.dashboard' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
    </svg>
    Dashboard
</a>
<a href="{{ route('formateur.profile') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'formateur.profile' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
    </svg>
    Mon Profil
</a>
<a href="{{ route('formateur.create-candidature') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'formateur.create-candidature' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
    </svg>
    Déposer une candidature
</a>
<a href="{{ route('formateur.candidatures') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium {{ $currentRoute === 'formateur.candidatures' ? 'text-red-600 bg-red-50' : 'text-gray-700 hover:bg-gray-100' }} rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
    </svg>
    Mes Candidatures
</a>
<a href="#" class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-lg">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
    </svg>
    Offres d'emploi
</a>

