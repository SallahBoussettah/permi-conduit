@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 mt-16">
        <div class="px-4 sm:px-0 py-6">
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('Notifications') }}</h2>
        </div>
        
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded shadow-sm" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded shadow-sm" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                @if($notifications->count() > 0)
                    <div class="mb-4 flex justify-between">
                        <h3 class="text-lg font-semibold">{{ __('Toutes les notifications') }}</h3>
                        <form action="{{ route('notifications.mark-all-read') }}" method="POST">
                            @csrf
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                {{ __('Marquer toutes comme lues') }}
                            </button>
                        </form>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                            <div class="p-4 border rounded-lg {{ $notification->read_at ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-medium {{ $notification->read_at ? 'text-gray-700' : 'text-blue-800' }}">
                                            {{ $notification->message }}
                                        </p>
                                        <p class="text-sm text-gray-500">
                                            {{ $notification->created_at->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <div class="flex space-x-2">
                                        @if(!$notification->read_at)
                                            <form action="{{ route('notifications.mark-read', $notification) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-blue-600 hover:text-blue-800">
                                                    {{ __('Marquer comme lue') }}
                                                </button>
                                            </form>
                                        @endif
                                        @if($notification->link)
                                            <a href="{{ $notification->link }}" class="text-blue-600 hover:text-blue-800 ml-4">
                                                {{ __('Voir') }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                @else
                    <div class="text-center py-8">
                        <p class="text-gray-500 text-lg">{{ __('Vous n\'avez pas de notifications.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection 