@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Gérer les utilisateurs') }}</h1>
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

        <!-- Filter and Search -->
        <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
            <form action="{{ route('admin.users.index') }}" method="GET" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                <div class="flex-1">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Rechercher') }}</label>
                    <input type="text" name="search" id="search" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="{{ __('Rechercher par nom ou email') }}" value="{{ $search ?? '' }}">
                </div>
                
                <div class="w-full md:w-1/4">
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Rôle') }}</label>
                    <select name="role" id="role" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">{{ __('Toutes les rôles') }}</option>
                        @foreach($roles as $id => $name)
                            <option value="{{ $id }}" {{ (isset($roleFilter) && $roleFilter == $id) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="w-full md:w-1/4">
                    <label for="permit_category" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Catégorie de permis') }}</label>
                    <select name="permit_category" id="permit_category" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">{{ __('Toutes les catégories de permis') }}</option>
                        <option value="null" {{ (isset($permitCategoryFilter) && $permitCategoryFilter === 'null') ? 'selected' : '' }}>{{ __('Aucune catégorie de permis') }}</option>
                        @foreach($permitCategories as $id => $name)
                            <option value="{{ $id }}" {{ (isset($permitCategoryFilter) && $permitCategoryFilter == $id) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-full md:w-1/4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Statut') }}</label>
                    <select name="status" id="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">{{ __('Tous les statuts') }}</option>
                        <option value="pending" {{ (isset($statusFilter) && $statusFilter === 'pending') ? 'selected' : '' }}>{{ __('En attente d\'approbation') }}</option>
                        <option value="approved" {{ (isset($statusFilter) && $statusFilter === 'approved') ? 'selected' : '' }}>{{ __('Approuvé') }}</option>
                        <option value="rejected" {{ (isset($statusFilter) && $statusFilter === 'rejected') ? 'selected' : '' }}>{{ __('Rejeté') }}</option>
                        <option value="active" {{ (isset($statusFilter) && $statusFilter === 'active') ? 'selected' : '' }}>{{ __('Actif') }}</option>
                        <option value="inactive" {{ (isset($statusFilter) && $statusFilter === 'inactive') ? 'selected' : '' }}>{{ __('Inactif') }}</option>
                    </select>
                </div>
                
                <div class="flex space-x-2">
                    <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('Filtrer') }}
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        {{ __('Réinitialiser') }}
                    </a>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nom') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Email') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Rôle') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Statut') }}</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Catégorie de permis') }}</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->role->name === 'admin' ? 'bg-purple-100 text-purple-800' : ($user->role->name === 'inspector' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') }}">
                                        {{ ucfirst($user->role->name) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-2">
                                        <!-- Approval Status -->
                                        @if($user->approval_status === 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                {{ __('En attente d\'approbation') }}
                                            </span>
                                        @elseif($user->approval_status === 'approved')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                {{ __('Approuvé') }}
                                            </span>
                                        @elseif($user->approval_status === 'rejected')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" title="{{ $user->rejection_reason }}">
                                                {{ __('Rejeté') }}
                                            </span>
                                        @endif
                                        
                                        <!-- Active Status -->
                                        @if($user->is_active)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                                {{ __('Actif') }}
                                            </span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                {{ __('Inactif') }}
                                            </span>
                                        @endif
                                        
                                        <!-- Expiration Status -->
                                        @if($user->expires_at)
                                            @if($user->hasExpired())
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800" title="{{ __('Expired on') }} {{ $user->expires_at->format('Y-m-d') }}">
                                                    {{ __('Expiré') }}
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $user->daysUntilExpiration() < 7 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}" title="{{ __('Expires on') }} {{ $user->expires_at->format('Y-m-d') }}">
                                                    {{ __('Expire dans') }} {{ $user->daysUntilExpiration() }} {{ __('jours') }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-2">
                                        <div class="flex flex-wrap gap-1 mb-2">
                                            @forelse($user->permitCategories as $permitCategory)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 group">
                                                    {{ $permitCategory->code }}
                                                    <form action="{{ route('admin.users.remove-permit-category', ['user' => $user->id, 'category' => $permitCategory->id]) }}" method="POST" class="ml-1">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex text-blue-400 hover:text-blue-600 focus:outline-none" title="{{ __('Remove this category') }}">
                                                            <svg class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                            </svg>
                                                        </button>
                                                    </form>
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-500">{{ __('Aucun') }}</span>
                                            @endforelse
                                        </div>
                                        
                                        <div class="relative">
                                            <button type="button" 
                                                    onclick="toggleCategoryDropdown(this, '{{ $user->id }}')" 
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-1.5 bg-white text-left">
                                                {{ __('Gérer les catégories') }}
                                                <span class="ml-1 absolute right-3 top-2">
                                                    <svg class="h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </span>
                                            </button>
                                            <div id="dropdown-{{ $user->id }}" class="absolute z-10 hidden mt-1 max-h-60 overflow-auto w-56 rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                                <form action="{{ route('admin.users.update-permit-category', $user) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="p-2 border-b border-gray-100">
                                                        <p class="text-xs font-medium text-gray-700 mb-2">{{ __('Sélectionner les catégories') }}</p>
                                                        @foreach($permitCategories as $id => $name)
                                                            <div class="flex items-center py-1">
                                                                <input id="category-{{ $user->id }}-{{ $id }}" 
                                                                       name="permit_category_ids[]" 
                                                                       value="{{ $id }}" 
                                                                       type="checkbox" 
                                                                       {{ in_array($id, $user->permitCategories->pluck('id')->toArray()) ? 'checked' : '' }}
                                                                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                                <label for="category-{{ $user->id }}-{{ $id }}" class="ml-2 block text-sm text-gray-700">
                                                                    {{ $name }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    <div class="p-2 flex justify-between">
                                                        <button type="button" onclick="toggleCategoryDropdown(this, '{{ $user->id }}')" class="text-sm text-gray-500">
                                                            {{ __('Annuler') }}
                                                        </button>
                                                        <button type="submit" class="inline-flex justify-center text-sm text-white px-3 py-1 bg-indigo-600 hover:bg-indigo-700 rounded">
                                                            {{ __('Enregistrer') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex flex-col space-y-2">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">
                                            <svg class="h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                            {{ __('Modifier') }}
                                        </a>
                                        
                                        <!-- Approval Actions -->
                                        @if($user->approval_status === 'pending')
                                            <div class="flex space-x-2">
                                                <a href="{{ route('admin.users.show-approve', $user) }}" class="text-green-600 hover:text-green-900">
                                                    <svg class="h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    {{ __('Approuver') }}
                                                </a>
                                                <a href="{{ route('admin.users.show-reject', $user) }}" class="text-red-600 hover:text-red-900">
                                                    <svg class="h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                    {{ __('Rejeter') }}
                                                </a>
                                            </div>
                                        @endif
                                        
                                        <!-- Toggle Active Status -->
                                        <form action="{{ route('admin.users.toggle-active', $user) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="{{ $user->is_active ? 'text-yellow-600 hover:text-yellow-900' : 'text-green-600 hover:text-green-900' }}">
                                                @if($user->is_active)
                                                    <svg class="h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    {{ __('Désactiver') }}
                                                @else
                                                    <svg class="h-5 w-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ __('Activer') }}
                                                @endif
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-gray-500">
                                    {{ __('Aucun utilisateur trouvé.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($users->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<script>
    // Track the currently open dropdown
    let openDropdown = null;

    function toggleCategoryDropdown(el, userId) {
        const dropdown = document.getElementById(`dropdown-${userId}`);
        
        // Close any open dropdown first
        if (openDropdown && openDropdown !== dropdown) {
            openDropdown.classList.add('hidden');
        }
        
        // Toggle the clicked dropdown
        dropdown.classList.toggle('hidden');
        
        // Update tracking of open dropdown
        openDropdown = dropdown.classList.contains('hidden') ? null : dropdown;
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (openDropdown) {
            // Check if click is outside the dropdown
            let targetElement = event.target;
            let clickedInsideDropdown = false;
            
            do {
                if (targetElement === openDropdown) {
                    clickedInsideDropdown = true;
                    break;
                }
                
                // Check if clicked on the toggle button
                if (targetElement.getAttribute('onclick') && 
                    targetElement.getAttribute('onclick').includes('toggleCategoryDropdown')) {
                    clickedInsideDropdown = true;
                    break;
                }
                
                targetElement = targetElement.parentNode;
            } while (targetElement);
            
            if (!clickedInsideDropdown) {
                openDropdown.classList.add('hidden');
                openDropdown = null;
            }
        }
    });
</script>
@endsection 