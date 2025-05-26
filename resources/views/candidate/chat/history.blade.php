@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">
                    {{ __('Chat History') }}
                </h1>
                <p class="mt-2 text-lg text-gray-600">
                    {{ __('View your previous conversations with our support team') }}
                </p>
            </div>
            <div>
                <a href="{{ route('candidate.chat.start') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    {{ __('Start New Conversation') }}
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-sm mb-6" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Chat History List -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('Your Conversations') }}
                </h2>
            </div>
            <div class="p-6">
                @if (count($conversations) > 0)
                    <div class="space-y-4">
                        @foreach ($conversations as $conversation)
                            <div class="bg-white border rounded-lg shadow-sm overflow-hidden hover:border-blue-300 transition-colors duration-200">
                                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                    <div class="flex items-center">
                                        <div class="{{ $conversation->status === 'closed' ? 'bg-gray-100' : ($conversation->status === 'inspector_joined' ? 'bg-green-100' : 'bg-blue-100') }} rounded-full p-2 mr-3">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $conversation->status === 'closed' ? 'text-gray-600' : ($conversation->status === 'inspector_joined' ? 'text-green-600' : 'text-blue-600') }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                @if ($conversation->status === 'inspector_joined')
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                @else
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                                                @endif
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-medium text-gray-900">
                                                @if ($conversation->status === 'closed')
                                                    {{ __('Closed Conversation') }}
                                                @elseif ($conversation->status === 'inspector_joined')
                                                    {{ __('Conversation with Inspector') }}
                                                @else
                                                    {{ __('AI Support Conversation') }}
                                                @endif
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                {{ $conversation->messages_count }} {{ __('messages') }} · 
                                                {{ __('Started') }}: {{ $conversation->created_at->format('M j, Y') }}
                                            </p>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if ($conversation->status === 'closed')
                                                bg-gray-100 text-gray-800
                                            @elseif ($conversation->status === 'inspector_joined')
                                                bg-green-100 text-green-800
                                            @else
                                                bg-blue-100 text-blue-800
                                            @endif
                                        ">
                                            @if ($conversation->status === 'closed')
                                                {{ __('Closed') }}
                                            @elseif ($conversation->status === 'inspector_joined')
                                                {{ __('Active with Inspector') }}
                                            @else
                                                {{ __('Active with AI') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="px-6 py-4 bg-gray-50">
                                    <div class="text-sm text-gray-700 mb-3">
                                        <strong>{{ __('Last message') }}:</strong> 
                                        @if($conversation->lastMessage)
                                            {{ Str::limit($conversation->lastMessage->message, 150) }}
                                        @else
                                            {{ __('No messages yet') }}
                                        @endif
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <div class="text-sm text-gray-500">
                                            {{ __('Last activity') }}: {{ $conversation->updated_at->diffForHumans() }}
                                        </div>
                                        <a href="{{ route('candidate.chat.index') }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            @if ($conversation->status === 'closed')
                                                {{ __('View') }}
                                            @else
                                                {{ __('Continue') }}
                                            @endif
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $conversations->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="bg-blue-100 rounded-full p-4 w-20 h-20 mx-auto mb-4 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-2">{{ __('No conversations yet') }}</h3>
                        <p class="text-base text-gray-600 mb-8 max-w-md mx-auto">
                            {{ __('Start a new conversation with our AI Assistant to get help or ask questions about your driving courses and exams.') }}
                        </p>
                        <a href="{{ route('candidate.chat.start') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            {{ __('Start New Conversation') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 