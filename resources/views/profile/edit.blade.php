@extends('layouts.main')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-6">{{ __('Profile Settings') }}</h1>
                
                <div class="mb-6">
                    <a href="{{ route('dashboard') }}" class="text-yellow-600 hover:underline">
                        &larr; {{ __('Back to Dashboard') }}
                    </a>
                </div>

                <!-- Success Message -->
                @if (session('status') === 'profile-updated')
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ __('Profile information updated successfully.') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="space-y-8">
                    <!-- Profile Information -->
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Profile Information') }}</h2>
                        <p class="mt-1 text-sm text-gray-600 mb-4">{{ __("Update your account's profile information and email address.") }}</p>
                        
                        <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
                            @csrf
                            @method('patch')
                            
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('name') border-red-300 @enderror">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Email -->
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required autocomplete="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('email') border-red-300 @enderror">
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                
                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-800">
                                            {{ __('Your email address is unverified.') }}
                                            
                                            <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                {{ __('Click here to re-send the verification email.') }}
                                            </button>
                                        </p>
                                        
                                        @if (session('status') === 'verification-link-sent')
                                            <p class="mt-2 font-medium text-sm text-green-600">
                                                {{ __('A new verification link has been sent to your email address.') }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Update Password -->
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Update Password') }}</h2>
                        <p class="mt-1 text-sm text-gray-600 mb-4">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
                        
                        <form method="post" action="{{ route('password.update') }}" class="space-y-6">
                            @csrf
                            @method('put')
                            
                            <!-- Current Password -->
                            <div>
                                <label for="current_password" class="block text-sm font-medium text-gray-700">{{ __('Current Password') }}</label>
                                <input type="password" name="current_password" id="current_password" autocomplete="current-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('current_password') border-red-300 @enderror">
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- New Password -->
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                                <input type="password" name="password" id="password" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500 @error('password') border-red-300 @enderror">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <!-- Confirm Password -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                                <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500">
                                @error('password_confirmation')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-500 border border-transparent rounded-md font-semibold text-xs text-gray-900 uppercase tracking-widest hover:bg-yellow-400 focus:bg-yellow-400 active:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    {{ __('Save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Delete Account -->
                    <div class="bg-white rounded-lg overflow-hidden border border-gray-200 p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ __('Delete Account') }}</h2>
                        <p class="mt-1 text-sm text-gray-600 mb-4">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted.') }}</p>
                        
                        <button
                            x-data=""
                            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            {{ __('Delete Account') }}
                        </button>
                        
                        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
                            <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
                                @csrf
                                @method('delete')
                                
                                <h2 class="text-lg font-medium text-gray-900">{{ __('Are you sure you want to delete your account?') }}</h2>
                                <p class="mt-1 text-sm text-gray-600">{{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}</p>
                                
                                <div class="mt-6">
                                    <label for="password" class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                                    <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500" placeholder="{{ __('Password') }}">
                                    @error('password', 'userDeletion')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="mt-6 flex justify-end">
                                    <button x-on:click="$dispatch('close')" type="button" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ __('Cancel') }}
                                    </button>
                                    
                                    <button type="submit" class="ml-3 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                        {{ __('Delete Account') }}
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
