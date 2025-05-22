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
                    {{ __('Cet examen QCM comporte 10 questions à compléter en 6 minutes. Vous devez avoir au moins 6 réponses correctes pour passer.') }}
                </p>
            </div>
            <div class="flex items-center">
                <div class="px-4 py-2 bg-white shadow rounded-lg mr-4">
                    <div class="text-sm text-gray-500">{{ __('Temps restant') }}</div>
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
                                {{ __('Précédent') }}
                            </button>
                            
                            @if($index === count($questions) - 1)
                                <button type="button" 
                                    onclick="document.getElementById('confirmation-modal').classList.remove('hidden');"
                                    class="finish-exam-btn ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    {{ __('Terminer l\'examen') }}
                                </button>
                            @else
                                <button type="button"
                                    class="ml-3 inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                    onclick="document.getElementById('question-{{ $index + 1 }}').classList.add('hidden'); document.getElementById('question-{{ $index + 2 }}').classList.remove('hidden'); document.getElementById('current-question').textContent = '{{ $index + 2 }}'; document.getElementById('progress-bar').style.width = '{{ (($index + 2) / count($questions)) * 100 }}%';">
                                    {{ __('Suivant') }}
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
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">{{ __('Soumettre l\'examen') }}</h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            {{ __('Êtes-vous sûr de vouloir soumettre votre examen ? Vous ne pouvez pas modifier vos réponses après la soumission.') }}
                                        </p>
                                        <div id="unanswered-warning" class="mt-2 text-sm text-red-600 hidden">
                                            {{ __('Attention: Vous avez des questions non répondues.') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="button" 
                                onclick="document.getElementById('exam-form').submit();" 
                                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Soumettre') }}
                            </button>
                            <button type="button" id="cancel-submit" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                {{ __('Continuer l\'examen') }}
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
    
    // SUPER SIMPLE TIMER IMPLEMENTATION
    // This is a bare-bones implementation that will work in any browser
    if (timerElement) {
        console.log("Timer element found, starting simple timer...");
        
        // Get the end time from the element
        const endTimeTimestamp = parseInt(timerElement.getAttribute('data-end-time')) * 1000;
        
        // Create a very simple updating function
        function simpleTimerUpdate() {
            try {
                // Get current time and calculate difference
                const now = new Date().getTime();
                const diff = Math.max(0, endTimeTimestamp - now);
                
                // Format minutes and seconds
                const minutes = Math.floor(diff / 60000);
                const seconds = Math.floor((diff % 60000) / 1000);
                
                // Format the time string with leading zeros
                const timeString = 
                    (minutes < 10 ? "0" + minutes : minutes) + ":" + 
                    (seconds < 10 ? "0" + seconds : seconds);
                
                // Directly update the innerHTML
                timerElement.innerHTML = timeString;
                
                // Apply different styles based on time remaining
                if (diff <= 30000) { // Less than 30 seconds
                    timerElement.className = 'text-2xl font-bold text-red-600 animate-pulse';
                } else if (diff <= 60000) { // Less than 1 minute
                    timerElement.className = 'text-2xl font-bold text-yellow-600';
                } else {
                    timerElement.className = 'text-2xl font-bold text-gray-900';
                }
                
                // If time's up, submit the form
                if (diff <= 0) {
                    clearInterval(timerUpdateInterval);
                    alert('{{ __('Le temps est écoulé ! Votre examen sera soumis automatiquement.') }}');
                    examForm.submit();
                }
            } catch (error) {
                console.error("Error updating timer:", error);
            }
        }
        
        // Run immediately
        simpleTimerUpdate();
        
        // Then set up an interval to run EXACTLY every second
        const timerUpdateInterval = setInterval(simpleTimerUpdate, 1000);
        
        // Create debug button (in development only)
        if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
            const debugBtn = document.createElement('button');
            debugBtn.textContent = '{{ __('Déboguer le timer') }}';
            debugBtn.style.position = 'fixed';
            debugBtn.style.bottom = '10px';
            debugBtn.style.right = '10px';
            debugBtn.style.zIndex = '9999';
            debugBtn.style.padding = '5px 10px';
            debugBtn.style.background = '#f0f0f0';
            debugBtn.style.border = '1px solid #ccc';
            debugBtn.style.borderRadius = '4px';
            debugBtn.onclick = function() {
                console.log('Timer element:', timerElement);
                console.log('Current value:', timerElement.innerHTML);
                timerElement.style.border = '2px solid red';
                simpleTimerUpdate(); // Force an update
            };
            document.body.appendChild(debugBtn);
        }
    } else {
        console.error("Timer element not found!");
    }
    
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