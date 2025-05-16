@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Create Permit Category') }}</h1>
            <a href="{{ route('admin.permit-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Permit Categories') }}
            </a>
        </div>

        @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <form action="{{ route('admin.permit-categories.store') }}" method="POST">
                @csrf
                <div class="px-4 py-5 sm:p-6">
                    <div class="grid grid-cols-1 gap-6">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" id="name" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('name') }}" required>
                            <p class="mt-1 text-sm text-gray-500">{{ __('The full name of the permit category (e.g. "Driver License C").') }}</p>
                        </div>

                        <!-- Code -->
                        <div>
                            <label for="code" class="block text-sm font-medium text-gray-700">{{ __('Code') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="code" id="code" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('code') }}" required>
                            <p class="mt-1 text-sm text-gray-500">{{ __('The short code for the permit category (e.g. "C", "CE", "D").') }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea name="description" id="description" rows="3" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">{{ old('description') }}</textarea>
                        </div>

                        <!-- Status -->
                        <div>
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input id="status" name="status" type="checkbox" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded" value="1" {{ old('status', '1') == '1' ? 'checked' : '' }}>
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="status" class="font-medium text-gray-700">{{ __('Active') }}</label>
                                    <p class="text-gray-500">{{ __('Whether this permit category is active and available.') }}</p>
                                    <div class="mt-2 p-2 border-l-4 border-yellow-400 bg-yellow-50">
                                        <p class="text-yellow-700">
                                            <strong>{{ __('Important:') }}</strong> {{ __('When a permit category is set to inactive:') }}
                                        </p>
                                        <ul class="list-disc ml-5 mt-1 text-yellow-700">
                                            <li>{{ __('Candidates with this permit category will only see general courses.') }}</li>
                                            <li>{{ __('Courses associated with this permit category will not be accessible.') }}</li>
                                            <li>{{ __('Candidates will see a message informing them the category is unavailable.') }}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-4 py-3 bg-gray-50 text-right sm:px-6">
                    <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        {{ __('Create Permit Category') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection 