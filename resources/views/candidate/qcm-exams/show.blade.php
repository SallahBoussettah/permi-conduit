@extends('layouts.main')

@section('content')
<div class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $qcmExam->paper->title }}</h1>
                <p class="mt-2 text-sm text-gray-700">{{ $qcmExam->paper->permitCategory->name }}</p>
                <p class="mt-2 text-sm font-medium text-red-600">
                    {{ __('This QCM has 10 questions to be completed in 6 minutes. You need at least 6 correct answers to pass.') }}
                </p>
            </div>
            <div class="flex items-center">
                <div class="px-4 py-2 bg-white shadow rounded-lg mr-4">
                    <div class="text-sm text-gray-500">{{ __('Time Remaining') }}</div>
                    <div id="timer" class="text-2xl font-bold text-gray-900" 
                         data-end-time="{{ $qcmExam->expires_at ? $qcmExam->expires_at->timestamp : now()->addMinutes(6)->timestamp }}"
                         data-remaining-seconds="{{ $remainingTime }}">
                        {{ sprintf('%02d:%02d', floor($remainingTime / 60), $remainingTime % 60) }}
                    </div>
                </div>
                <div class="px-4 py-2 bg-white shadow rounded-lg">
                    <div class="text-sm text-gray-500">{{ __('Questions') }}</div>
                    <div class="text-2xl font-bold text-gray-900">
                        <span id="current-question">1</span> / {{ count($questions) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Exam Form -->
        <form id="exam-form" action="{{ route('candidate.qcm-exams.submit', $qcmExam) }}" method="POST">
            @csrf
            
            <!-- Progress Bar -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg mb-6">
                <div class="px-4 py-3">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div id="progress-bar" class="bg-indigo-600 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                </div>
                <!-- Question Navigation Buttons -->
                <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 flex flex-wrap gap-2">
                    @foreach($questions as $index => $question)
                        <button type="button" 
                            data-question="{{ $index + 1 }}" 
                            class="question-nav-btn w-8 h-8 rounded-full text-sm font-medium 
                                {{ isset($examAnswers[$question->id]) ? 'bg-indigo-600 text-white' : 'bg-white border border-gray-300 text-gray-700' }}
                                hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            {{ $index + 1 }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Questions -->
            <div id="questions-container" class="space-y-6">
                @foreach($questions as $index => $question)
                    <div id="question-{{ $index + 1 }}" class="question-slide bg-white shadow overflow-hidden sm:rounded-lg {{ $index > 0 ? 'hidden' : '' }}">
                        <div class="px-4 py-5 sm:px-6 flex justify-between items-center">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">{{ __('Question') }} {{ $index + 1 }}</h3>
                        </div>
                        <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                            <div class="mb-6">
                                <p class="text-base text-gray-900">{{ $question->question_text }}</p>
                                @if($question->image_path)
                                    <div class="mt-4">
                                        <img src="{{ $question->image_url }}" alt="Question Image" class="max-w-full h-auto rounded-lg">
                                    </div>
                                @endif
                            </div>
                            
                            <div class="space-y-4">
                                <input type="hidden" name="questions[{{ $question->id }}][id]" value="{{ $question->id }}">
                                
                                @foreach($question->answers as $answer)
                                    <div class="relative flex items-start py-2 px-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors {{ isset($examAnswers[$question->id]) && $examAnswers[$question->id]->qcm_answer_id == $answer->id ? 'bg-indigo-50 border-indigo-300' : '' }}">
                                        <div class="flex items-center h-5">
                                            <input 
                                                id="answer-{{ $question->id }}-{{ $answer->id }}" 
                                                name="questions[{{ $question->id }}][answer_id]" 
                                                value="{{ $answer->id }}" 
                                                type="radio" 
                                                class="answer-radio focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300" 
                                                {{ isset($examAnswers[$question->id]) && $examAnswers[$question->id]->qcm_answer_id == $answer->id ? 'checked' : '' }}
                                                data-question-id="{{ $question->id }}"
                                                data-answer-id="{{ $answer->id }}">
                                        </div>
                                        <div class="ml-3 text-sm flex-grow">
                                            <label for="answer-{{ $question->id }}-{{ $answer->id }}" class="font-medium text-gray-700 cursor-pointer">
                                                {{ $answer->answer_text }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-4 sm:px-6 flex justify-between">
                            <button type="button" 
                                class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 {{ $index === 0 ? 'invisible' : '' }}"
                                onclick="document.getElementById('question-{{ $index + 1 }}').classList.add('hidden'); document.getElementById('question-{{ $index }}').classList.remove('hidden'); document.getElementById('current-question').textContent = '{{ $index }}'; document.getElementById('progress-bar').style.width = '{{ ($index / count($questions)) * 100 }}%';">
                                {{ __('Previous') }}
                            </button>
                            
                            @if($index === count($questions) - 1)
                                <button type="button" 
                                    onclick="document.getElementById('confirmation-modal').classList.remove('hidden');"
                                    class="finish-exam-btn ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Finish Exam') }}
                                </button>
                            @else
                                <button type="button"
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    onclick="document.getElementById('question-{{ $index + 1 }}').classList.add('hidden'); document.getElementById('question-{{ $index + 2 }}').classList.remove('hidden'); document.getElementById('current-question').textContent = '{{ $index + 2 }}'; document.getElementById('progress-bar').style.width = '{{ (($index + 2) / count($questions)) * 100 }}%';">
                                    {{ __('Next') }}
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Confirmation Modal -->
            <div id="confirmation-modal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-yellow-100 sm:mx-0 sm:h-10 sm:w-10">
                                    <svg class="h-6 w-6 text-yellow-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">{{ __('Submit Exam') }}</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            {{ __('Are you sure you want to submit your exam? You cannot change your answers after submission.') }}
                                        </p>
                                        <div id="unanswered-warning" class="mt-2 text-sm text-red-600 hidden">
                                            {{ __('Warning: You have unanswered questions.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" 
                                onclick="document.getElementById('exam-form').submit();" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Submit') }}
                            </button>
                            <button type="button" id="cancel-submit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Continue Exam') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Wait for the DOM to be fully loaded before running the script
document.addEventListener('DOMContentLoaded', function() {
    // Get references to important elements
    const timerElement = document.getElementById('timer');
    const examForm = document.getElementById('exam-form');
    const totalQuestions = {{ count($questions) }};
    const navButtons = document.querySelectorAll('.question-nav-btn');
    
    // Get timer data from server
    const endTimeTimestamp = parseInt(timerElement.getAttribute('data-end-time')) * 1000; // convert to milliseconds
    const serverRemainingTime = parseInt(timerElement.getAttribute('data-remaining-seconds')); // seconds
    
    // Set up timer variables
    let timeRemaining; 
    
    // Determine the starting time - prefer server-calculated time if available
    if (!isNaN(serverRemainingTime) && serverRemainingTime > 0 && serverRemainingTime <= 360) {
        timeRemaining = serverRemainingTime;
        console.log("Using server time: " + timeRemaining + " seconds remaining");
    } else {
        // Calculate from end timestamp as fallback
        const currentTime = new Date().getTime();
        timeRemaining = Math.max(0, Math.floor((endTimeTimestamp - currentTime) / 1000));
        console.log("Using client time: " + timeRemaining + " seconds remaining");
    }
    
    // Safety check - if time calculation fails, default to 6 minutes
    if (isNaN(timeRemaining) || timeRemaining <= 0 || timeRemaining > 360) {
        timeRemaining = 360;
        console.log("Using default time: 6 minutes (360 seconds)");
    }
    
    // TIMER FUNCTION - updates the timer display and handles timeout
    function updateTimerDisplay() {
        // Calculate minutes and seconds
        const minutes = Math.floor(timeRemaining / 60);
        const seconds = timeRemaining % 60;
        
        // Format with leading zeros
        const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        
        // Update the DOM element with the new time
        timerElement.innerText = formattedTime;
        
        // Update visual styling based on time remaining
        if (timeRemaining <= 30) {
            // Red pulsing for last 30 seconds
            timerElement.className = 'text-2xl font-bold text-red-600 animate-pulse';
        } else if (timeRemaining <= 60) {
            // Yellow for last minute
            timerElement.className = 'text-2xl font-bold text-yellow-600';
        } else {
            // Normal styling
            timerElement.className = 'text-2xl font-bold text-gray-900';
        }
        
        // Debug logging
        console.log(`Timer updated: ${formattedTime} (${timeRemaining} seconds remaining)`);
    }
    
    // Start timer function - runs every second
    function startTimer() {
        // Update the display immediately once
        updateTimerDisplay();
        
        // Set up the interval to run every 1000ms (1 second)
        const timerInterval = setInterval(function() {
            // Decrease remaining time
            timeRemaining--;
            
            // Update the display with new time
            updateTimerDisplay();
            
            // Check if timer has expired
            if (timeRemaining <= 0) {
                // Clear the interval to stop the timer
                clearInterval(timerInterval);
                
                // Show message
                alert('Time is up! Your exam will be submitted automatically.');
                
                // Submit the form
                examForm.submit();
            }
        }, 1000);
        
        console.log("Timer started with " + timeRemaining + " seconds remaining");
        return timerInterval;
    }
    
    // Initialize the timer
    const timerInterval = startTimer();
    
    // Question navigation
    navButtons.forEach(function(button, index) {
        button.addEventListener('click', function() {
            const questionIndex = parseInt(this.getAttribute('data-question')) - 1;
            
            // Hide all questions
            document.querySelectorAll('.question-slide').forEach(function(slide) {
                slide.classList.add('hidden');
            });
            
            // Show the selected question
            document.getElementById('question-' + (questionIndex + 1)).classList.remove('hidden');
            
            // Update question counter and progress bar
            document.getElementById('current-question').textContent = (questionIndex + 1);
            document.getElementById('progress-bar').style.width = ((questionIndex + 1) / totalQuestions * 100) + '%';
            
            // Update active button styles
            navButtons.forEach(function(btn, idx) {
                if (idx === questionIndex) {
                    btn.classList.add('ring-2', 'ring-indigo-500');
                } else {
                    btn.classList.remove('ring-2', 'ring-indigo-500');
                }
            });
        });
    });
    
    // Handle answer selection
    document.querySelectorAll('.answer-radio').forEach(function(radio) {
        radio.addEventListener('change', function() {
            const questionId = this.getAttribute('data-question-id');
            const answerId = this.getAttribute('data-answer-id');
            
            // Update button style to show question is answered
            const questionIndex = Array.from(document.querySelectorAll('.question-slide')).findIndex(function(slide) {
                return slide.querySelector('[data-question-id="' + questionId + '"]');
            });
            
            if (questionIndex >= 0 && questionIndex < navButtons.length) {
                navButtons[questionIndex].classList.remove('bg-white', 'border', 'border-gray-300', 'text-gray-700');
                navButtons[questionIndex].classList.add('bg-indigo-600', 'text-white');
            }
            
            // Save answer via AJAX
            fetch('{{ route('candidate.qcm-exams.answer', $qcmExam) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    question_id: questionId,
                    answer_id: answerId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error saving answer:', data.error);
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    }
                } else {
                    console.log('Answer saved successfully');
                }
            })
            .catch(error => {
                console.error('Failed to save answer:', error);
            });
        });
    });
    
    // Handle exam submission
    const confirmationModal = document.getElementById('confirmation-modal');
    const cancelButton = document.getElementById('cancel-submit');
    const unansweredWarning = document.getElementById('unanswered-warning');
    
    // Cancel button for submission modal
    cancelButton.addEventListener('click', function() {
        confirmationModal.classList.add('hidden');
    });
    
    // Finish exam button
    document.querySelector('.finish-exam-btn').addEventListener('click', function() {
        // Check for unanswered questions
        const answeredCount = document.querySelectorAll('.question-nav-btn.bg-indigo-600').length;
        
        if (answeredCount < totalQuestions) {
            unansweredWarning.classList.remove('hidden');
            unansweredWarning.textContent = '{{ __('Warning: You have') }} ' + (totalQuestions - answeredCount) + ' {{ __('unanswered questions.') }}';
        } else {
            unansweredWarning.classList.add('hidden');
        }
        
        // Show the confirmation modal
        confirmationModal.classList.remove('hidden');
    });
});
</script>
@endpush
@endsection 