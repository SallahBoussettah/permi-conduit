<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <title>{{ config('app.name', 'ECF') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Load assets from manifest -->
    @php
    // Check both possible manifest locations
    $manifestPath = public_path('build/manifest.json');
    $viteManifestPath = public_path('build/.vite/manifest.json');
    $manifest = null;
    
    if (file_exists($manifestPath)) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
    } elseif (file_exists($viteManifestPath)) {
        $manifest = json_decode(file_get_contents($viteManifestPath), true);
    }
    
    $cssFile = null;
    $jsFile = null;
    
    if ($manifest) {
        // Check both possible CSS entry formats
        if (isset($manifest['resources/css/app.css']['file'])) {
            $cssFile = '/build/' . $manifest['resources/css/app.css']['file'];
        } elseif (isset($manifest['resources/css/app.css'])) {
            $cssFile = '/build/' . $manifest['resources/css/app.css'];
        }
        
        // Check both possible JS entry formats
        if (isset($manifest['resources/js/app.js']['file'])) {
            $jsFile = '/build/' . $manifest['resources/js/app.js']['file'];
        } elseif (isset($manifest['resources/js/app.js'])) {
            $jsFile = '/build/' . $manifest['resources/js/app.js'];
        }
    }
    @endphp
    
    @if($cssFile)
    <link rel="stylesheet" href="{{ $cssFile }}">
    @endif
    
    @if($jsFile)
    <script src="{{ $jsFile }}" defer></script>
    @endif
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <header class="bg-gray-900 text-white fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('images/logo.png') }}" alt="ECF Logo" class="h-10 w-auto">
                    </a>
                    <nav class="hidden md:ml-10 md:flex md:space-x-8">
                        @if(!request()->routeIs('dashboard') && !Str::startsWith(request()->route()->getName(), 'admin.') && !Str::startsWith(request()->route()->getName(), 'inspector.') && !Str::startsWith(request()->route()->getName(), 'candidate.') && !Str::startsWith(request()->route()->getName(), 'super_admin.'))
                            <a href="{{ route('home') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('home') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                {{ __('app.home') }}
                            </a>
                            <a href="{{ route('contact') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('contact') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                {{ __('app.contact') }}
                            </a>
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('dashboard') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                    {{ __('app.dashboard') }}
                                </a>
                            @endauth
                        @else
                            @auth
                                @if(Auth::user()->hasRole('candidate'))
                                    <a href="{{ route('candidate.courses.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('candidate.courses.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Courses') }}
                                    </a>
                                    <a href="{{ route('candidate.qcm-exams.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('candidate.qcm-exams.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('QCM Exams') }}
                                    </a>
                                @elseif(Auth::user()->hasRole('inspector'))
                                    <a href="{{ route('inspector.courses.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('inspector.courses.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Gérer les cours') }}
                                    </a>
                                    <a href="{{ route('inspector.qcm-papers.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('inspector.qcm-papers.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Gérer QCM') }}
                                    </a>
                                    <a href="{{ route('inspector.permit-categories.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('inspector.permit-categories.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Catégories de permis') }}
                                    </a>
                                @elseif(Auth::user()->hasRole('admin'))
                                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.users.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Gestion des utilisateurs') }}
                                    </a>
                                    <a href="{{ route('admin.inspectors') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.inspectors') || request()->routeIs('admin.register.inspector') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Inspecteurs') }}
                                    </a>
                                    <a href="{{ route('admin.qcm-reports.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.qcm-reports.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Rapports QCM') }}
                                    </a>
                                    <a href="{{ route('admin.permit-categories.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('admin.permit-categories.*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Catégories de permis') }}
                                    </a>
                                @elseif(Auth::user()->hasRole('super_admin'))
                                    <a href="{{ route('super_admin.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('super_admin.dashboard') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Tableau de bord') }}
                                    </a>
                                    <a href="{{ route('super_admin.schools') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('super_admin.schools*') ? 'border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:text-white hover:border-gray-300' }}">
                                        {{ __('Ecoles') }}
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </nav>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button type="button" aria-controls="mobile-menu" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-yellow-500">
                            <span class="sr-only">Open main menu</span>
                            <!-- Icon when menu is closed -->
                            <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <!-- Icon when menu is open -->
                            <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- User Authentication Links - Hidden on mobile -->
                    <div class="hidden md:flex items-center space-x-2">
                        @guest
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-yellow-500 rounded-md font-semibold text-xs text-yellow-500 uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('app.candidate_space') }}
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('app.inspector_space') }}
                            </a>
                        @else
                            <div class="relative">
                                <button id="user-menu-button" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-300 hover:text-white focus:outline-none transition ease-in-out duration-150">
                                    {{ Auth::user()->name }}
                                    <svg class="ml-1 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div id="user-menu" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-10">
                                    @if(!request()->routeIs('dashboard') && !Str::startsWith(request()->route()->getName(), 'admin.') && !Str::startsWith(request()->route()->getName(), 'inspector.') && !Str::startsWith(request()->route()->getName(), 'candidate.') && !Str::startsWith(request()->route()->getName(), 'super_admin.'))
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('app.dashboard') }}
                                        </a>
                                    @endif
                                    @if(Auth::user()->hasRole('candidate'))
                                        <a href="{{ route('candidate.courses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('My Courses') }}
                                        </a>
                                        <a href="{{ route('candidate.qcm-exams.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('QCM Exams') }}
                                        </a>
                                    @elseif(Auth::user()->hasRole('inspector'))
                                        <a href="{{ route('inspector.courses.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Gérer les cours') }}
                                        </a>
                                        <a href="{{ route('inspector.qcm-papers.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Gérer QCM') }}
                                        </a>
                                        <a href="{{ route('inspector.permit-categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Catégories de permis') }}
                                        </a>
                                    @elseif(Auth::user()->hasRole('admin'))
                                        <a href="{{ route('admin.users.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Gestion des utilisateurs') }}
                                        </a>
                                        <a href="{{ route('admin.inspectors') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Inspecteurs') }}
                                        </a>
                                        <a href="{{ route('admin.qcm-reports.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Rapports QCM') }}
                                        </a>
                                        <a href="{{ route('admin.permit-categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Catégories de permis') }}
                                        </a>
                                    @elseif(Auth::user()->hasRole('super_admin'))
                                        <a href="{{ route('super_admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Tableau de bord') }}
                                        </a>
                                        <a href="{{ route('super_admin.schools') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Ecoles') }}
                                        </a>
                                    @endif
                                    <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        {{ __('app.profile') }}
                                    </a>
                                    <!-- Logout Form -->
                                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                        @csrf
                                        <a href="{{ route('logout') }}" 
                                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
                                           class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('app.logout') }}
                                        </a>
                                    </form>
                                </div>
                            </div>
                        @endguest
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile menu, show/hide based on menu state -->
        <div id="mobile-menu" class="hidden md:hidden absolute w-full bg-gray-900 shadow-lg">
            <div class="pt-2 pb-3 space-y-1">
                @if(!request()->routeIs('dashboard') && !Str::startsWith(request()->route()->getName(), 'admin.') && !Str::startsWith(request()->route()->getName(), 'inspector.') && !Str::startsWith(request()->route()->getName(), 'candidate.') && !Str::startsWith(request()->route()->getName(), 'super_admin.'))
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        {{ __('app.home') }}
                    </a>
                    <a href="{{ route('contact') }}" class="{{ request()->routeIs('contact') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        {{ __('app.contact') }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            {{ __('app.dashboard') }}
                        </a>
                    @endauth
                @else
                    @auth
                        @if(Auth::user()->hasRole('candidate'))
                            <a href="{{ route('candidate.courses.index') }}" class="{{ request()->routeIs('candidate.courses.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Courses') }}
                            </a>
                            <a href="{{ route('candidate.qcm-exams.index') }}" class="{{ request()->routeIs('candidate.qcm-exams.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('QCM Exams') }}
                            </a>
                        @elseif(Auth::user()->hasRole('inspector'))
                            <a href="{{ route('inspector.courses.index') }}" class="{{ request()->routeIs('inspector.courses.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Gérer les cours') }}
                            </a>
                            <a href="{{ route('inspector.qcm-papers.index') }}" class="{{ request()->routeIs('inspector.qcm-papers.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Gérer QCM') }}
                            </a>
                            <a href="{{ route('inspector.permit-categories.index') }}" class="{{ request()->routeIs('inspector.permit-categories.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Catégories de permis') }}
                            </a>
                        @elseif(Auth::user()->hasRole('admin'))
                            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Gestion des utilisateurs') }}
                            </a>
                            <a href="{{ route('admin.inspectors') }}" class="{{ request()->routeIs('admin.inspectors') || request()->routeIs('admin.register.inspector') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Inspecteurs') }}
                            </a>
                            <a href="{{ route('admin.qcm-reports.index') }}" class="{{ request()->routeIs('admin.qcm-reports.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Rapports QCM') }}
                            </a>
                            <a href="{{ route('admin.permit-categories.index') }}" class="{{ request()->routeIs('admin.permit-categories.*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Catégories de permis') }}
                            </a>
                        @elseif(Auth::user()->hasRole('super_admin'))
                            <a href="{{ route('super_admin.dashboard') }}" class="{{ request()->routeIs('super_admin.dashboard') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Tableau de bord') }}
                            </a>
                            <a href="{{ route('super_admin.schools') }}" class="{{ request()->routeIs('super_admin.schools*') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                                {{ __('Ecoles') }}
                            </a>
                        @endif
                        <a href="{{ route('profile.edit') }}" class="{{ request()->routeIs('profile.edit') ? 'bg-gray-800 border-yellow-500 text-white' : 'border-transparent text-gray-300 hover:bg-gray-700 hover:border-gray-300 hover:text-white' }} block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                            {{ __('app.profile') }}
                        </a>
                    @endauth
                @endif
            </div>
            <div class="pt-2 pb-3 border-t border-gray-700">
                <!-- Language options in mobile menu -->
                <!-- Removed language options since we only use French -->
            </div>
            <div class="pt-4 pb-3 border-t border-gray-700">
                @guest
                    <div class="flex items-center px-4 space-x-2">
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 border border-yellow-500 rounded-md font-semibold text-xs text-yellow-500 uppercase tracking-widest hover:bg-gray-800 focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('app.candidate_space') }}
                        </a>
                        <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:border-yellow-600 focus:ring ring-yellow-300 disabled:opacity-25 transition ease-in-out duration-150">
                            {{ __('app.inspector_space') }}
                        </a>
                    </div>
                @else
                    <div class="px-4 py-2">
                        <div class="font-medium text-white">{{ Auth::user()->name }}</div>
                        <div class="text-sm text-gray-400">{{ Auth::user()->email }}</div>
                    </div>
                    <div class="mt-3 px-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                {{ __('app.logout') }}
                            </button>
                        </form>
                    </div>
                @endguest
            </div>
        </div>
    </header>

    <!-- Add padding to main content to prevent it from being hidden under the fixed header -->
    <main class="pt-16">
        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">ECF</h3>
                    <p class="text-gray-300">
                        {{ __('app.footer_description') }}
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ __('app.quick_links') }}</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="text-gray-300 hover:text-white">{{ __('app.home') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-300 hover:text-white">{{ __('app.contact') }}</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">{{ __('app.footer_contact') }}</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <span>{{ __('app.footer_phone') }}</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span>{{ __('app.footer_email') }}</span>
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ __('app.footer_address') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <p class="text-gray-400">{{ __('app.copyright') }}</p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white">{{ __('app.privacy_policy') }}</a>
                        <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white">{{ __('app.terms_of_service') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // User menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const userMenuButton = document.getElementById('user-menu-button');
            const userMenu = document.getElementById('user-menu');
            
            if (userMenuButton && userMenu) {
                userMenuButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    userMenu.classList.toggle('hidden');
                });
                
                // Close the menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!userMenuButton.contains(event.target) && !userMenu.contains(event.target)) {
                        userMenu.classList.add('hidden');
                    }
                });
            }
            
            // Mobile menu toggle
            const mobileMenuButton = document.querySelector('[aria-controls="mobile-menu"]');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function(event) {
                    event.stopPropagation(); // Prevent document click from immediately closing the menu
                    mobileMenu.classList.toggle('hidden');
                    
                    // Toggle menu icons
                    const openIcon = mobileMenuButton.querySelector('svg:first-of-type');
                    const closeIcon = mobileMenuButton.querySelector('svg:last-of-type');
                    
                    if (openIcon && closeIcon) {
                        openIcon.classList.toggle('block');
                        openIcon.classList.toggle('hidden');
                        closeIcon.classList.toggle('block');
                        closeIcon.classList.toggle('hidden');
                    }
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    // Only close if the menu is open and the click is outside the menu and menu button
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target) && !mobileMenu.classList.contains('hidden')) {
                        mobileMenu.classList.add('hidden');
                        
                        // Reset icons
                        const openIcon = mobileMenuButton.querySelector('svg:first-of-type');
                        const closeIcon = mobileMenuButton.querySelector('svg:last-of-type');
                        
                        if (openIcon && closeIcon) {
                            openIcon.classList.add('block');
                            openIcon.classList.remove('hidden');
                            closeIcon.classList.add('hidden');
                            closeIcon.classList.remove('block');
                        }
                    }
                });
            }
        });
    </script>
</body>
</html> 