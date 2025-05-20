@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        
        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-gray-900 to-gray-800 rounded-xl shadow-xl overflow-hidden mb-8">
            <div class="md:flex md:items-center">
                <div class="p-8 md:w-2/3">
                    <div class="uppercase tracking-wide text-yellow-500 font-semibold">
                        @if(Auth::user()->role)
                            {{ Auth::user()->role->name }}
                        @else
                            {{ __('User') }}
                        @endif
                    </div>
                    <h1 class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-white sm:text-4xl">
                        {{ __('Bienvenue') }}, {{ Auth::user()->name }}!
                    </h1>
                    <p class="mt-3 max-w-md text-gray-300">
                        {{ __('Accédez à votre tableau de bord personnalisé pour gérer vos activités et ressources.') }}
                    </p>
                </div>
                <div class="md:w-1/3 flex justify-center p-8">
                    <div class="bg-yellow-500 rounded-full p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if(Auth::user()->role && Auth::user()->role->name === 'admin')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @elseif(Auth::user()->role && Auth::user()->role->name === 'inspector')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            @elseif(Auth::user()->role && Auth::user()->role->name === 'super_admin')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            @endif
                        </svg>
                    </div>
                </div>
            </div>
        </div>
        
        @if(!Auth::user()->role)
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md mb-8">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700">
                            {{ __('Votre rôle n\'a pas été assigné. Veuillez contacter un administrateur.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Dashboard Content -->
                @if(Auth::user()->role)
                    @if(Auth::user()->role->name === 'super_admin')
                        <!-- Super Admin Redirect -->
                        <script>
                            window.location.href = "{{ route('super_admin.dashboard') }}";
                        </script>
                        <div class="p-8 text-center">
                            <p>{{ __('Redirection vers le tableau de bord de l\'administrateur...') }}</p>
                            <p class="mt-4">
                                <a href="{{ route('super_admin.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Cliquez ici si vous n\'êtes pas redirigé') }}
                                </a>
                            </p>
                        </div>
                    @elseif(Auth::user()->role->name === 'admin')
                        <!-- Admin Dashboard -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Administration') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <a href="{{ route('admin.users.index') }}" class="block">
                                    <div class="p-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            </div>
                                            <div class="ml-5">
                                                <h3 class="text-lg font-medium text-gray-900">{{ __('Gestion des utilisateurs') }}</h3>
                                                <p class="mt-1 text-sm text-gray-500">{{ __('Gérer tous les utilisateurs du système.') }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-6">
                                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                {{ __('Gérer les utilisateurs') }}
                                            </a>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <!-- Permit Categories Card -->
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <a href="{{ route('admin.permit-categories.index') }}" class="block">
                                    <div class="p-6">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                                <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                            </div>
                                            <div class="ml-5">
                                                <h3 class="text-lg font-medium text-gray-900">{{ __('Catégories de permis') }}</h3>
                                                <p class="mt-1 text-sm text-gray-500">{{ __('Gérer les catégories de permis de conduire (C, CE, D, etc.).') }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-6">
                                            <a href="{{ route('admin.permit-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                                {{ __('Gérer les catégories de permis') }}
                                            </a>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Inscription de l\'inspecteur') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Créer et gérer les comptes d\'inspecteurs.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.register.inspector') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            {{ __('Inscription de l\'inspecteur') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- QCM Reports Section -->
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Module QCM') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Rapports QCM') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Voir les résultats des examens des candidats et les statistiques.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.qcm-reports.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('Voir les rapports') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Performance des candidats') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Voir l\'historique des examens des candidats.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.qcm-reports.candidates') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('Voir les candidats') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-red-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Export des données') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Exporter les résultats des examens QCM pour la création de rapports.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.qcm-reports.export') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            {{ __('Exporter les rapports') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        
                    @elseif(Auth::user()->role->name === 'inspector')
                        <!-- Inspector Dashboard -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Outils de l\'inspecteur') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Gestion des cours
') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Créer et gérer des supports de cours.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('inspector.courses.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('Gérer les cours') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- QCM Papers Card -->
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Gestion du QCM') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Créer et gérer des QCM et des questions.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('inspector.qcm-papers.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            {{ __('Gérer les QCM') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Permit Categories Card -->
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Catégories de permis') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Gérer les catégories de permis de conduire (C, CE, D, etc.).') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('inspector.permit-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            {{ __('Gérer les catégories de permis') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Profile Settings Card -->
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Paramètres du profil') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Mettre à jour vos informations personnelles.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('Modifier le profil') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        
                    @else
                        <!-- Candidate Dashboard -->
                <div class="space-y-8">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Ressources d\'apprentissage') }}</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Mes cours') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Accéder à vos supports de cours.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('candidate.courses.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            {{ __('Voir les cours') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('QCM Exams') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Passer des examens à choix multiple et voir les résultats.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('candidate.qcm-exams.available') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            {{ __('Exams disponibles') }}
                                        </a>
                                        <a href="{{ route('candidate.qcm-exams.index') }}" class="mt-2 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            {{ __('Mon historique d\'examens') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Mon profil') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Mettre à jour vos informations personnelles.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('Modifier le profil') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    @endif
                @endif
    </div>
</div>
@endsection
