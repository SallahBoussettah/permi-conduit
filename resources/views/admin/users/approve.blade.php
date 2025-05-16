@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Approve User Registration') }}</h1>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                {{ __('Back to Users') }}
            </a>
        </div>

        @if($errors->any())
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="mb-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                            <svg class="h-6 w-6 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-lg font-medium text-gray-900">{{ $user->name }}</h2>
                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-green-700">
                                {{ __('You are about to approve this user\'s registration. You can optionally set an expiration date for their account.') }}
                            </p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('admin.users.approve', $user) }}" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('Account Expiration Options') }}</label>
                        
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center">
                                    <input type="radio" id="no_expiration" name="expiration_type" value="none" checked class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="no_expiration" class="ml-3 block text-sm font-medium text-gray-700">
                                        {{ __('No expiration (permanent access)') }}
                                    </label>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex items-center">
                                    <input type="radio" id="days_expiration" name="expiration_type" value="days" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="days_expiration" class="ml-3 block text-sm font-medium text-gray-700">
                                        {{ __('Set expiration by days') }}
                                    </label>
                                </div>
                                <div class="ml-7 mt-2">
                                    <div class="flex items-center">
                                        <input type="number" id="expires_after" name="expires_after" min="1" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('Number of days') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="flex items-center">
                                    <input type="radio" id="date_expiration" name="expiration_type" value="date" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                    <label for="date_expiration" class="ml-3 block text-sm font-medium text-gray-700">
                                        {{ __('Set specific expiration date') }}
                                    </label>
                                </div>
                                <div class="ml-7 mt-2">
                                    <div class="flex items-center">
                                        <input type="date" id="expiration_date" name="expiration_date" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ now()->addDays(30)->format('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <p class="mt-2 text-sm text-gray-500">
                            {{ __('Choose how long the account should remain active after approval.') }}
                        </p>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            {{ __('Approve User') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const noExpirationRadio = document.getElementById('no_expiration');
        const daysExpirationRadio = document.getElementById('days_expiration');
        const dateExpirationRadio = document.getElementById('date_expiration');
        
        const expiresAfterInput = document.getElementById('expires_after');
        const expirationDateInput = document.getElementById('expiration_date');
        
        function updateInputStates() {
            // Disable/enable days input
            const daysContainer = expiresAfterInput.closest('.ml-7');
            daysContainer.classList.toggle('opacity-50', !daysExpirationRadio.checked);
            expiresAfterInput.disabled = !daysExpirationRadio.checked;
            
            // Disable/enable date input
            const dateContainer = expirationDateInput.closest('.ml-7');
            dateContainer.classList.toggle('opacity-50', !dateExpirationRadio.checked);
            expirationDateInput.disabled = !dateExpirationRadio.checked;
        }
        
        // Initial state
        updateInputStates();
        
        // Add event listeners
        noExpirationRadio.addEventListener('change', updateInputStates);
        daysExpirationRadio.addEventListener('change', updateInputStates);
        dateExpirationRadio.addEventListener('change', updateInputStates);
        
        // Focus the appropriate input when a radio is selected
        daysExpirationRadio.addEventListener('change', function() {
            if (this.checked) {
                expiresAfterInput.focus();
            }
        });
        
        dateExpirationRadio.addEventListener('change', function() {
            if (this.checked) {
                expirationDateInput.focus();
            }
        });
    });
</script>
@endsection 