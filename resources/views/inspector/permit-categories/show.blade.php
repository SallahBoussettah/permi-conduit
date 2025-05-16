@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Permit Category Details') }}</h1>
            <a href="{{ route('inspector.permit-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Permit Categories') }}
            </a>
        </div>

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ $permitCategory->name }} ({{ $permitCategory->code }})</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $permitCategory->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $permitCategory->status ? 'Active' : 'Inactive' }}
                    </span>
                </p>
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Name') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $permitCategory->name }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Code') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $permitCategory->code }}</dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $permitCategory->description ?: 'No description available' }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $permitCategory->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $permitCategory->status ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Courses using this category') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $permitCategory->courses()->count() }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <div class="mt-6 bg-blue-50 p-4 rounded-md shadow-sm">
            <div class="flex items-center">
                <svg class="h-5 w-5 text-blue-400 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-blue-700">
                    {{ __('Contact an administrator if you need to make changes to this permit category.') }}
                </p>
            </div>
        </div>
    </div>
</div>
@endsection 