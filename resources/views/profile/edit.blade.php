@extends('layouts.main')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-6">{{ __('Paramètres du profil') }}</h1>
                
                <div class="mb-6">
                    <a href="{{ route('dashboard') }}" class="text-yellow-600 hover:underline">
                        &larr; {{ __('Retour au tableau de bord') }}
                    </a>
                </div>

                <!-- Success Message -->
                @if (session('status') === 'profile-updated')
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ __('Les informations du profil ont été mises à jour avec succès.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-8">
                    <!-- Profile Information -->
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Informations du profil') }}</h2>
                        <p class="mt-1 text-sm text-gray-600 mb-4">{{ __("Mettre à jour les informations de votre compte et votre adresse email.") }}</p>
                        
                        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                            @csrf
                            @method('patch')
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Nom') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('name') border-red-300 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Adresse email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('email') border-red-300 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                
                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-800">
                                            {{ __('Votre adresse email n\'est pas vérifiée.') }}
                                            
                                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('Cliquez ici pour renvoyer l\'email de vérification.') }}
                                            </button>
                                        </p>
                                        
                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 font-medium text-sm text-green-600">
                                                {{ __('Un nouvel email de vérification a été envoyé à votre adresse email.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Enregistrer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Update Password -->
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Mettre à jour le mot de passe') }}</h2>
                        <p class="mt-1 text-sm text-gray-600 mb-4">{{ __('Assurez-vous que votre compte utilise un mot de passe long et aléatoire pour rester sécurisé.') }}</p>
                        
                        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                            @csrf
                            @method('put')
                            
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">{{ __('Mot de passe actuel') }}</label>
                                <input type="password" name="current_password" id="current_password" autocomplete="current-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('current_password') border-red-300 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Nouveau mot de passe') }}</label>
                                <input type="password" name="password" id="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('password') border-red-300 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirmer le mot de passe') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                @error('password_confirmation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Enregistrer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Delete Account -->
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Supprimer le compte') }}</h2>
                        <p class="mt-1 text-sm text-gray-600 mb-4">{{ __('Une fois votre compte supprimé, tous ses ressources et données seront définitivement supprimées.') }}</p>
                        
                        <button
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            {{ __('Supprimer le compte') }}
                        </button>
                        
                        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                                @csrf
                                @method('delete')
                                
                                <h2 class="text-lg font-medium text-gray-900">{{ __('Êtes-vous sûr de vouloir supprimer votre compte ?') }}</h2>
                                <p class="mt-1 text-sm text-gray-600">{{ __('Une fois votre compte supprimé, tous ses ressources et données seront définitivement supprimées. Veuillez entrer votre mot de passe pour confirmer que vous souhaitez supprimer définitivement votre compte.') }}</p>
                                
                                <div class="mt-6">
                                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Mot de passe') }}</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" placeholder="{{ __('Mot de passe') }}">
                                    @error('password', 'userDeletion')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button x-on:click="$dispatch('close')" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ __('Annuler') }}
                                    </button>
                                    
                                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Supprimer le compte') }}
                                    </button>
                                </div>
                            </form>
                        </x-modal>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>
@endsection
