@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between">
                <div class="flex-1 min-w-0">
                    <div class="flex md:items-center">
                        @if($course->thumbnail)
                            <div class="flex-shrink-0 h-24 w-24 rounded-lg overflow-hidden bg-gray-100 mr-4">
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}" class="h-24 w-24 object-cover">
                            </div>
                        @endif
                        <div>
                            <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl sm:tracking-tight">
                                {{ $course->title }}
                            </h1>
                            @if($course->category)
                            <p class="mt-2 text-lg text-gray-500">
                                {{ $course->category->name }}
                            </p>
                            @endif
                            @if($course->examSection)
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $course->examSection->name }}
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 flex-shrink-0">
                    <a href="{{ route('candidate.courses.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        {{ __('Back to Courses') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="mb-6 rounded-md bg-green-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Error Message -->
        @if(session('error'))
            <div class="mb-6 rounded-md bg-red-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <!-- Course Description -->
                @if($course->description)
                <div class="mb-8 bg-gray-50 p-4 rounded-md">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">{{ __('About this course') }}</h3>
                    <p class="text-gray-700">{{ $course->description }}</p>
                </div>
                @endif

                <!-- Course Progress -->
                <div class="mb-8">
                    <div class="flex items-center mb-2">
                        <h3 class="text-lg font-medium text-gray-900">{{ __('Course Progress') }}</h3>
                        <span class="ml-2 px-2.5 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            {{ $course->progress_percentage }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-yellow-500 h-3 rounded-full" style="width: {{ $course->progress_percentage }}%"></div>
                    </div>
                </div>

                <!-- Course Materials -->
                <div class="mb-4">
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">{{ __('Course Materials') }}</h3>

                    @if(count($courseMaterials) > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($courseMaterials as $material)
                            <div class="border rounded-lg overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow duration-300">
                                <div class="aspect-w-4 aspect-h-3">
                                    <img src="{{ $material->thumbnail_path ? asset('storage/' . $material->thumbnail_path) : asset('images/default-pdf-thumbnail.png') }}" 
                                         alt="{{ $material->title }}" 
                                         class="w-full h-48 object-cover" />
                                </div>
                                <div class="p-4">
                                    @php
                                        $materialProgress = $progress[$material->id] ?? null;
                                        $status = $materialProgress ? $materialProgress->status : 'not_started';
                                        $completionPercentage = $materialProgress ? $materialProgress->completion_percentage : 0;
                                    @endphp
                                    
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-md font-medium text-gray-900">{{ $material->title }}</h4>
                                        
                                        @if($status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('Completed') }}
                                        </span>
                                        @elseif($status === 'in_progress')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('In Progress') }}
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ __('Not Started') }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Material Type Badge -->
                                    <div class="mb-2">
                                        @if($material->material_type === 'video')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"></path>
                                            </svg>
                                            {{ __('YouTube Video') }}
                                        </span>
                                        @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('PDF Document') }}
                                        </span>
                                        @endif
                                    </div>
                                    
                                    @if($material->description)
                                    <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $material->description }}</p>
                                    @endif
                                    
                                    <div class="mt-2">
                                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                            <span>{{ __('Progress') }}</span>
                                            <span>{{ $completionPercentage }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $completionPercentage }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <a href="{{ route('candidate.courses.materials.show', ['course' => $course, 'material' => $material]) }}" 
                                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                            @if($status === 'completed')
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                {{ __('Review Material') }}
                                            @elseif($status === 'in_progress')
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                {{ __('Continue Learning') }}
                                            @else
                                                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                                </svg>
                                                {{ __('Start Learning') }}
                                            @endif
                                        </a>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 rounded-lg p-8 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <h3 class="mt-2 text-lg font-medium text-gray-900">{{ __('No materials available') }}</h3>
                            <p class="mt-1 text-sm text-gray-500">{{ __('No materials available for this course yet.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 