/**
 * QCM Exam JavaScript
 * Handles exam timer, question navigation, and answer submission
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const examForm = document.getElementById('exam-form');
    const timerDisplay = document.getElementById('timer-display');
    const questionContainers = document.querySelectorAll('.question-container');
    const questionTabs = document.querySelectorAll('.question-tab');
    const nextButtons = document.querySelectorAll('.next-question');
    const prevButtons = document.querySelectorAll('.prev-question');
    const submitButton = document.getElementById('submit-exam');
    const progressBar = document.getElementById('exam-progress-bar');
    
    // Exam data
    const examId = examForm ? examForm.dataset.examId : null;
    const examDuration = examForm ? parseInt(examForm.dataset.duration) : 0;
    const examStartTime = examForm ? parseInt(examForm.dataset.startTime) : 0;
    
    let currentQuestionIndex = 0;
    let answeredQuestions = new Set();
    let timer = null;
    
    /**
     * Initialize the exam interface
     */
    function initExam() {
        if (!examForm) return;
        
        // Show first question
        showQuestion(0);
        
        // Start timer
        startTimer();
        
        // Setup event listeners
        setupEventListeners();
        
        // Update progress bar initially
        updateProgressBar();
    }
    
    /**
     * Setup event listeners for the exam interface
     */
    function setupEventListeners() {
        // Question tabs
        questionTabs.forEach((tab, index) => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();
                showQuestion(index);
            });
        });
        
        // Next buttons
        nextButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (currentQuestionIndex < questionContainers.length - 1) {
                    showQuestion(currentQuestionIndex + 1);
                }
            });
        });
        
        // Previous buttons
        prevButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                if (currentQuestionIndex > 0) {
                    showQuestion(currentQuestionIndex - 1);
                }
            });
        });
        
        // Option selection
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const questionId = this.name.replace('question_', '');
                answeredQuestions.add(questionId);
                
                // Mark question as answered in the navigation
                const questionIndex = findQuestionIndexById(questionId);
                if (questionIndex !== -1) {
                    questionTabs[questionIndex].classList.add('answered');
                }
                
                // Update progress bar
                updateProgressBar();
                
                // Save answer via AJAX
                saveAnswer(questionId, this.value);
            });
        });
        
        // Submit button
        if (submitButton) {
            submitButton.addEventListener('click', function(e) {
                if (!confirmSubmission()) {
                    e.preventDefault();
                }
            });
        }
        
        // Mark questions that are already answered (page refresh case)
        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            const questionId = radio.name.replace('question_', '');
            answeredQuestions.add(questionId);
            
            const questionIndex = findQuestionIndexById(questionId);
            if (questionIndex !== -1) {
                questionTabs[questionIndex].classList.add('answered');
            }
        });
    }
    
    /**
     * Find question index by question ID
     */
    function findQuestionIndexById(questionId) {
        for (let i = 0; i < questionContainers.length; i++) {
            if (questionContainers[i].dataset.questionId === questionId) {
                return i;
            }
        }
        return -1;
    }
    
    /**
     * Show a specific question and hide others
     */
    function showQuestion(index) {
        // Hide all questions
        questionContainers.forEach(container => {
            container.classList.add('hidden');
        });
        
        // Remove active class from all tabs
        questionTabs.forEach(tab => {
            tab.classList.remove('active');
            tab.classList.remove('bg-indigo-600');
            tab.classList.remove('text-white');
            tab.classList.add('bg-gray-100');
            tab.classList.add('text-gray-700');
        });
        
        // Show the selected question
        questionContainers[index].classList.remove('hidden');
        
        // Mark the tab as active
        questionTabs[index].classList.add('active');
        questionTabs[index].classList.remove('bg-gray-100');
        questionTabs[index].classList.remove('text-gray-700');
        questionTabs[index].classList.add('bg-indigo-600');
        questionTabs[index].classList.add('text-white');
        
        // Update current question index
        currentQuestionIndex = index;
        
        // Update navigation buttons
        updateNavigationButtons();
    }
    
    /**
     * Update the next/prev navigation buttons
     */
    function updateNavigationButtons() {
        // Disable/enable previous buttons
        prevButtons.forEach(button => {
            button.disabled = currentQuestionIndex === 0;
            if (currentQuestionIndex === 0) {
                button.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
        
        // Disable/enable next buttons
        nextButtons.forEach(button => {
            button.disabled = currentQuestionIndex === questionContainers.length - 1;
            if (currentQuestionIndex === questionContainers.length - 1) {
                button.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
    }
    
    /**
     * Update the progress bar
     */
    function updateProgressBar() {
        if (progressBar) {
            const progress = (answeredQuestions.size / questionContainers.length) * 100;
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
        }
    }
    
    /**
     * Start the exam timer
     */
    function startTimer() {
        if (!timerDisplay || !examDuration || !examStartTime) return;
        
        const endTime = examStartTime + (examDuration * 60);
        
        timer = setInterval(() => {
            const now = Math.floor(Date.now() / 1000);
            const timeLeft = endTime - now;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                timerDisplay.innerHTML = '00:00';
                alert('Time is up! Your exam will be submitted automatically.');
                examForm.submit();
                return;
            }
            
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            // Format the time
            const formattedMinutes = minutes < 10 ? '0' + minutes : minutes;
            const formattedSeconds = seconds < 10 ? '0' + seconds : seconds;
            
            // Update the timer display
            timerDisplay.innerHTML = `${formattedMinutes}:${formattedSeconds}`;
            
            // Warning when less than 5 minutes left
            if (timeLeft < 300) {
                timerDisplay.classList.add('text-red-600');
                timerDisplay.classList.add('animate-pulse');
            }
        }, 1000);
    }
    
    /**
     * Save an answer via AJAX
     */
    function saveAnswer(questionId, answerId) {
        if (!examId) return;
        
        // Create a FormData object
        const formData = new FormData();
        formData.append('question_id', questionId);
        formData.append('answer_id', answerId);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
        
        // Send the AJAX request
        fetch(`/candidate/qcm-exams/${examId}/save-answer`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Answer saved successfully');
            } else {
                console.error('Error saving answer:', data.message);
            }
        })
        .catch(error => {
            console.error('Error saving answer:', error);
        });
    }
    
    /**
     * Confirm exam submission
     */
    function confirmSubmission() {
        const unansweredCount = questionContainers.length - answeredQuestions.size;
        
        if (unansweredCount > 0) {
            return confirm(`You have ${unansweredCount} unanswered question(s). Are you sure you want to submit the exam?`);
        }
        
        return confirm('Are you sure you want to submit the exam?');
    }
    
    // Initialize the exam
    initExam();
}); 