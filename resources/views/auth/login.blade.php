<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Formateur - Simplon Africa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind via CDN pour le prototypage UI -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex">
    <!-- Colonne image -->
    <div class="hidden lg:flex w-1/2 relative min-h-screen">
        <img
            src="https://images.pexels.com/photos/1181352/pexels-photo-1181352.jpeg?auto=compress&cs=tinysrgb&w=1260&h=750&dpr=2"
            alt="Formateurs en collaboration"
            class="w-full h-full object-cover"
        >
        <div class="absolute inset-0 bg-black/30"></div>
    </div>

    <!-- Colonne formulaire -->
    <div class="flex-1 flex items-center justify-center px-4 sm:px-8 lg:px-16">
        <div class="w-full max-w-md">
            <div class="mb-8 text-center">
                <div class="flex justify-center items-center mb-6">
                    <img
                        src="{{ asset('images/simplon-logo.jpg') }}"
                        alt="Simplon Africa"
                        class="h-16 w-auto"
                    >
                </div>
                <h1 class="text-4xl font-semibold text-gray-900 mb-2">Connexion Formateur</h1>
                <p class="text-sm text-gray-500">
                    Bienvenue sur votre espace. Connectez-vous pour suivre votre labellisation.
                </p>
            </div>

            @if ($errors->any())
                <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <div class="space-y-1">
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Adresse email
                    </label>
                    <div class="relative">
                        <input
                            id="email"
                            name="email"
                            type="email"
                            required
                            autofocus
                            value="{{ old('email') }}"
                            class="block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-red-600 focus:ring-2 focus:ring-red-100 focus:outline-none"
                            placeholder="Entrez votre adresse email"
                        >
                    </div>
                </div>

                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Mot de passe
                        </label>
                        <a href="#" class="text-xs font-medium text-red-600 hover:text-red-700">
                            Mot de passe oublié ?
                        </a>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            class="block w-full rounded-xl border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-red-600 focus:ring-2 focus:ring-red-100 focus:outline-none"
                            placeholder="Entrez votre mot de passe"
                        >
                    </div>
                </div>

                <button
                    type="submit"
                    class="w-full inline-flex justify-center items-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 transition"
                >
                    Se connecter
                </button>
            </form>

            <p class="mt-6 text-center text-xs text-gray-500">
                Première connexion formateur ?
                <a href="#" class="font-medium text-red-600 hover:text-red-700">
                    Créer mon mot de passe
                </a>
            </p>
        </div>
    </div>
</body>
</html>


