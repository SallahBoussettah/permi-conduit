@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ __('Rapports QCM') }}</h1>
                <p class="mt-2 text-sm text-gray-700">{{ __('Afficher les statistiques et les rapports pour les examens QCM.') }}</p>
            </div>
            <div>
                <a href="{{ route('admin.qcm-reports.export') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    {{ __('Exporter les données') }}
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="mb-8 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Exams -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('Total des examens') }}</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ $totalExams }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed Exams -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('Exams terminés') }}</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ $completedExams }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Passed Exams -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('Exams réussis') }}</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ $passedExams }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pass Rate -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">{{ __('Taux de réussite') }}</dt>
                                <dd class="text-3xl font-semibold text-gray-900">{{ $passRate }}%</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Recent Exams -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Exams récents') }}</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Derniers examens QCM passés par les candidats.') }}</p>
                    </div>
                </div>
                <div class="border-t border-gray-200">
                    @if(count($recentExams) > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Candidat') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Examen') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Note') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Statut') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($recentExams as $exam)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ $exam->user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $exam->user->email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">{{ $exam->paper->title }}</div>
                                                <div class="text-sm text-gray-500">{{ $exam->paper->permitCategory->name }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($exam->completed_at)
                                                    <div class="text-sm text-gray-900">{{ $exam->score }} / {{ $exam->total_points }}</div>
                                                    <div class="text-sm text-gray-500">{{ $exam->total_points > 0 ? round(($exam->score / $exam->total_points) * 100) : 0 }}%</div>
                                                @else
                                                    <span class="text-sm text-yellow-500">{{ __('En cours') }}</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($exam->completed_at)
                                                    @if($exam->passed)
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                            {{ __('Réussi') }}
                                                        </span>
                                                    @else
                                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                            {{ __('Échoué') }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                        {{ __('Incomplet') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                {{ $exam->created_at->format('d/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="py-12 text-center">
                            <p class="text-sm text-gray-500">{{ __('Aucun examen passé pour le moment.') }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Exams by Paper -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Exams par QCM') }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Nombre d\'examens passés pour chaque QCM.') }}</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <div class="h-80">
                        <canvas id="examsByPaperChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- Monthly Exams -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Statistiques mensuelles') }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Nombre d\'examens passés chaque mois.') }}</p>
                </div>
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <div class="h-80">
                        <canvas id="monthlyExamsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Candidates -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Top candidats') }}</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Candidats avec le plus d\'examens passés.') }}</p>
                    </div>
                    <a href="{{ route('admin.qcm-reports.candidates') }}" class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm leading-5 font-medium rounded-md text-gray-700 bg-white hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50 transition ease-in-out duration-150">
                        {{ __('Voir tous') }}
                    </a>
                </div>
                <div class="border-t border-gray-200">
                    @if(count($topCandidates) > 0)
                        <ul class="divide-y divide-gray-200">
                            @foreach($topCandidates as $candidate)
                                <li>
                                    <a href="{{ route('admin.qcm-reports.candidate-detail', $candidate->user) }}" class="block hover:bg-gray-50">
                                        <div class="px-4 py-4 sm:px-6">
                                            <div class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0">
                                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-semibold">
                                                            {{ strtoupper(substr($candidate->user->name, 0, 1)) }}
                                                        </div>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-indigo-600 truncate">{{ $candidate->user->name }}</div>
                                                        <div class="text-sm text-gray-500">{{ $candidate->user->email }}</div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="flex items-center">
                                                        <div class="text-sm text-gray-900 mr-2">{{ $candidate->exam_count }} {{ __('examens') }}</div>
                                                        <div class="text-sm text-gray-900">
                                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                                {{ $candidate->passed_count }} {{ __('réussis') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="py-12 text-center">
                            <p class="text-sm text-gray-500">{{ __('Aucun candidat n\'a passé d\'examens pour le moment.') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Exams by Paper Chart
        const examsByPaperCtx = document.getElementById('examsByPaperChart').getContext('2d');
        const examsByPaperChart = new Chart(examsByPaperCtx, {
            type: 'bar',
            data: {
                labels: {!! isset($examsByPaper) && $examsByPaper->count() > 0 ? json_encode($examsByPaper->pluck('title')) : '[]' !!},
                datasets: [{
                    label: '{{ __('Total des examens') }}',
                    data: {!! isset($examsByPaper) && $examsByPaper->count() > 0 ? json_encode($examsByPaper->pluck('exam_count')) : '[]' !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.6)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 1
                }, {
                    label: '{{ __('Exams réussis') }}',
                    data: {!! isset($examsByPaper) && $examsByPaper->count() > 0 ? json_encode($examsByPaper->pluck('passed_count')) : '[]' !!},
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

        // Monthly Exams Chart
        const monthlyExamsCtx = document.getElementById('monthlyExamsChart').getContext('2d');
        const monthlyExamsChart = new Chart(monthlyExamsCtx, {
            type: 'line',
            data: {
                labels: {!! isset($examsByMonth) && !empty($examsByMonth) ? json_encode($examsByMonth->pluck('month')) : '[]' !!},
                datasets: [{
                    label: '{{ __('Total Exams') }}',
                    data: {!! isset($examsByMonth) && !empty($examsByMonth) ? json_encode($examsByMonth->pluck('exam_count')) : '[]' !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.2)',
                    borderColor: 'rgba(79, 70, 229, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }, {
                    label: '{{ __('Passed Exams') }}',
                    data: {!! isset($examsByMonth) && !empty($examsByMonth) ? json_encode($examsByMonth->pluck('passed_count')) : '[]' !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.2)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
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
@endsection 