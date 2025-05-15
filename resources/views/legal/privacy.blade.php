@extends('layouts.main')

@section('content')
    <!-- Hero Section with Background -->
    <div class="relative bg-gray-900 text-white" style="background-image: linear-gradient(to left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), url('https://images.pexels.com/photos/5688507/pexels-photo-5688507.jpeg?auto=compress&cs=tinysrgb&w=1920'); background-size: cover; background-position: center;">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-24">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">
                    {{ __('app.privacy_policy_title') }}
                </h1>
                <p class="text-xl text-gray-300">
                    {{ __('app.privacy_policy_subtitle') }}
                </p>
                <p class="mt-4 text-sm text-gray-400">
                    {{ __('app.privacy_policy_updated') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Content -->
    <div class="bg-white py-16">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="prose prose-lg prose-blue mx-auto">
                <!-- Section 1 -->
                <h2>{{ __('app.privacy_section1_title') }}</h2>
                <p>{{ __('app.privacy_section1_content') }}</p>
                
                <!-- Section 2 -->
                <h2>{{ __('app.privacy_section2_title') }}</h2>
                <p>{{ __('app.privacy_section2_content') }}</p>
                
                <!-- Section 3 -->
                <h2>{{ __('app.privacy_section3_title') }}</h2>
                <p>{{ __('app.privacy_section3_content') }}</p>
                
                <!-- Section 4 -->
                <h2>{{ __('app.privacy_section4_title') }}</h2>
                <p>{{ __('app.privacy_section4_content') }}</p>
                
                <!-- Section 5 -->
                <h2>{{ __('app.privacy_section5_title') }}</h2>
                <p>{{ __('app.privacy_section5_content') }}</p>
                
                <!-- Section 6 -->
                <h2>{{ __('app.privacy_section6_title') }}</h2>
                <p>{{ __('app.privacy_section6_content') }}</p>
                <ul>
                    <li><strong>Email:</strong> {{ __('app.footer_email') }}</li>
                    <li><strong>{{ __('app.phone') }}:</strong> {{ __('app.footer_phone') }}</li>
                    <li><strong>{{ __('app.address') }}:</strong> {{ __('app.footer_address') }}</li>
                </ul>
            </div>
        </div>
    </div>
@endsection 