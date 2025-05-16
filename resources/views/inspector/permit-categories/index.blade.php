@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Permit Categories') }}</h1>
            <div class="text-sm text-gray-600 italic">
                {{ __('Permit categories are managed by administrators') }}
            </div>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <!-- Status Information Alert -->
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded relative" role="alert">
            <div class="flex">
                <div class="py-1">
                    <svg class="h-6 w-6 text-blue-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="font-bold">{{ __('Status Information') }}</p>
                    <p class="text-sm">{{ __('Inactive permit categories will not be visible to candidates, and their associated courses will not be accessible. Only general courses (not associated with any permit category) will be shown to candidates with inactive permit categories.') }}</p>
                </div>
            </div>
        </div>

        <!-- Permit Categories Table -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Code') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Courses') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($permitCategories as $category)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $category->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $category->code }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $category->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $category->status_text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $category->courses()->count() }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $category->description ?: 'No description available' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-500">
                                    {{ __('No permit categories found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($permitCategories->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $permitCategories->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection 