@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Page Title with Back Button -->
        <div class="mb-6 flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-900">
                {{ __('Chat with') }} {{ $candidate->name }}
            </h1>
            <a href="{{ route('inspector.chat.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                {{ __('Back to All Chats') }}
            </a>
        </div>

        <!-- Chat Container -->
        <div class="bg-white overflow-hidden shadow-xl rounded-lg">
            <div class="flex flex-col h-[calc(100vh-200px)] min-h-[600px]">
                <!-- Chat Header -->
                <div class="flex justify-between items-center p-6 border-b border-gray-200 bg-gray-50">
                    <div class="flex items-center">
                        <div class="bg-blue-100 rounded-full p-2 mr-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900">
                                {{ $candidate->name }}
                            </div>
                            <div class="text-sm text-gray-500">
                                {{ $candidate->email }}
                            </div>
                        </div>
                    </div>
                    
                    @if ($conversation->status !== 'closed')
                    <form method="POST" action="{{ route('inspector.chat.close', $conversation) }}" onsubmit="return confirm('Are you sure you want to close this conversation?');">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ __('Close Chat') }}
                        </button>
                    </form>
                    @endif
                </div>
                
                <!-- Chat Messages -->
                <div id="chat-messages" class="flex-1 p-6 overflow-y-auto space-y-4 bg-gray-50">
                    @foreach ($messages as $message)
                        <div class="flex {{ $message->user_id === auth()->id() ? 'justify-end' : 'justify-start' }}" data-message-id="{{ $message->id }}">
                            <div class="max-w-[75%] px-4 py-3 rounded-lg shadow-sm {{ $message->user_id === auth()->id() ? 'bg-blue-600 text-white' : ($message->is_from_ai ? 'bg-gray-200 text-gray-800' : ($message->user_id === $candidate->id ? 'bg-green-600 text-white' : 'bg-purple-600 text-white')) }}">
                                @if (!$message->is_from_ai && $message->user_id !== auth()->id())
                                    <div class="font-bold text-sm mb-1">
                                        @if ($message->user_id === $candidate->id)
                                            {{ $candidate->name }} (Candidate)
                                        @else
                                            {{ optional($message->user)->name ?? 'Unknown' }}
                                        @endif
                                    </div>
                                @endif
                                <p class="text-base">{{ $message->message }}</p>
                                <div class="text-xs {{ $message->user_id === auth()->id() ? 'text-blue-100' : ($message->is_from_ai ? 'text-gray-500' : 'text-green-100') }} mt-2">
                                    {{ $message->created_at->format('M j, g:i a') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <!-- Chat Input -->
                @if ($conversation->status !== 'closed')
                <div class="border-t border-gray-200 p-4 bg-white">
                    <form id="message-form" method="POST" action="{{ route('inspector.chat.send-message', $conversation) }}">
                        @csrf
                        <div class="flex">
                            <input 
                                type="text" 
                                name="message" 
                                id="message-input"
                                class="flex-1 px-4 py-3 border-gray-300 rounded-l-lg shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                placeholder="Type your message..."
                                autocomplete="off"
                                required
                            >
                            <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-r-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 flex items-center">
                                <span>{{ __('Send') }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <div class="border-t border-gray-200 p-6 bg-gray-100">
                    <div class="flex justify-center">
                        <div class="bg-white px-6 py-4 rounded-lg shadow-sm text-center max-w-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <p class="text-gray-600 font-medium mb-1">{{ __('This conversation has been closed') }}</p>
                            <p class="text-gray-500 text-sm">{{ __('No new messages can be sent in this conversation.') }}</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($conversation->status !== 'closed')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessages = document.getElementById('chat-messages');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        let canSendMessage = true;
        
        // Scroll to bottom of messages
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        // Initial scroll
        scrollToBottom();
        
        // Handle form submission
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const message = messageInput.value.trim();
            if (!message || !canSendMessage) return;
            
            // Implement cooldown
            canSendMessage = false;
            setTimeout(() => {
                canSendMessage = true;
            }, 3000); // 3 second cooldown
            
            // Send message via AJAX
            fetch(messageForm.action, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    message: message
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear input
                    messageInput.value = '';
                    
                    // Fetch latest messages (including the one we just sent)
                    fetchNewMessages();
                }
            })
            .catch(error => {
                console.error('Error sending message:', error);
                canSendMessage = true; // Reset cooldown on error
            });
        });
        
        // Function to fetch new messages
        function fetchNewMessages() {
            const lastMessageElement = chatMessages.querySelector('div.flex:last-child');
            const lastMessageId = lastMessageElement ? lastMessageElement.dataset.messageId || 0 : 0;
            
            fetch(`{{ route('inspector.chat.get-messages', $conversation) }}?last_message_id=${lastMessageId}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    // Append new messages
                    data.messages.forEach(message => {
                        const messageElement = document.createElement('div');
                        messageElement.className = `flex ${message.user_id === {{ auth()->id() }} ? 'justify-end' : 'justify-start'}`;
                        messageElement.dataset.messageId = message.id;
                        
                        const messageContent = document.createElement('div');
                        messageContent.className = `max-w-[75%] px-4 py-3 rounded-lg shadow-sm ${
                            message.user_id === {{ auth()->id() }} 
                                ? 'bg-blue-600 text-white' 
                                : (message.is_from_ai 
                                    ? 'bg-gray-200 text-gray-800' 
                                    : (message.user_id === {{ $candidate->id }} 
                                        ? 'bg-green-600 text-white' 
                                        : 'bg-purple-600 text-white'))
                        }`;
                        
                        let innerHTML = '';
                        if (!message.is_from_ai && message.user_id !== {{ auth()->id() }}) {
                            innerHTML += `<div class="font-bold text-sm mb-1">`;
                            if (message.user_id === {{ $candidate->id }}) {
                                innerHTML += `{{ $candidate->name }} (Candidate)`;
                            } else {
                                innerHTML += `${message.user ? message.user.name : 'Unknown'}`;
                            }
                            innerHTML += `</div>`;
                        }
                        
                        innerHTML += `<p class="text-base">${message.message}</p>`;
                        innerHTML += `<div class="text-xs ${
                            message.user_id === {{ auth()->id() }} 
                                ? 'text-blue-100' 
                                : (message.is_from_ai 
                                    ? 'text-gray-500' 
                                    : 'text-green-100')
                        } mt-2">`;
                        innerHTML += new Date(message.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: 'numeric', hour12: true });
                        innerHTML += '</div>';
                        
                        messageContent.innerHTML = innerHTML;
                        messageElement.appendChild(messageContent);
                        
                        chatMessages.appendChild(messageElement);
                    });
                    
                    scrollToBottom();
                }
            })
            .catch(error => {
                console.error('Error fetching messages:', error);
            });
        }
        
        // Poll for new messages every 3 seconds
        setInterval(fetchNewMessages, 3000);
    });
</script>
@endif
@endsection 