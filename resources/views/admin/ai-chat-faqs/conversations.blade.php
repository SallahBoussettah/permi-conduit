@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900">
                    {{ __('Chat Conversations') }}
                </h1>
                <p class="mt-2 text-lg text-gray-600">
                    {{ __('Overview of all candidate chat conversations') }}
                </p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('admin.ai-chat-faqs.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    {{ __('Back to FAQs') }}
                </a>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('All Conversations') }}
                </h2>
            </div>
            <div class="p-6">
                @if (count($conversations) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Candidate') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Inspector') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Status') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Messages') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Started') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Last Activity') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($conversations as $conversation)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        {{ $conversation->candidate->name }}
                                                    </div>
                                                    <div class="text-sm text-gray-500">
                                                        {{ $conversation->candidate->email }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                @if ($conversation->inspector)
                                                    {{ $conversation->inspector->name }}
                                                @else
                                                    <span class="text-gray-500">{{ __('None') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
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
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $conversation->messages_count }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $conversation->created_at->format('M j, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $conversation->created_at->format('g:i a') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $conversation->updated_at->format('M j, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $conversation->updated_at->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('admin.chat-conversations.show', $conversation) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                {{ __('View') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-6">
                        {{ $conversations->links() }}
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        {{ __('No conversations found.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 