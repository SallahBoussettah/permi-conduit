@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Modifier l\'utilisateur') }}</h1>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                {{ __('Retour aux utilisateurs') }}
            </a>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nom') }}</label>
                            <div class="mt-1">
                                <input type="text" name="name" id="name" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('name', $user->name) }}" required>
                            </div>
                            @error('name')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                            <div class="mt-1">
                                <input type="email" name="email" id="email" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('email', $user->email) }}" required>
                            </div>
                            @error('email')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="role_id" class="block text-sm font-medium text-gray-700">{{ __('Rôle') }}</label>
                            <div class="mt-1">
                                <select id="role_id" name="role_id" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    @foreach($roles as $id => $name)
                                        <option value="{{ $id }}" {{ old('role_id', $user->role_id) == $id ? 'selected' : '' }}>{{ ucfirst($name) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('role_id')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="permit_category_ids" class="block text-sm font-medium text-gray-700">{{ __('Catégories autorisées') }}</label>
                            <div class="mt-1">
                                <select id="permit_category_ids" name="permit_category_ids[]" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" multiple>
                                    @foreach($permitCategories as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $user->permitCategories->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Maintenir Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs catégories') }}</p>
                            @error('permit_category_ids')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('permit_category_ids.*')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="approval_status" class="block text-sm font-medium text-gray-700">{{ __('Statut d\'approbation') }}</label>
                            <div class="mt-1">
                                <select id="approval_status" name="approval_status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="pending" {{ old('approval_status', $user->approval_status) === 'pending' ? 'selected' : '' }}>{{ __('En attente') }}</option>
                                    <option value="approved" {{ old('approval_status', $user->approval_status) === 'approved' ? 'selected' : '' }}>{{ __('Approuvé') }}</option>
                                    <option value="rejected" {{ old('approval_status', $user->approval_status) === 'rejected' ? 'selected' : '' }}>{{ __('Rejeté') }}</option>
                                </select>
                            </div>
                            @error('approval_status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="is_active" class="block text-sm font-medium text-gray-700">{{ __('Statut du compte') }}</label>
                            <div class="mt-1">
                                <select id="is_active" name="is_active" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>{{ __('Actif') }}</option>
                                    <option value="0" {{ old('is_active', $user->is_active) ? '' : 'selected' }}>{{ __('Inactif') }}</option>
                                </select>
                            </div>
                            @error('is_active')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="expires_at" class="block text-sm font-medium text-gray-700">{{ __('Date d\'expiration du compte') }}</label>
                            <div class="mt-1">
                                <input type="date" name="expires_at" id="expires_at" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" value="{{ old('expires_at', $user->expires_at ? $user->expires_at->format('Y-m-d') : '') }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Laisser vide pour aucune expiration') }}</p>
                            @error('expires_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @if($user->expires_at)
                                <div class="mt-2">
                                    @if($user->hasExpired())
                                        <div class="p-2 bg-red-100 border-l-4 border-red-500 text-red-700 mb-2">
                                            <p class="font-medium">{{ __('Le compte a expiré le') }} {{ $user->expires_at->format('Y-m-d') }}</p>
                                            <p class="text-sm">{{ __('Le compte est actuellement inactif en raison de l\'expiration.') }}</p>
                                        </div>
                                        <button type="button" class="text-sm bg-green-600 hover:bg-green-700 text-white py-1 px-3 rounded" 
                                                onclick="extendAndActivateAccount()">
                                            {{ __('Prolonger de 30 jours et activer') }}
                                        </button>
                                        
                                        <script>
                                            function extendAndActivateAccount() {
                                                // Set the expiration date to 30 days from now
                                                document.getElementById('expires_at').value = '{{ now()->addDays(30)->format('Y-m-d') }}';
                                                
                                                // Set account to active
                                                document.getElementById('is_active').value = '1';
                                                
                                                // Create and show toast notification
                                                const toast = document.createElement('div');
                                                toast.className = 'fixed bottom-4 right-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-lg transform transition-transform duration-300 ease-in-out z-50';
                                                toast.innerHTML = `
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0">
                                                            <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                            </svg>
                                                        </div>
                                                        <div class="ml-3">
                                                            <p class="text-sm font-medium">
                                                                {{ __('Le compte sera prolongé de 30 jours et activé lorsque vous enregistrerez le formulaire.') }}
                                                            </p>
                                                        </div>
                                                        <div class="ml-auto pl-3">
                                                            <div class="-mx-1.5 -my-1.5">
                                                                <button type="button" onclick="this.parentElement.parentElement.parentElement.remove()" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                                                    <span class="sr-only">{{ __('Fermer') }}</span>
                                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                `;
                                                
                                                document.body.appendChild(toast);
                                                
                                                // Auto remove after 5 seconds
                                                setTimeout(() => {
                                                    toast.classList.add('translate-y-2', 'opacity-0');
                                                    setTimeout(() => {
                                                        toast.remove();
                                                    }, 300);
                                                }, 5000);
                                            }
                                        </script>
                                    @else
                                        <div class="p-2 {{ $user->daysUntilExpiration() < 7 ? 'bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700' : 'bg-blue-100 border-l-4 border-blue-500 text-blue-700' }}">
                                            <p class="font-medium">
                                                {{ __('Le compte expirera dans') }} {{ $user->daysUntilExpiration() }} {{ __('jours') }}
                                                ({{ $user->expires_at->format('Y-m-d') }})
                                            </p>
                                            @if($user->daysUntilExpiration() < 7)
                                                <p class="text-sm">{{ __('Attention: Ce compte expirera bientôt.') }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div id="rejection_reason_container" class="sm:col-span-6 {{ old('approval_status', $user->approval_status) !== 'rejected' ? 'hidden' : '' }}">
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">{{ __('Raison de rejet') }}</label>
                            <div class="mt-1">
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('rejection_reason', $user->rejection_reason) }}</textarea>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Requis si le statut est défini sur Rejeté') }}</p>
                            @error('rejection_reason')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Nouveau mot de passe') }}</label>
                            <div class="mt-1">
                                <input type="password" name="password" id="password" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('Laisser vide pour conserver le mot de passe actuel') }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Laisser vide pour conserver le mot de passe actuel') }}</p>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirmer le nouveau mot de passe') }}</label>
                            <div class="mt-1">
                                <input type="password" name="password_confirmation" id="password_confirmation" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                            </div>
                            @error('password_confirmation')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-5 flex justify-end">
                        <a href="{{ route('admin.users.index') }}" class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Annuler') }}
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Mettre à jour l\'utilisateur') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const approvalStatusSelect = document.getElementById('approval_status');
        const rejectionReasonContainer = document.getElementById('rejection_reason_container');
        
        // Function to toggle the rejection reason field visibility
        function toggleRejectionReason() {
            if (approvalStatusSelect.value === 'rejected') {
                rejectionReasonContainer.classList.remove('hidden');
            } else {
                rejectionReasonContainer.classList.add('hidden');
            }
        }
        
        // Add event listener to the approval status select
        approvalStatusSelect.addEventListener('change', toggleRejectionReason);
        
        // Call the function once at page load to set initial state
        toggleRejectionReason();
    });
</script>
@endsection 