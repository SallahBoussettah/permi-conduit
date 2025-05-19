@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('QCM Exam Results') }}</h1>
                <a href="{{ route('candidate.qcm-exams.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    {{ __('Back to Exams') }}
                </a>
            </div>
            <p class="mt-2 text-sm text-gray-700">{{ $qcmExam->paper->title }} - {{ $qcmExam->paper->permitCategory->name }}</p>
        </div>

        <!-- Results Summary -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Exam Summary') }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $qcmExam->completed_at->format('d/m/Y H:i') }}</p>
                </div>
                @if($qcmExam->is_eliminatory)
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        {{ __('Eliminatory') }}
                    </span>
                @elseif($qcmExam->points_earned >= 1)
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        {{ __('Passed') }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                        {{ __('Not Eliminatory') }}
                    </span>
                @endif
            </div>
            <div class="border-t border-gray-200">
                <dl>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Score') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="font-bold text-lg">{{ $qcmExam->correct_answers_count }}</span> / {{ $qcmExam->total_questions }} 
                            {{ __('correct answers') }} ({{ number_format($percentage, 1) }}%)
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Points Earned') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            <span class="font-bold text-lg">{{ $qcmExam->points_earned }}</span> / 3
                            <span class="text-sm text-gray-500 ml-2">
                                <!-- Display the grading scale information -->
                                @if($qcmExam->correct_answers_count >= 9)
                                    ({{ __('9-10 correct answers: 3 points') }})
                                @elseif($qcmExam->correct_answers_count >= 7)
                                    ({{ __('7-8 correct answers: 2 points') }})
                                @elseif($qcmExam->correct_answers_count == 6)
                                    ({{ __('6 correct answers: 1 point') }})
                                @else
                                    ({{ __('5 or fewer correct answers: 0 points (eliminatory)') }})
                                @endif
                            </span>
                        </dd>
                    </div>
                    <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Duration') }}</dt>
                        <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                            {{ floor($qcmExam->duration_seconds / 60) }}m {{ $qcmExam->duration_seconds % 60 }}s 
                            <span class="text-sm text-gray-500 ml-2">({{ __('Maximum: 6 minutes') }})</span>
                        </dd>
                    </div>
                    <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                        <dt class="text-sm font-medium text-gray-500">{{ __('Status') }}</dt>
                        <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">
                            @if($qcmExam->status === 'completed')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ __('Completed') }}
                                </span>
                            @elseif($qcmExam->status === 'timed_out')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ __('Timed Out') }}
                                </span>
                            @endif

                            <div class="mt-3">
                                @if($qcmExam->is_eliminatory)
                                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mt-2">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-red-700">
                                                    {{ __('This result is eliminatory. You need at least 6 correct answers to pass.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @elseif($qcmExam->points_earned >= 1)
                                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mt-2">
                                        <div class="flex">
                                            <div class="flex-shrink-0">
                                                <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm text-green-700">
                                                    {{ __('You have passed this QCM exam with') }} {{ $qcmExam->points_earned }} {{ __('points.') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Question Review -->
        <div class="bg-white shadow overflow-hidden sm:rounded-lg">
            <div class="px-4 py-5 sm:px-6">
                <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Question Review') }}</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ __('Review your answers and see the correct solutions.') }}</p>
            </div>
            <div class="border-t border-gray-200">
                <div class="divide-y divide-gray-200">
                    @foreach($examAnswers as $answer)
                        <div class="px-4 py-5 sm:px-6 @if($answer->is_correct) bg-green-50 @else bg-red-50 @endif">
                            <div class="mb-4 flex justify-between">
                                <h4 class="text-md font-medium text-gray-900 flex items-center">
                                    <span class="mr-2">{{ __('Question') }} {{ $loop->iteration }}:</span>
                                    @if($answer->is_correct)
                                        <svg class="h-5 w-5 text-green-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                </h4>
                            </div>
                            
                            <div class="text-md text-gray-900 mb-4">{{ $answer->question->question_text }}</div>
                            
                            @if($answer->question->image_path)
                                <div class="mb-4">
                                    <img src="{{ $answer->question->image_url }}" alt="Question Image" class="max-w-full h-auto max-h-64 rounded-lg">
                                </div>
                            @endif
                            
                            <div class="space-y-3">
                                @foreach($answer->question->answers as $option)
                                    <div class="relative flex items-start p-3 rounded-lg 
                                        @if($option->id === $answer->qcm_answer_id && $option->is_correct) bg-green-100 border border-green-300
                                        @elseif($option->id === $answer->qcm_answer_id && !$option->is_correct) bg-red-100 border border-red-300
                                        @elseif($option->is_correct) bg-green-50 border border-green-200
                                        @else bg-white border border-gray-200
                                        @endif">
                                        <div class="flex items-center h-5">
                                            <div class="flex-shrink-0 h-4 w-4 @if($option->id === $answer->qcm_answer_id) bg-blue-600 @else bg-gray-200 @endif rounded-full"></div>
                                        </div>
                                        <div class="ml-3 text-sm flex justify-between w-full">
                                            <div>
                                                <span class="font-medium text-gray-900">{{ $option->answer_text }}</span>
                                            </div>
                                            <div>
                                                @if($option->is_correct)
                                                    <span class="text-xs font-medium text-green-800 bg-green-100 px-2 py-1 rounded-full">{{ __('Correct Answer') }}</span>
                                                @endif
                                                @if($option->id === $answer->qcm_answer_id && !$option->is_correct)
                                                    <span class="text-xs font-medium text-red-800 bg-red-100 px-2 py-1 rounded-full">{{ __('Your Answer') }}</span>
                                                @elseif($option->id === $answer->qcm_answer_id && $option->is_correct)
                                                    <span class="text-xs font-medium text-green-800 bg-green-100 px-2 py-1 rounded-full">{{ __('Your Answer (Correct)') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if($answer->question->explanation)
                                <div class="mt-4 p-3 bg-indigo-50 rounded-lg">
                                    <h5 class="text-sm font-medium text-indigo-800 mb-1">{{ __('Explanation:') }}</h5>
                                    <p class="text-sm text-indigo-700">{{ $answer->question->explanation }}</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="mt-8 flex justify-between">
            <a href="{{ route('candidate.qcm-exams.available') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Take Another Exam') }}
            </a>
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                {{ __('Return to Dashboard') }}
            </a>
        </div>
    </div>
</div>
@endsection 