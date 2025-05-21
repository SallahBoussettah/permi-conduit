@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="flex-1 min-w-0">
                    <h1 class="text-3xl font-extrabold text-gray-900 sm:text-4xl sm:tracking-tight">
                        {{ __('Mes formations') }}
                    </h1>
                    <p class="mt-2 text-lg text-gray-500">
                        {{ __('Accéder à vos formations assignées') }}
                    </p>
                </div>
                <div class="mt-4 md:mt-0 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        {{ __('Tableau de bord') }}
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

        <!-- Permit Category Notice -->
        <div class="mb-6 rounded-md bg-blue-50 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3 flex-1 md:flex md:justify-between">
                    <div class="text-sm text-blue-700">
                        @if(isset($activePermitCategories) && $activePermitCategories->count() > 0)
                            <p>{{ __('Vous voyez les cours pour ces catégories de permis:') }}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($activePermitCategories as $category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $category->name }} ({{ $category->code }})
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <p>{{ __('Vous ne voyez que les cours généraux qui ne sont pas spécifiques à une catégorie de permis.') }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if(isset($permitCategoryInactive) && $permitCategoryInactive && isset($userPermitCategories))
            <div class="mb-6 rounded-md bg-yellow-50 p-4 border border-yellow-200">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">{{ __('Certaines catégories de permis sont indisponibles') }}</h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>{{ __('Certaines de vos catégories de permis sont actuellement inactives:') }}</p>
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach($userPermitCategories->where('status', false) as $inactiveCategory)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $inactiveCategory->name }} ({{ $inactiveCategory->code }})
                                    </span>
                                @endforeach
                            </div>
                            <p class="mt-1">{{ __('Ces catégories de permis seront disponibles bientôt. Vérifiez vos cours plus tard.') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Permit Category Filter -->
        <div class="mb-6">
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Filtrer les cours') }}</h3>
                    <div class="mt-4">
                        <form action="{{ route('candidate.courses.index') }}" method="GET" class="space-y-4 sm:space-y-0 sm:flex sm:items-end sm:space-x-4">
                            <div class="flex-1">
                                <label for="permit_category" class="block text-sm font-medium text-gray-700 mb-1">{{ __('Catégorie de permis') }}</label>
                                <select id="permit_category" name="permit_category" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <option value="">{{ __('Toutes les catégories') }}</option>
                                    @foreach($activePermitCategories as $category)
                                        <option value="{{ $category->id }}" {{ request('permit_category') == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }} ({{ $category->code }})
                                        </option>
                                    @endforeach
                                    <option value="null" {{ request('permit_category') === 'null' ? 'selected' : '' }}>{{ __('Seulement les cours généraux') }}</option>
                                </select>
                            </div>
                            <div class="flex space-x-2">
                                <button type="submit" class="inline-flex justify-center rounded-md border border-transparent bg-yellow-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    {{ __('Filtrer') }}
                                </button>
                                <a href="{{ route('candidate.courses.index') }}" class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2">
                                    {{ __('Réinitialiser') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if($courses->isEmpty())
            <!-- Empty State -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-16 sm:px-6 text-center">
                    <svg class="mx-auto h-16 w-16 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">{{ __('Aucun cours trouvé') }}</h3>
                    <p class="mt-2 text-sm text-gray-500">{{ __('Vous n\'avez pas été assigné à aucun cours pour le moment.') }}</p>
                </div>
            </div>
        @else
            <!-- Course Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($courses as $course)
                    <div class="bg-white rounded-lg shadow overflow-hidden transition-all duration-300 hover:shadow-lg hover:border-yellow-300 hover:border border border-gray-200">
                        <!-- Course Thumbnail -->
                        <div class="relative h-40 bg-gray-200">
                            @if($course->thumbnail)
                                <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                            @else
                                <div class="flex items-center justify-center w-full h-full bg-yellow-100 text-yellow-700">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            @endif
                            
                            <!-- Permit Category Badge -->
                            @if($course->permit_category_id)
                                <div class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        {{ $course->permitCategory->code }}
                                    </span>
                                </div>
                            @else
                                <div class="absolute top-2 right-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        General
                                    </span>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Course Content -->
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <h3 class="text-lg font-medium text-gray-900 mb-1">{{ $course->title }}</h3>
                                
                                @if($course->progress_percentage == 100)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ __('Complété') }}
                                    </span>
                                @elseif($course->progress_percentage > 0)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="mr-1 h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ __('En cours') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ __('Pas commencé') }}
                                    </span>
                                @endif
                            </div>
                            
                            @if($course->category)
                                <p class="text-sm text-gray-500 mb-3">{{ $course->category->name }}</p>
                            @endif
                            
                            @if($course->permitCategory)
                                <div class="mt-1 flex items-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                        <svg class="mr-1.5 h-2 w-2 text-indigo-400" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3" />
                                        </svg>
                                        {{ __('Catégorie de permis:') }} {{ $course->permitCategory->code }}
                                    </span>
                                </div>
                            @endif
                            
                            @if($course->description)
                                <p class="mt-2 text-sm text-gray-600 line-clamp-2">{{ $course->description }}</p>
                            @endif
                            
                            <div class="mt-4">
                                <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                    <span>{{ __('Progression') }}</span>
                                    <span>{{ $course->progress_percentage }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $course->progress_percentage }}%"></div>
                                </div>
                            </div>
                            
                            <div class="mt-5 flex items-center justify-between">
                                <div class="flex items-center text-sm text-gray-500">
                                    <svg class="flex-shrink-0 mr-1.5 h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    {{ $course->materials_count ?? 0 }} {{ __('Matériaux') }}
                                </div>
                                
                                <a href="{{ route('candidate.courses.show', $course) }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-500 hover:bg-yellow-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                                    @if($course->progress_percentage > 0 && $course->progress_percentage < 100)
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ __('Continuer') }}
                                    @elseif($course->progress_percentage == 100)
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        {{ __('Revoir') }}
                                    @else
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                        {{ __('Commencer') }}
                                    @endif
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination Links -->
            @if($courses->hasPages())
                <div class="mt-6">
                    <div class="flex justify-between items-center">
                        <div>
                            {{ __('Affichage de') }} {{ $courses->firstItem() }} {{ __('à') }} {{ $courses->lastItem() }} {{ __('sur') }} {{ $courses->total() }} {{ __('résultats') }}
                        </div>
                        <div>
                            {{ $courses->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>
@endsection 