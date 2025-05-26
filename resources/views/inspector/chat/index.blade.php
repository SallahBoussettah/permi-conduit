@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">
                {{ __('Chat Support') }}
            </h1>
            <p class="mt-2 text-lg text-gray-600">
                {{ __('Manage and respond to candidate conversations') }}
            </p>
        </div>

        <!-- New Conversations Section -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg mb-6">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('New Conversations') }}
                </h2>
            </div>
            <div class="p-6">
                @if (count($activeConversations) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Candidate') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Last Message') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Started') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Unread') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($activeConversations as $conversation)
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
                                                {{ $conversation->last_message ? Str::limit($conversation->last_message->message, 30) : 'No messages yet' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $conversation->last_message ? $conversation->last_message->created_at->diffForHumans() : '' }}
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
                                            @if ($conversation->unread_count > 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $conversation->unread_count }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    0
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('inspector.chat.show', $conversation) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                {{ __('Join Chat') }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        {{ __('No new conversations waiting.') }}
                    </div>
                @endif
            </div>
        </div>
        
        <!-- My Active Conversations Section -->
        <div class="bg-white overflow-hidden shadow-lg rounded-lg">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-900">
                    {{ __('My Active Conversations') }}
                </h2>
            </div>
            <div class="p-6">
                @if (count($myConversations) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Candidate') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Last Message') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Updated') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Unread') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Actions') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($myConversations as $conversation)
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
                                                {{ $conversation->last_message ? Str::limit($conversation->last_message->message, 30) : 'No messages yet' }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $conversation->last_message ? $conversation->last_message->created_at->diffForHumans() : '' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $conversation->updated_at->format('M j, Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $conversation->updated_at->format('g:i a') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($conversation->unread_count > 0)
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    {{ $conversation->unread_count }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    0
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="{{ route('inspector.chat.show', $conversation) }}" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 mr-2">
                                                {{ __('Continue') }}
                                            </a>
                                            <form method="POST" action="{{ route('inspector.chat.close', $conversation) }}" class="inline" onsubmit="return confirm('Are you sure you want to close this conversation?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    {{ __('Close') }}
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        {{ __('You don\'t have any active conversations.') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 