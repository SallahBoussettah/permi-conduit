@extends('layouts.main')

@section('content')
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 mt-16">
        <div class="px-4 sm:px-0 py-6 flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-900">{{ __('Notifications') }}</h2>
            <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="flex space-x-2">
                @csrf
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    {{ __('Marquer toutes comme lues') }}
                </button>
                <button type="button" id="show-filters-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold py-2 px-4 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    {{ __('Filtres') }}
                </button>
                <button type="button" id="deleteBulkBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    {{ __('Supprimer') }}
                </button>
            </form>
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
        
        <!-- Filters panel -->
        <div id="filters-panel" class="bg-white shadow overflow-hidden sm:rounded-lg mb-6 hidden">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium mb-4">{{ __('Filtrer les notifications') }}</h3>
                <form action="{{ route('notifications.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="read_status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Statut') }}</label>
                        <select name="read_status" id="read_status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">{{ __('Tous') }}</option>
                            <option value="read" {{ request('read_status') == 'read' ? 'selected' : '' }}>{{ __('Lu') }}</option>
                            <option value="unread" {{ request('read_status') == 'unread' ? 'selected' : '' }}>{{ __('Non lu') }}</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Type') }}</label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="">{{ __('Tous') }}</option>
                            @foreach($notificationTypes as $value => $label)
                                <option value="{{ $value }}" {{ request('type') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="sort_by" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Trier par') }}</label>
                        <select name="sort_by" id="sort_by" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="created_at" {{ request('sort_by', 'created_at') == 'created_at' ? 'selected' : '' }}>{{ __('Date de création') }}</option>
                            <option value="read_at" {{ request('sort_by') == 'read_at' ? 'selected' : '' }}>{{ __('Date de lecture') }}</option>
                            <option value="type" {{ request('sort_by') == 'type' ? 'selected' : '' }}>{{ __('Type') }}</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="sort_direction" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Ordre') }}</label>
                        <select name="sort_direction" id="sort_direction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <option value="desc" {{ request('sort_direction', 'desc') == 'desc' ? 'selected' : '' }}>{{ __('Décroissant') }}</option>
                            <option value="asc" {{ request('sort_direction') == 'asc' ? 'selected' : '' }}>{{ __('Croissant') }}</option>
                        </select>
                    </div>
                    
                    <div class="md:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date de début') }}</label>
                                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                            <div>
                                <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Date de fin') }}</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </div>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2 flex items-end">
                        <div class="flex space-x-2">
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('Appliquer les filtres') }}
                            </button>
                            <a href="{{ route('notifications.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                {{ __('Réinitialiser') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                @if($notifications->count() > 0)
                    <div class="space-y-4">
                        @foreach($notifications as $notification)
                            <div class="p-4 border rounded-lg {{ $notification->read_at ? 'bg-gray-50' : ($notification->type ? $notification->getColorClass() : 'bg-blue-50 border-blue-200') }}">
                                <div class="flex justify-between">
                                    <div>
                                        <p class="font-medium {{ $notification->read_at ? 'text-gray-700' : ($notification->type ? $notification->getTextColorClass() : 'text-blue-800') }}">
                                            {{ $notification->message }}
                                        </p>
                                        <div class="flex items-center mt-1">
                                            <p class="text-sm text-gray-500">
                                                {{ $notification->created_at->format('d/m/Y H:i') }}
                                            </p>
                                            @if($notification->type)
                                                <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $notificationTypes[$notification->type] ?? $notification->type }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex space-x-2">
                                        @if(!$notification->read_at)
                                            <form action="{{ route('notifications.mark-read', $notification) }}" method="POST" class="inline">
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
                                        <form action="{{ route('notifications.destroy', $notification) }}" method="POST" class="inline delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 ml-4">
                                                {{ __('Supprimer') }}
                                            </button>
                                        </form>
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

    <!-- Delete Bulk Modal -->
    <div id="deleteBulkModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                {{ __('Supprimer les notifications') }}
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    {{ __('Êtes-vous sûr de vouloir supprimer toutes les notifications filtrées ? Cette action ne peut pas être annulée.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <form action="{{ route('notifications.destroy-all') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="read_status" value="{{ request('read_status') }}">
                        <input type="hidden" name="type" value="{{ request('type') }}">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('Supprimer') }}
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" id="cancelBulkDelete">
                            {{ __('Annuler') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle filters panel
            const filtersPanel = document.getElementById('filters-panel');
            const filtersBtn = document.getElementById('show-filters-btn');
            
            filtersBtn.addEventListener('click', function() {
                filtersPanel.classList.toggle('hidden');
            });
            
            // Show filters panel if any filter is active
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('read_status') || urlParams.has('type') || urlParams.has('sort_by') || 
                urlParams.has('sort_direction') || urlParams.has('start_date') || urlParams.has('end_date')) {
                filtersPanel.classList.remove('hidden');
            }
            
            // Delete confirmation for individual items
            const deleteForms = document.querySelectorAll('.delete-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    if (!confirm('{{ __("Êtes-vous sûr de vouloir supprimer cette notification ?") }}')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Bulk delete modal
            const deleteBulkBtn = document.getElementById('deleteBulkBtn');
            const deleteBulkModal = document.getElementById('deleteBulkModal');
            const cancelBulkDelete = document.getElementById('cancelBulkDelete');
            
            deleteBulkBtn.addEventListener('click', function() {
                deleteBulkModal.classList.remove('hidden');
            });
            
            cancelBulkDelete.addEventListener('click', function() {
                deleteBulkModal.classList.add('hidden');
            });
        });
    </script>
@endsection 