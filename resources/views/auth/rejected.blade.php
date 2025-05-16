@extends('layouts.main')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 sm:py-16 lg:py-20">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-center">
                    <div class="h-16 w-16 flex items-center justify-center rounded-full bg-red-100">
                        <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                
                <div class="mt-5 text-center">
                    <h3 class="text-2xl font-medium text-gray-900">{{ __('Account Registration Declined') }}</h3>
                    <div class="mt-4">
                        <p class="text-gray-600">{{ __('Unfortunately, your account registration has been declined by our administrators.') }}</p>
                    </div>
                </div>
                
                @if(session('reason'))
                <div class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">{{ __('Reason for rejection:') }}</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <p>{{ session('reason') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                <div class="mt-8 border-t border-gray-200 pt-6">
                    <div class="text-center">
                        <h4 class="text-lg font-medium text-gray-900">{{ __('What can you do now?') }}</h4>
                        <ul class="mt-4 space-y-3">
                            <li class="flex items-start">
                                <span class="h-6 w-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 flex-shrink-0 mr-3">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-gray-600 text-left">{{ __('Review the reason for rejection (if provided).') }}</span>
                            </li>
                            <li class="flex items-start">
                                <span class="h-6 w-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 flex-shrink-0 mr-3">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-gray-600 text-left">{{ __('Contact our support team for more information.') }}</span>
                            </li>
                            <li class="flex items-start">
                                <span class="h-6 w-6 flex items-center justify-center rounded-full bg-gray-100 text-gray-600 flex-shrink-0 mr-3">
                                    <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span class="text-gray-600 text-left">{{ __('You may register again after addressing the reason for rejection.') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="mt-8 flex justify-center space-x-4">
                    <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-base font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        {{ __('Return to Login Page') }}
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        {{ __('Register Again') }}
                    </a>
                </div>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-gray-500 text-sm">
                {{ __('If you believe this is an error, please contact our support team.') }}
            </p>
        </div>
    </div>
</div>
@endsection 