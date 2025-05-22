@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <!-- Header with timer -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $exam->paper->title }}</h1>
                        <p class="mt-1 text-sm text-gray-500">{{ __('Catégorie de permis') }}: {{ $exam->paper->permitCategory->name }}</p>
                    </div>
                    <div class="flex flex-col items-end">
                        <div class="text-sm text-gray-500">{{ __('Temps restant') }}</div>
                        <div id="timer-display" class="text-2xl font-bold">--:--</div>
                    </div>
                </div>
                
                <!-- Progress bar -->
                <div class="mt-4">
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-1">
                        <div>{{ __('Progression') }}</div>
                        <div><span id="answered-count">0</span>/{{ count($exam->questions) }}</div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="exam-progress-bar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question navigation tabs -->
        <div class="bg-white shadow rounded-lg mb-6 p-4 overflow-x-auto">
            <div class="flex space-x-2">
                @foreach($exam->questions as $index => $question)
                    <button 
                        class="question-tab px-3 py-1 rounded-md text-sm font-medium bg-gray-100 text-gray-700 hover:bg-gray-200"
                        data-question-index="{{ $index }}"
                    >
                        {{ $index + 1 }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Exam form -->
        <form id="exam-form" action="{{ route('candidate.qcm-exams.submit', $exam) }}" method="POST" data-exam-id="{{ $exam->id }}" data-duration="{{ $exam->paper->duration }}" data-start-time="{{ $exam->started_at->timestamp }}">
            @csrf
            
            <!-- Questions -->
            @foreach($exam->questions as $index => $question)
                <div class="question-container hidden bg-white shadow rounded-lg mb-6" data-question-id="{{ $question->id }}">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-medium text-gray-900">{{ __('Question') }} {{ $index + 1 }} {{ __('of') }} {{ count($exam->questions) }}</h3>
                        </div>
                        
                        <div class="mb-6">
                            <p class="text-base text-gray-900">{{ $question->text }}</p>
                            
                            @if($question->image)
                                <div class="mt-4">
                                    <img src="{{ asset('storage/' . $question->image) }}" alt="{{ __('Question image') }}" class="max-h-64 rounded-md">
                                </div>
                            @endif
                        </div>
                        
                        <div class="space-y-4">
                            @foreach($question->options as $option)
                                <div class="flex items-center">
                                    <input 
                                        type="radio" 
                                        id="option_{{ $option->id }}" 
                                        name="question_{{ $question->id }}" 
                                        value="{{ $option->id }}"
                                        {{ isset($answers[$question->id]) && $answers[$question->id] == $option->id ? 'checked' : '' }}
                                        class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500"
                                    >
                                    <label for="option_{{ $option->id }}" class="ml-3 block text-sm font-medium text-gray-700">
                                        {{ $option->text }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    
                    <div class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-between">
                        <button type="button" class="prev-question inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ __('Précédent') }}
                        </button>
                        
                        @if($index == count($exam->questions) - 1)
                            <button type="submit" id="submit-exam" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Soumettre l\'examen') }}
                            </button>
                        @else
                            <button type="button" class="next-question inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                {{ __('Suivant') }}
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
            
            <!-- Submit button (fixed at bottom for mobile) -->
            <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 p-4 md:hidden">
                <button type="submit" id="submit-exam-mobile" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Soumettre l\'examen') }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="{{ asset('js/qcm-exam.js') }}"></script>
@endpush
@endsection 