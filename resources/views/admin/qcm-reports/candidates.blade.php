@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center">
                <a href="{{ route('admin.qcm-reports.index') }}" class="mr-4 text-indigo-600 hover:text-indigo-900">
                    <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Rapport des candidats') }}</h1>
            </div>
            <p class="mt-2 text-sm text-gray-700">{{ __('Voir les statistiques des examens pour tous les candidats.') }}</p>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <form action="{{ route('admin.qcm-reports.candidates') }}" method="GET" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700">{{ __('Rechercher') }}</label>
                            <div class="mt-1">
                                <input type="text" name="search" id="search" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" placeholder="{{ __('Nom ou email') }}" value="{{ request('search') }}">
                            </div>
                        </div>
                        <div>
                            <label for="sort" class="block text-sm font-medium text-gray-700">{{ __('Trier par') }}</label>
                            <div class="mt-1">
                                <select id="sort" name="sort" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>{{ __('Nom') }}</option>
                                    <option value="email" {{ request('sort') == 'email' ? 'selected' : '' }}>{{ __('Email') }}</option>
                                    <option value="exams" {{ request('sort') == 'exams' ? 'selected' : '' }}>{{ __('Nombre d\'examens') }}</option>
                                    <option value="passed" {{ request('sort') == 'passed' ? 'selected' : '' }}>{{ __('Nombre d\'examens passés') }}</option>
                                    <option value="rate" {{ request('sort') == 'rate' ? 'selected' : '' }}>{{ __('Taux de réussite') }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label for="direction" class="block text-sm font-medium text-gray-700">{{ __('Direction') }}</label>
                            <div class="mt-1">
                                <select id="direction" name="direction" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md">
                                    <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>{{ __('Ascendant') }}</option>
                                    <option value="desc" {{ request('direction') == 'desc' ? 'selected' : '' }}>{{ __('Descendant') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Filtrer') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Candidates List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Liste des candidats') }}</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Affichage de') }} {{ $candidates->firstItem() ?? 0 }} - {{ $candidates->lastItem() ?? 0 }} {{ __('sur') }} {{ $candidates->total() }} {{ __('candidats') }}</p>
            </div>
            <div class="border-t border-gray-200">
                @if($candidates->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Candidat') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Nombre d\'examens') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Completés') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Passés') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Taux de réussite') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($candidates as $candidate)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold">
                                                    {{ strtoupper(substr($candidate->name, 0, 1)) }}
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-gray-900">{{ $candidate->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $candidate->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $candidate->exam_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $candidate->completed_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $candidate->passed_count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($candidate->completed_count > 0)
                                                <div class="flex items-center">
                                                    <div class="mr-2 text-sm text-gray-900">
                                                        {{ round(($candidate->passed_count / $candidate->completed_count) * 100) }}%
                                                    </div>
                                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ ($candidate->passed_count / $candidate->completed_count) * 100 }}%"></div>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-500">{{ __('N/A') }}</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.qcm-reports.candidate-detail', $candidate) }}" class="text-indigo-600 hover:text-indigo-900">{{ __('Voir les détails') }}</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                        {{ $candidates->withQueryString()->links() }}
                    </div>
                @else
                    <div class="py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('Aucun candidat trouvé') }}</h3>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Aucun candidat ne correspond à vos critères de recherche.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection 