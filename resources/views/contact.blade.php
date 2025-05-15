@extends('layouts.main')

@section('content')
    <!-- Hero Section with Background -->
    <div class="relative bg-gray-900 text-white" style="background-image: linear-gradient(to left, rgba(15, 23, 42, 0.9), rgba(15, 23, 42, 0.7)), url('https://images.pexels.com/photos/327540/pexels-photo-327540.jpeg?auto=compress&cs=tinysrgb&w=1920'); background-size: cover; background-position: center;">
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-24">
            <div class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-5xl font-bold tracking-tight mb-6">
                    {{ __('app.contact_us') }}
                </h1>
                <p class="text-xl text-gray-300">
                    {{ __('app.contact_subtitle') }}
                </p>
            </div>
        </div>
    </div>

    <!-- Contact Form and Information -->
    <div class="bg-gray-100 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-2">
                <!-- Contact Form -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-gray-900 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">{{ __('app.send_message') }}</h2>
                    </div>
                    <div class="p-6">
                        @if(session('success'))
                            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ __('app.contact_success') }}</span>
                            </div>
                        @endif
                        
                        @if(session('error'))
                            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <span class="block sm:inline">{{ __('app.contact_error') }}</span>
                            </div>
                        @endif
                        
                        <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6">
                            @csrf
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('app.name') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('name') border-red-300 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('app.email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('email') border-red-300 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="subject" class="block text-sm font-medium text-gray-700">{{ __('app.subject') }}</label>
                                <input type="text" name="subject" id="subject" value="{{ old('subject') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('subject') border-red-300 @enderror">
                                @error('subject')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="message" class="block text-sm font-medium text-gray-700">{{ __('app.message') }}</label>
                                <textarea name="message" id="message" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('message') border-red-300 @enderror">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-900 bg-yellow-500 hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-300">
                                    {{ __('app.send') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="bg-gray-900 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">{{ __('app.contact_info') }}</h2>
                    </div>
                    <div class="p-6">
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ __('app.phone') }}</p>
                                    <p class="text-sm text-gray-500">+33 (0)1 23 45 67 89</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ __('app.email') }}</p>
                                    <p class="text-sm text-gray-500">contact@ecf.com</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ __('app.address') }}</p>
                                    <p class="text-sm text-gray-500">123 Rue de l'Exemple, 75001 Paris, France</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ __('app.hours') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('app.weekdays') }}</p>
                                    <p class="text-sm text-gray-500">{{ __('app.weekends') }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Map with styling -->
                        <div class="mt-8 rounded-md overflow-hidden shadow-inner border border-gray-200">
                            <div class="relative h-64 bg-gray-200">
                                <iframe class="absolute inset-0 w-full h-full" frameborder="0" style="border:0" 
                                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2624.142047744348!2d2.3354330160472316!3d48.87456857928921!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e66e38f817b573%3A0x48d69c30470e7aeb!2sPlace%20de%20la%20Concorde%2C%2075008%20Paris!5e0!3m2!1sen!2sfr!4v1651234567890!5m2!1sen!2sfr" 
                                    allowfullscreen="" loading="lazy">
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FAQ Section -->
    <div class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">{{ __('app.faq_title') }}</h2>
                <p class="mt-4 text-xl text-gray-600">{{ __('app.faq_subtitle') }}</p>
            </div>
            
            <div class="max-w-3xl mx-auto">
                <!-- FAQ Item 1 -->
                <div x-data="{ open: false }" class="border-b border-gray-200">
                    <button @click="open = !open" class="flex justify-between items-center w-full py-5 px-2 text-left focus:outline-none">
                        <span class="text-lg font-medium text-gray-900">{{ __('app.faq_q1') }}</span>
                        <svg class="h-5 w-5 text-yellow-500 transition-transform duration-200" :class="{'transform rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="pb-5 px-2">
                        <p class="text-gray-600">{{ __('app.faq_a1') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item 2 -->
                <div x-data="{ open: false }" class="border-b border-gray-200">
                    <button @click="open = !open" class="flex justify-between items-center w-full py-5 px-2 text-left focus:outline-none">
                        <span class="text-lg font-medium text-gray-900">{{ __('app.faq_q2') }}</span>
                        <svg class="h-5 w-5 text-yellow-500 transition-transform duration-200" :class="{'transform rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="pb-5 px-2">
                        <p class="text-gray-600">{{ __('app.faq_a2') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item 3 -->
                <div x-data="{ open: false }" class="border-b border-gray-200">
                    <button @click="open = !open" class="flex justify-between items-center w-full py-5 px-2 text-left focus:outline-none">
                        <span class="text-lg font-medium text-gray-900">{{ __('app.faq_q3') }}</span>
                        <svg class="h-5 w-5 text-yellow-500 transition-transform duration-200" :class="{'transform rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="pb-5 px-2">
                        <p class="text-gray-600">{{ __('app.faq_a3') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item 4 -->
                <div x-data="{ open: false }" class="border-b border-gray-200">
                    <button @click="open = !open" class="flex justify-between items-center w-full py-5 px-2 text-left focus:outline-none">
                        <span class="text-lg font-medium text-gray-900">{{ __('app.faq_q4') }}</span>
                        <svg class="h-5 w-5 text-yellow-500 transition-transform duration-200" :class="{'transform rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="pb-5 px-2">
                        <p class="text-gray-600">{{ __('app.faq_a4') }}</p>
                    </div>
                </div>
                
                <!-- FAQ Item 5 -->
                <div x-data="{ open: false }" class="border-b border-gray-200">
                    <button @click="open = !open" class="flex justify-between items-center w-full py-5 px-2 text-left focus:outline-none">
                        <span class="text-lg font-medium text-gray-900">{{ __('app.faq_q5') }}</span>
                        <svg class="h-5 w-5 text-yellow-500 transition-transform duration-200" :class="{'transform rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="pb-5 px-2">
                        <p class="text-gray-600">{{ __('app.faq_a5') }}</p>
                        <ul class="list-disc list-inside mt-2 space-y-1 text-gray-600">
                            {!! __('app.faq_a5_points') !!}
                        </ul>
                    </div>
                </div>
                
                <!-- FAQ Item 6 -->
                <div x-data="{ open: false }" class="border-b border-gray-200">
                    <button @click="open = !open" class="flex justify-between items-center w-full py-5 px-2 text-left focus:outline-none">
                        <span class="text-lg font-medium text-gray-900">{{ __('app.faq_q6') }}</span>
                        <svg class="h-5 w-5 text-yellow-500 transition-transform duration-200" :class="{'transform rotate-180': open}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200" 
                         x-transition:enter-start="opacity-0 transform -translate-y-2" 
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform translate-y-0"
                         x-transition:leave-end="opacity-0 transform -translate-y-2"
                         class="pb-5 px-2">
                        <p class="text-gray-600">{{ __('app.faq_a6') }}</p>
                        <ul class="list-disc list-inside mt-2 space-y-1 text-gray-600">
                            {!! __('app.faq_a6_items') !!}
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection 