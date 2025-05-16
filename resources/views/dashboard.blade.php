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
                        {{ __('Welcome back') }}, {{ Auth::user()->name }}!
                    </h1>
                    <p class="mt-3 max-w-md text-gray-300">
                        {{ __('Access your personalized dashboard to manage your activities and resources.') }}
                    </p>
                </div>
                <div class="md:w-1/3 flex justify-center p-8">
                    <div class="bg-yellow-500 rounded-full p-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-24 w-24 text-gray-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            @if(Auth::user()->role && Auth::user()->role->name === 'admin')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            @elseif(Auth::user()->role && Auth::user()->role->name === 'inspector')
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
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
                            {{ __('Your role has not been assigned. Please contact an administrator.') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Dashboard Content -->
                @if(Auth::user()->role)
                    @if(Auth::user()->role->name === 'admin')
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
                                                <h3 class="text-lg font-medium text-gray-900">{{ __('User Management') }}</h3>
                                                <p class="mt-1 text-sm text-gray-500">{{ __('Manage all system users.') }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-6">
                                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                {{ __('Manage Users') }}
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Inspector Registration') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Create and manage inspector accounts.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('admin.register.inspector') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            {{ __('Register Inspector') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-white overflow-hidden shadow-lg rounded-lg hover:shadow-xl transition-shadow duration-300">
                                <div class="p-6">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                        </div>
                                        <div class="ml-5">
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('System Analytics') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('View system statistics and reports.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('View Analytics') }}
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
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Inspector Tools') }}</h2>
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Course Management') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Create and manage course materials.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('inspector.courses.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                            {{ __('Manage Courses') }}
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Permit Categories') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Manage driver permit categories (C, CE, D, etc.).') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('inspector.permit-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            {{ __('Manage Permit Categories') }}
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('Profile Settings') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Update your personal information.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('Edit Profile') }}
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
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">{{ __('Learning Resources') }}</h2>
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('My Courses') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Access your learning materials.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('candidate.courses.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                                            {{ __('View Courses') }}
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('QCM Practice') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Practice with multiple choice questions.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="#" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            {{ __('Start Practice') }}
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
                                            <h3 class="text-lg font-medium text-gray-900">{{ __('My Profile') }}</h3>
                                            <p class="mt-1 text-sm text-gray-500">{{ __('Update your personal information.') }}</p>
                                        </div>
                                    </div>
                                    <div class="mt-6">
                                        <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            {{ __('Edit Profile') }}
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
