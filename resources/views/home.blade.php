@extends('layouts.main')

@section('content')
    <!-- Hero Section with Full-width Heavy Vehicle Background -->
    <div class="relative bg-gray-900 text-white min-h-[600px]" style="background-image: linear-gradient(to left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), url('https://images.pexels.com/photos/2199293/pexels-photo-2199293.jpeg?auto=compress&cs=tinysrgb&w=1920'); background-size: cover; background-position: center; transform: scaleX(-1);">
        <!-- Main Hero Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 md:py-32" style="transform: scaleX(-1);">
            <div class="md:max-w-lg">
                <h1 class="text-5xl md:text-6xl font-bold tracking-tight mb-6">
                    {{ __('app.hero_title') }}
                </h1>
                <p class="text-xl text-gray-300 mb-10">
                    {{ __('app.hero_subtitle') }}
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-6 py-3 bg-yellow-500 text-gray-900 rounded-md font-medium hover:bg-yellow-400 transition duration-300">
                        {{ __('app.get_started') }}
                    </a>
                    <a href="#features" class="inline-flex items-center px-6 py-3 border border-white text-white rounded-md font-medium hover:bg-gray-800 transition duration-300">
                        {{ __('app.learn_more') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- User Spaces Section -->
    <div class="bg-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">{{ __('app.choose_space_title') }}</h2>
                <p class="mt-4 text-xl text-gray-600">{{ __('app.choose_space_subtitle') }}</p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <div class="bg-white rounded-lg p-6 shadow-lg border border-gray-200 hover:border-yellow-500 transition duration-300">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">{{ __('app.candidate_space') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('app.candidate_space_desc') }}</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-gray-900 rounded-md font-medium hover:bg-yellow-400 transition duration-300">
                        {{ __('app.get_started') }} →
                    </a>
                </div>
                <div class="bg-white rounded-lg p-6 shadow-lg border border-gray-200 hover:border-yellow-500 transition duration-300">
                    <h2 class="text-2xl font-bold mb-4 text-gray-900">{{ __('app.inspector_space') }}</h2>
                    <p class="text-gray-600 mb-6">{{ __('app.inspector_space_desc') }}</p>
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 bg-yellow-500 text-gray-900 rounded-md font-medium hover:bg-yellow-400 transition duration-300">
                        {{ __('app.get_started') }} →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section with Car Images -->
    <div id="features" class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900">
                    {{ __('app.features_title') }}
                </h2>
                <p class="mt-4 text-xl text-gray-600 max-w-3xl mx-auto">
                    {{ __('app.features_subtitle') }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
                <!-- Feature 1 -->
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-lg transform transition duration-300 hover:scale-105">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1541899481282-d53bffe3c35d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Learning Materials" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('app.feature1_title') }}</h3>
                        <p class="text-gray-600">{{ __('app.feature1_desc') }}</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-lg transform transition duration-300 hover:scale-105">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1516321497487-e288fb19713f?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="QCM Tests" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('app.feature2_title') }}</h3>
                        <p class="text-gray-600">{{ __('app.feature2_desc') }}</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-50 rounded-lg overflow-hidden shadow-lg transform transition duration-300 hover:scale-105">
                    <div class="h-48 bg-gray-200 overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80" alt="Practical Exam" class="w-full h-full object-cover">
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('app.feature3_title') }}</h3>
                        <p class="text-gray-600">{{ __('app.feature3_desc') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action with Map Background -->
    <div class="relative bg-gradient-to-b from-gray-900 to-gray-800 text-white">
        <!-- Background pattern with map-like grid -->
        <div class="absolute inset-0 z-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <defs>
                    <pattern id="map-grid" width="10" height="10" patternUnits="userSpaceOnUse">
                        <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#map-grid)" />
            </svg>
        </div>
        
        <!-- Map lines overlay -->
        <div class="absolute inset-0 z-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                <path d="M10,20 Q30,10 50,30 T90,40" stroke="white" fill="none" stroke-width="0.5"/>
                <path d="M20,80 Q40,50 60,70 T95,60" stroke="white" fill="none" stroke-width="0.5"/>
                <path d="M30,10 Q50,30 70,20 T90,30" stroke="white" fill="none" stroke-width="0.5"/>
                <circle cx="30" cy="30" r="2" fill="white"/>
                <circle cx="70" cy="60" r="2" fill="white"/>
                <circle cx="45" cy="40" r="1" fill="white"/>
                <circle cx="80" cy="20" r="1" fill="white"/>
            </svg>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto py-16 px-4 sm:px-6 lg:px-8 flex flex-col items-center">
            <h2 class="text-4xl font-bold mb-6 text-center">
                {{ __('app.cta_title') }}
            </h2>
            <p class="text-xl text-gray-300 mb-10 text-center max-w-3xl">
                {{ __('app.cta_subtitle') }}
            </p>
            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-gray-900 bg-yellow-500 hover:bg-yellow-400 transition duration-300">
                {{ __('app.register_now') }}
            </a>
        </div>
    </div>
@endsection 