@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Page Title with Back Button -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-900">
                {{ __('Create AI Chat FAQ') }}
            </h1>
            <a href="{{ route('admin.ai-chat-faqs.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to FAQs') }}
            </a>
        </div>
        
        <!-- Form Container -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('FAQ Information') }}
                </h2>
                <p class="mt-1 text-sm text-gray-600">
                    {{ __('Create a new FAQ to help the AI assistant answer common questions') }}
                </p>
            </div>
            <div class="p-6">
                @if ($errors->any())
                    <div class="mb-6">
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shadow-sm" role="alert">
                            <strong class="font-bold">{{ __('Whoops!') }}</strong>
                            <span class="block sm:inline">{{ __('There were some problems with your input.') }}</span>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <form action="{{ route('admin.ai-chat-faqs.store') }}" method="POST">
                    @csrf

                    <div class="mb-6">
                        <label for="trigger_phrase" class="block text-sm font-medium text-gray-700">{{ __('Trigger Phrase') }}</label>
                        <p class="text-xs text-gray-500 mb-1">{{ __('The keywords or phrase that will trigger this FAQ response. Make it specific and unique.') }}</p>
                        <input type="text" name="trigger_phrase" id="trigger_phrase" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" value="{{ old('trigger_phrase') }}" required>
                    </div>

                    <div class="mb-6">
                        <label for="question" class="block text-sm font-medium text-gray-700">{{ __('Question') }}</label>
                        <p class="text-xs text-gray-500 mb-1">{{ __('The full question this FAQ answers. This helps with context and documentation.') }}</p>
                        <textarea name="question" id="question" rows="3" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>{{ old('question') }}</textarea>
                    </div>

                    <div class="mb-6">
                        <label for="answer" class="block text-sm font-medium text-gray-700">{{ __('Answer') }}</label>
                        <p class="text-xs text-gray-500 mb-1">{{ __('The response that will be provided when the trigger phrase is detected.') }}</p>
                        <textarea name="answer" id="answer" rows="6" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>{{ old('answer') }}</textarea>
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label for="is_active" class="ml-2 block text-sm text-gray-700">
                                {{ __('Active') }}
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">{{ __('If checked, this FAQ will be available for AI chat responses.') }}</p>
                    </div>

                    <div class="flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ __('Create FAQ') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 