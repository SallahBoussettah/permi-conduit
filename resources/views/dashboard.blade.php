@extends('layouts.main')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <h1 class="text-2xl font-bold mb-6">{{ __('Dashboard') }}</h1>

                @if(Auth::user()->role)
                    @if(Auth::user()->role->name === 'admin')
                        <!-- Admin Dashboard -->
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
                            <h2 class="text-xl font-semibold text-blue-700">{{ __('Admin Dashboard') }}</h2>
                            <p class="text-blue-600">{{ __('Welcome to the administrator dashboard.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('User Management') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('Manage all system users.') }}</p>
                                <a href="#" class="text-blue-600 hover:underline">{{ __('View Users') }} →</a>
                            </div>
                            
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('Inspector Registration') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('Create and manage inspector accounts.') }}</p>
                                <a href="{{ route('admin.register.inspector') }}" class="text-blue-600 hover:underline">{{ __('Register Inspector') }} →</a>
                            </div>
                            
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('System Analytics') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('View system statistics and reports.') }}</p>
                                <a href="#" class="text-blue-600 hover:underline">{{ __('View Analytics') }} →</a>
                            </div>
                        </div>
                        
                    @elseif(Auth::user()->role->name === 'inspector')
                        <!-- Inspector Dashboard -->
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
                            <h2 class="text-xl font-semibold text-green-700">{{ __('Inspector Dashboard') }}</h2>
                            <p class="text-green-600">{{ __('Welcome to the inspector dashboard.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('Scheduled Exams') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('View your upcoming exams.') }}</p>
                                <a href="#" class="text-green-600 hover:underline">{{ __('View Schedule') }} →</a>
                            </div>
                            
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('Candidate Results') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('View and manage exam results.') }}</p>
                                <a href="#" class="text-green-600 hover:underline">{{ __('View Results') }} →</a>
                            </div>
                            
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('My Profile') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('Update your profile information.') }}</p>
                                <a href="{{ route('profile.edit') }}" class="text-green-600 hover:underline">{{ __('Edit Profile') }} →</a>
                            </div>
                        </div>
                        
                    @else
                        <!-- Candidate Dashboard -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6">
                            <h2 class="text-xl font-semibold text-yellow-700">{{ __('Candidate Dashboard') }}</h2>
                            <p class="text-yellow-600">{{ __('Welcome to your candidate dashboard.') }}</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('Course Materials') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('Access your learning materials.') }}</p>
                                <a href="#" class="text-yellow-600 hover:underline">{{ __('View Materials') }} →</a>
                            </div>
                            
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('QCM Practice') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('Practice with multiple choice questions.') }}</p>
                                <a href="#" class="text-yellow-600 hover:underline">{{ __('Start Practice') }} →</a>
                            </div>
                            
                            <div class="p-4 bg-white rounded-lg border border-gray-200 shadow-md">
                                <h3 class="font-bold text-lg mb-2">{{ __('My Profile') }}</h3>
                                <p class="text-gray-600 mb-4">{{ __('Update your profile information.') }}</p>
                                <a href="{{ route('profile.edit') }}" class="text-yellow-600 hover:underline">{{ __('Edit Profile') }} →</a>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="bg-red-50 border-l-4 border-red-500 p-4">
                        <p class="text-red-700">{{ __('Your role has not been assigned. Please contact an administrator.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
