@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Page Title with Back Button -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-900">
                {{ __('Conversation with') }} {{ $conversation->candidate->name }}
            </h1>
            <a href="{{ route('admin.chat-conversations.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to All Conversations') }}
            </a>
        </div>

        <!-- Conversation Info -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('Conversation Details') }}
                </h2>
            </div>
            <div class="p-6">
                <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2 md:grid-cols-3">
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Candidate') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $conversation->candidate->name }} <br>
                            <span class="text-gray-500">{{ $conversation->candidate->email }}</span>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
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
                                    {{ __('Inspector Joined') }}
                                @else
                                    {{ __('AI Only') }}
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Inspector') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if ($conversation->inspector)
                                {{ $conversation->inspector->name }}
                            @else
                                <span class="text-gray-500">{{ __('None') }}</span>
                            @endif
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Started') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $conversation->created_at->format('M j, Y g:i a') }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Last Activity') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $conversation->updated_at->format('M j, Y g:i a') }}
                            <div class="text-xs text-gray-500">{{ $conversation->updated_at->diffForHumans() }}</div>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Messages') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ count($messages) }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Chat Messages -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('Messages') }}
                </h2>
            </div>
            <div class="p-6 bg-gray-50 overflow-y-auto max-h-[600px]">
                <div class="space-y-4">
                    @foreach ($messages as $message)
                        <div class="flex {{ $message->user_id === $conversation->candidate->id ? 'justify-start' : 'justify-end' }}">
                            <div class="max-w-[75%] px-4 py-3 rounded-lg shadow-sm 
                                @if ($message->is_from_ai)
                                    bg-gray-200 text-gray-800
                                @elseif ($message->user_id === $conversation->candidate->id)
                                    bg-green-600 text-white
                                @elseif ($conversation->inspector && $message->user_id === $conversation->inspector->id)
                                    bg-blue-600 text-white
                                @else
                                    bg-purple-600 text-white
                                @endif
                            ">
                                <div class="font-bold text-sm mb-1">
                                    @if ($message->is_from_ai)
                                        {{ __('AI Assistant') }}
                                    @elseif ($message->user_id === $conversation->candidate->id)
                                        {{ $conversation->candidate->name }} ({{ __('Candidate') }})
                                    @elseif ($conversation->inspector && $message->user_id === $conversation->inspector->id)
                                        {{ $conversation->inspector->name }} ({{ __('Inspector') }})
                                    @else
                                        {{ optional($message->user)->name ?? __('Unknown User') }}
                                    @endif
                                </div>
                                <p class="text-base">{{ $message->message }}</p>
                                <div class="text-xs 
                                    @if ($message->is_from_ai)
                                        text-gray-500
                                    @elseif ($message->user_id === $conversation->candidate->id)
                                        text-green-100
                                    @elseif ($conversation->inspector && $message->user_id === $conversation->inspector->id)
                                        text-blue-100
                                    @else
                                        text-purple-100
                                    @endif
                                    mt-2"
                                >
                                    {{ $message->created_at->format('M j, g:i a') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @if (count($messages) === 0)
                    <div class="text-center py-8 text-gray-500">
                        {{ __('No messages in this conversation.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 