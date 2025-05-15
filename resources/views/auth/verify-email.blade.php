@extends('layouts.main')

@section('content')
    <!-- Verify Email Content Section -->
    <div class="bg-gray-100 py-16">
        <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-gray-900 px-6 py-4">
                    <h2 class="text-xl font-bold text-white">{{ __('auth.verify_email_check') }}</h2>
                </div>
                <div class="p-6">
                    <div class="mb-4 text-sm text-gray-600">
                        {{ __('auth.verify_email_subtitle') }}
                    </div>

                    @if (session('status') == 'verification-link-sent')
                        <div class="mb-4 font-medium text-sm text-green-600">
                            {{ __('auth.verify_email_sent') }}
                        </div>
                    @endif

                    <div class="mt-4 flex items-center justify-between">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf

                            <div>
                                <button type="submit" class="flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-gray-900 bg-yellow-500 hover:bg-yellow-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 transition duration-300">
                                    {{ __('auth.verify_email_request_another') }}
                                </button>
                            </div>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <button type="submit" class="py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300">
                                {{ __('auth.logout') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
