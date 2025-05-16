@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Permit Category Details') }}</h1>
            <a href="{{ route('admin.permit-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to Permit Categories') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

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
                        <dt class="text-sm font-medium text-gray-500">{{ __('Created at') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $permitCategory->created_at->format('F j, Y, g:i a') }}</dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Updated at') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $permitCategory->updated_at->format('F j, Y, g:i a') }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="mt-6 bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50">
                <h3 class="text-lg font-medium leading-6 text-gray-900">{{ __('Usage Statistics') }}</h3>
            </div>
            <div class="border-t border-gray-200">
                <div class="px-4 py-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="bg-blue-50 rounded-md p-4">
                        <h4 class="text-lg font-medium text-blue-800">{{ __('Courses') }}</h4>
                        <p class="text-3xl font-bold text-blue-900 mt-2">{{ $permitCategory->courses()->count() }}</p>
                        <p class="text-sm text-blue-600 mt-1">{{ __('Courses assigned to this permit category') }}</p>
                    </div>
                    <div class="bg-green-50 rounded-md p-4">
                        <h4 class="text-lg font-medium text-green-800">{{ __('Users') }}</h4>
                        <p class="text-3xl font-bold text-green-900 mt-2">{{ $permitCategory->users()->count() }}</p>
                        <p class="text-sm text-green-600 mt-1">{{ __('Users assigned to this permit category') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end space-x-4">
            <a href="{{ route('admin.permit-categories.edit', $permitCategory) }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                </svg>
                {{ __('Edit') }}
            </a>

            <form action="{{ route('admin.permit-categories.destroy', $permitCategory) }}" method="POST" onsubmit="return confirm('{{ __('Are you sure you want to delete this permit category?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m6-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ __('Delete') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection 