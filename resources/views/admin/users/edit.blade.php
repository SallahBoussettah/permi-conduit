@extends('layouts.main')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="px-4 py-6 sm:px-0">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Edit User') }}</h1>
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                </svg>
                {{ __('Back to Users') }}
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
                            <label for="name" class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
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
                            <label for="role_id" class="block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
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
                            <label for="permit_category_ids" class="block text-sm font-medium text-gray-700">{{ __('Permit Categories') }}</label>
                            <div class="mt-1">
                                <select id="permit_category_ids" name="permit_category_ids[]" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" multiple>
                                    @foreach($permitCategories as $id => $name)
                                        <option value="{{ $id }}" {{ in_array($id, $user->permitCategories->pluck('id')->toArray()) ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Hold Ctrl (or Cmd on Mac) to select multiple categories') }}</p>
                            @error('permit_category_ids')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            @error('permit_category_ids.*')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="approval_status" class="block text-sm font-medium text-gray-700">{{ __('Approval Status') }}</label>
                            <div class="mt-1">
                                <select id="approval_status" name="approval_status" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="pending" {{ old('approval_status', $user->approval_status) === 'pending' ? 'selected' : '' }}>{{ __('Pending') }}</option>
                                    <option value="approved" {{ old('approval_status', $user->approval_status) === 'approved' ? 'selected' : '' }}>{{ __('Approved') }}</option>
                                    <option value="rejected" {{ old('approval_status', $user->approval_status) === 'rejected' ? 'selected' : '' }}>{{ __('Rejected') }}</option>
                                </select>
                            </div>
                            @error('approval_status')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="is_active" class="block text-sm font-medium text-gray-700">{{ __('Account Status') }}</label>
                            <div class="mt-1">
                                <select id="is_active" name="is_active" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>{{ __('Active') }}</option>
                                    <option value="0" {{ old('is_active', $user->is_active) ? '' : 'selected' }}>{{ __('Inactive') }}</option>
                                </select>
                            </div>
                            @error('is_active')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div id="rejection_reason_container" class="sm:col-span-6 {{ old('approval_status', $user->approval_status) !== 'rejected' ? 'hidden' : '' }}">
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">{{ __('Rejection Reason') }}</label>
                            <div class="mt-1">
                                <textarea id="rejection_reason" name="rejection_reason" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">{{ old('rejection_reason', $user->rejection_reason) }}</textarea>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Required if status is set to Rejected') }}</p>
                            @error('rejection_reason')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="password" class="block text-sm font-medium text-gray-700">{{ __('New Password') }}</label>
                            <div class="mt-1">
                                <input type="password" name="password" id="password" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('Leave blank to keep current password') }}">
                            </div>
                            <p class="mt-1 text-sm text-gray-500">{{ __('Leave blank to keep current password') }}</p>
                            @error('password')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-3">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">{{ __('Confirm New Password') }}</label>
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
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit" class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Update User') }}
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