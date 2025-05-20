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
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Statistiques QCM') }}</h1>
            </div>
            <p class="mt-2 text-sm text-gray-700">{{ __('Statistiques détaillées pour les examens QCM.') }}</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6">
            <div class="sm:hidden">
                <label for="tabs" class="sr-only">{{ __('Select a tab') }}</label>
                <select id="tabs" name="tabs" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="category" {{ $activeTab == 'category' ? 'selected' : '' }}>{{ __('Par catégorie de permis') }}</option>
                    <option value="paper" {{ $activeTab == 'paper' ? 'selected' : '' }}>{{ __('Par QCM') }}</option>
                    <option value="month" {{ $activeTab == 'month' ? 'selected' : '' }}>{{ __('Par mois') }}</option>
                </select>
            </div>
            <div class="hidden sm:block">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                        <a href="{{ route('admin.qcm-reports.statistics', ['tab' => 'category']) }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab == 'category' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Par catégorie de permis') }}
                        </a>
                        <a href="{{ route('admin.qcm-reports.statistics', ['tab' => 'paper']) }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab == 'paper' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Par QCM') }}
                        </a>
                        <a href="{{ route('admin.qcm-reports.statistics', ['tab' => 'month']) }}" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm {{ $activeTab == 'month' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ __('Par mois') }}
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Statistics Content -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">
                    @if($activeTab == 'category')
                        {{ __('Statistiques par catégorie de permis') }}
                    @elseif($activeTab == 'paper')
                        {{ __('Statistiques par QCM') }}
                    @else
                        {{ __('Statistiques par mois') }}
                    @endif
                </h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    @if($activeTab == 'category')
                        {{ __('Statistiques regroupées par catégorie de permis.') }}
                    @elseif($activeTab == 'paper')
                        {{ __('Statistiques regroupées par QCM.') }}
                    @else
                        {{ __('Statistiques regroupées par mois.') }}
                    @endif
                </p>
            </div>
            <div class="border-t border-gray-200">
                @if($activeTab == 'category')
                    <!-- By Permit Category -->
                    @if(count($categoryStats) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Catégorie de permis') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total des examens') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Completés') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Passés') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Moyenne des notes') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Taux de réussite') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($categoryStats as $stat)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $stat->name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->total_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->completed_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->passed_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ round($stat->avg_score, 1) }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="mr-2 text-sm text-gray-900">
                                                        {{ round($stat->pass_rate, 1) }}%
                                                    </div>
                                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $stat->pass_rate }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <p class="text-sm text-gray-500">{{ __('Aucune statistique disponible.') }}</p>
                        </div>
                    @endif

                @elseif($activeTab == 'paper')
                    <!-- By QCM Paper -->
                    @if(count($paperStats) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('QCM') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Catégorie de permis') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total des examens') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Completés') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Passés') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Moyenne des notes') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Taux de réussite') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($paperStats as $stat)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{ $stat->title }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->category_name }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->total_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->completed_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $stat->passed_count }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ round($stat->avg_score, 1) }}%
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="mr-2 text-sm text-gray-900">
                                                        {{ round($stat->pass_rate, 1) }}%
                                                    </div>
                                                    <div class="w-16 bg-gray-200 rounded-full h-2">
                                                        <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $stat->pass_rate }}%"></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <p class="text-sm text-gray-500">{{ __('Aucune statistique disponible.') }}</p>
                        </div>
                    @endif

                @else
                    <!-- By Month -->
                    <div class="px-4 py-5 sm:p-6">
                        <div class="h-96">
                            <canvas id="monthlyStatsChart"></canvas>
                        </div>
                    </div>

                    @if(count($monthStats) > 0)
                        <div class="border-t border-gray-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Mois') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total des examens') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Completés') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Passés') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Moyenne des notes') }}</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Taux de réussite') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($monthStats as $stat)
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {{ $stat->month }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $stat->total_count }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $stat->completed_count }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $stat->passed_count }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {{ round($stat->avg_score, 1) }}%
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="mr-2 text-sm text-gray-900">
                                                            {{ round($stat->pass_rate, 1) }}%
                                                        </div>
                                                        <div class="w-16 bg-gray-200 rounded-full h-2">
                                                            <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $stat->pass_rate }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <p class="text-sm text-gray-500">{{ __('Aucune statistique disponible.') }}</p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@if($activeTab == 'month')
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Monthly Stats Chart
        const monthlyStatsCtx = document.getElementById('monthlyStatsChart').getContext('2d');
        const monthlyStatsChart = new Chart(monthlyStatsCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($monthStats->pluck('month')) !!},
                datasets: [{
                    label: '{{ __('Total des examens') }}',
                    data: {!! json_encode($monthStats->pluck('total_count')) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.6)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1
                }, {
                    label: '{{ __('Completés') }}',
                    data: {!! json_encode($monthStats->pluck('completed_count')) !!},
                    backgroundColor: 'rgba(59, 130, 246, 0.6)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }, {
                    label: '{{ __('Passés') }}',
                    data: {!! json_encode($monthStats->pluck('passed_count')) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.6)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endif
@endsection 