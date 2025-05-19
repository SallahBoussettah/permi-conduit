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
                    <div id="timer" class="text-2xl font-bold text-gray-900" data-end-time="{{ $qcmExam->expires_at ? $qcmExam->expires_at->timestamp : now()->addMinutes(6)->timestamp }}">
                        06:00
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
document.addEventListener('DOMContentLoaded', function() {
    // Basic elements
    var timerElement = document.getElementById('timer');
    var examForm = document.getElementById('exam-form');
    var totalQuestions = {{ count($questions) }};
    var navButtons = document.querySelectorAll('.question-nav-btn');
    
    // Timer setup - get the actual server-provided end time
    var endTimeTimestamp = parseInt(timerElement.getAttribute('data-end-time')) * 1000; // convert to milliseconds
    var currentTime = new Date().getTime();
    var timeRemaining = Math.max(0, Math.floor((endTimeTimestamp - currentTime) / 1000)); // convert to seconds and ensure it's not negative
    
    console.log("End time: " + new Date(endTimeTimestamp).toLocaleTimeString());
    console.log("Current time: " + new Date(currentTime).toLocaleTimeString());
    console.log("Initial time remaining: " + timeRemaining + " seconds");
    
    // If the calculated time is invalid or too large, set to 6 minutes (360 seconds)
    if (isNaN(timeRemaining) || timeRemaining <= 0 || timeRemaining > 360) {
        timeRemaining = 360; // 6 minutes in seconds
        console.log("Using default 6 minute timer");
    } else {
        console.log("Using server-calculated remaining time: " + timeRemaining + " seconds");
    }
    
    // Question number buttons
    for (var i = 0; i < navButtons.length; i++) {
        navButtons[i].addEventListener('click', function() {
            var questionIndex = parseInt(this.getAttribute('data-question')) - 1;
            
            // Hide all questions
            var questions = document.querySelectorAll('.question-slide');
            for (var j = 0; j < questions.length; j++) {
                questions[j].classList.add('hidden');
            }
            
            // Show the clicked question
            document.getElementById('question-' + (questionIndex + 1)).classList.remove('hidden');
            
            // Update counter and progress
            document.getElementById('current-question').textContent = (questionIndex + 1);
            document.getElementById('progress-bar').style.width = ((questionIndex + 1) / totalQuestions * 100) + '%';
            
            // Update active button
            for (var k = 0; k < navButtons.length; k++) {
                if (k === questionIndex) {
                    navButtons[k].classList.add('ring-2', 'ring-indigo-500');
                } else {
                    navButtons[k].classList.remove('ring-2', 'ring-indigo-500');
                }
            }
        });
    }
    
    // Timer functionality
    function updateTimer() {
        // Check if we need to stop the timer
        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            timerElement.textContent = '00:00';
            
            // Show a brief time's up message
            alert('Time is up! Your exam will be submitted automatically.');
            
            // Submit the form
            examForm.submit();
            
            // Prevent further timer updates
            return false;
        }
        
        // Calculate minutes and seconds
        var minutes = Math.floor(timeRemaining / 60);
        var seconds = timeRemaining % 60;
        
        // Format with leading zeros
        var formattedMinutes = minutes.toString().padStart(2, '0');
        var formattedSeconds = seconds.toString().padStart(2, '0');
        
        // Update the display
        timerElement.textContent = formattedMinutes + ':' + formattedSeconds;
        
        // Update visual styling based on time remaining
        if (timeRemaining <= 30) {
            timerElement.classList.add('text-red-600', 'animate-pulse');
        } else if (timeRemaining <= 60) {
            timerElement.classList.add('text-red-600');
            timerElement.classList.remove('animate-pulse');
        } else if (timeRemaining <= 120) {
            timerElement.classList.add('text-yellow-600');
            timerElement.classList.remove('text-red-600', 'animate-pulse');
        }
        
        // Decrement time remaining
        timeRemaining--;
        
        return true;
    }
    
    // Initialize timer and start the countdown
    var timerInterval = setInterval(function() {
        if (!updateTimer()) {
            clearInterval(timerInterval);
        }
    }, 1000);
    
    // Run once immediately to set initial display
    updateTimer();
    
    // Add function to check unanswered questions when the modal is shown
    function checkUnansweredQuestions() {
        var unansweredWarning = document.getElementById('unanswered-warning');
        var answeredCount = document.querySelectorAll('.answer-radio:checked').length;
        var unansweredCount = totalQuestions - answeredCount;
        
        if (unansweredCount > 0) {
            unansweredWarning.textContent = "{{ __('Warning: You have') }} " + unansweredCount + " {{ __('unanswered questions.') }}";
            unansweredWarning.classList.remove('hidden');
        } else {
            unansweredWarning.classList.add('hidden');
        }
    }
    
    // Add MutationObserver to check when the confirmation modal becomes visible
    var confirmationModal = document.getElementById('confirmation-modal');
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.attributeName === 'class' && 
                !confirmationModal.classList.contains('hidden')) {
                checkUnansweredQuestions();
            }
        });
    });
    
    observer.observe(confirmationModal, { attributes: true });
    
    // Cancel submission
    document.getElementById('cancel-submit').addEventListener('click', function() {
        document.getElementById('confirmation-modal').classList.add('hidden');
    });
    
    // Save answers automatically when selected
    var radioButtons = document.querySelectorAll('.answer-radio');
    for (var i = 0; i < radioButtons.length; i++) {
        radioButtons[i].addEventListener('change', function() {
            var questionId = this.dataset.questionId;
            var answerId = this.dataset.answerId;
            
            // Find the question index and update the nav button
            var slides = document.querySelectorAll('.question-slide');
            var questionIndex = -1;
            
            for (var j = 0; j < slides.length; j++) {
                if (slides[j].querySelector('input[data-question-id="' + questionId + '"]')) {
                    questionIndex = j;
                    break;
                }
            }
            
            if (questionIndex !== -1) {
                var navButton = document.querySelector('.question-nav-btn[data-question="' + (questionIndex + 1) + '"]');
                if (navButton) {
                    navButton.classList.remove('bg-white', 'border', 'border-gray-300', 'text-gray-700');
                    navButton.classList.add('bg-indigo-600', 'text-white');
                }
            }
            
            // Save answer via AJAX
            console.log('Saving answer:', questionId, answerId);
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
                console.log('Answer saved:', data);
            })
            .catch(function(error) {
                console.error('Error saving answer:', error);
            });
        });
    }

    // Add event listener to form submit button
    document.getElementById('exam-form').addEventListener('submit', function() {
        console.log('Form submitted');
    });
});
</script>
@endpush
@endsection 