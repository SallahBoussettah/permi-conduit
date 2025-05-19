/**
 * QCM Questions JavaScript
 * Handles dynamic option addition/removal and image preview for QCM questions
 */

document.addEventListener('DOMContentLoaded', function() {
    // Question form elements
    const questionForm = document.getElementById('question-form');
    const addOptionBtn = document.getElementById('add-option');
    const optionsContainer = document.getElementById('options-container');
    const questionImageInput = document.getElementById('question_image');
    const questionImagePreview = document.getElementById('question-image-preview');
    const removeImageBtn = document.getElementById('remove-image');
    
    /**
     * Initialize the question form
     */
    function initQuestionForm() {
        if (!questionForm) return;
        
        // Setup event listeners
        setupEventListeners();
        
        // Initialize option counters
        updateOptionCounters();
        
        // Setup image preview if there's an existing image
        if (questionImagePreview && questionImagePreview.querySelector('img')) {
            showImagePreview();
        }
    }
    
    /**
     * Setup event listeners for the question form
     */
    function setupEventListeners() {
        // Add option button
        if (addOptionBtn) {
            addOptionBtn.addEventListener('click', function(e) {
                e.preventDefault();
                addOption();
            });
        }
        
        // Remove option buttons (initial)
        document.querySelectorAll('.remove-option').forEach(button => {
            button.addEventListener('click', handleRemoveOption);
        });
        
        // Correct answer radios (initial)
        document.querySelectorAll('input[name="correct_option"]').forEach(radio => {
            radio.addEventListener('change', updateCorrectOptionStyles);
        });
        
        // Image upload preview
        if (questionImageInput) {
            questionImageInput.addEventListener('change', handleImageUpload);
        }
        
        // Remove image button
        if (removeImageBtn) {
            removeImageBtn.addEventListener('click', function(e) {
                e.preventDefault();
                removeImage();
            });
        }
    }
    
    /**
     * Add a new option to the question
     */
    function addOption() {
        if (!optionsContainer) return;
        
        // Get the current number of options
        const optionCount = optionsContainer.querySelectorAll('.option-row').length;
        
        // Create a new option row
        const newOptionId = Date.now(); // Use timestamp to ensure unique IDs
        const newOption = document.createElement('div');
        newOption.className = 'option-row mb-4 flex items-center space-x-2';
        newOption.innerHTML = `
            <div class="flex-grow">
                <div class="flex items-center">
                    <span class="option-number mr-2 font-medium">${optionCount + 1}.</span>
                    <input type="text" name="options[${newOptionId}][text]" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Option text" required>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <label class="inline-flex items-center">
                    <input type="radio" name="correct_option" value="${newOptionId}" class="h-4 w-4 text-indigo-600 border-gray-300 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-700">Correct</span>
                </label>
                <button type="button" class="remove-option p-1 text-red-600 hover:text-red-800">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        `;
        
        // Add the new option to the container
        optionsContainer.appendChild(newOption);
        
        // Add event listener to the new remove button
        const removeBtn = newOption.querySelector('.remove-option');
        if (removeBtn) {
            removeBtn.addEventListener('click', handleRemoveOption);
        }
        
        // Add event listener to the new correct radio
        const correctRadio = newOption.querySelector('input[type="radio"]');
        if (correctRadio) {
            correctRadio.addEventListener('change', updateCorrectOptionStyles);
        }
        
        // Update option numbers
        updateOptionCounters();
        
        // Focus on the new input
        newOption.querySelector('input[type="text"]').focus();
    }
    
    /**
     * Handle remove option button click
     */
    function handleRemoveOption(e) {
        e.preventDefault();
        
        const optionRow = this.closest('.option-row');
        if (!optionRow) return;
        
        // Check if this is the last option
        const optionCount = optionsContainer.querySelectorAll('.option-row').length;
        if (optionCount <= 2) {
            alert('A question must have at least 2 options.');
            return;
        }
        
        // Check if this is the correct option
        const correctRadio = optionRow.querySelector('input[type="radio"]');
        if (correctRadio && correctRadio.checked) {
            alert('You cannot remove the correct option. Please select another option as correct first.');
            return;
        }
        
        // Remove the option row
        optionRow.remove();
        
        // Update option numbers
        updateOptionCounters();
    }
    
    /**
     * Update option numbers after adding/removing options
     */
    function updateOptionCounters() {
        if (!optionsContainer) return;
        
        const optionRows = optionsContainer.querySelectorAll('.option-row');
        optionRows.forEach((row, index) => {
            const numberSpan = row.querySelector('.option-number');
            if (numberSpan) {
                numberSpan.textContent = `${index + 1}.`;
            }
        });
    }
    
    /**
     * Update styles for correct option
     */
    function updateCorrectOptionStyles() {
        if (!optionsContainer) return;
        
        const optionRows = optionsContainer.querySelectorAll('.option-row');
        optionRows.forEach(row => {
            const radio = row.querySelector('input[type="radio"]');
            if (radio && radio.checked) {
                row.classList.add('bg-green-50', 'border-l-4', 'border-green-500', 'pl-2');
            } else {
                row.classList.remove('bg-green-50', 'border-l-4', 'border-green-500', 'pl-2');
            }
        });
    }
    
    /**
     * Handle image upload and preview
     */
    function handleImageUpload() {
        if (!questionImageInput || !questionImagePreview) return;
        
        const file = questionImageInput.files[0];
        if (!file) return;
        
        // Check file type
        if (!file.type.match('image.*')) {
            alert('Please select an image file.');
            questionImageInput.value = '';
            return;
        }
        
        // Check file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Image size should not exceed 2MB.');
            questionImageInput.value = '';
            return;
        }
        
        // Create file reader to preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            // Create or update image element
            let img = questionImagePreview.querySelector('img');
            if (!img) {
                img = document.createElement('img');
                img.className = 'max-h-48 rounded-md';
                questionImagePreview.appendChild(img);
            }
            
            // Set image source
            img.src = e.target.result;
            
            // Show preview container and remove button
            showImagePreview();
        };
        
        // Read the image file
        reader.readAsDataURL(file);
    }
    
    /**
     * Show image preview container and remove button
     */
    function showImagePreview() {
        if (!questionImagePreview || !removeImageBtn) return;
        
        questionImagePreview.classList.remove('hidden');
        removeImageBtn.classList.remove('hidden');
    }
    
    /**
     * Remove image and hide preview
     */
    function removeImage() {
        if (!questionImageInput || !questionImagePreview || !removeImageBtn) return;
        
        // Clear file input
        questionImageInput.value = '';
        
        // Hide preview container and remove button
        questionImagePreview.classList.add('hidden');
        removeImageBtn.classList.add('hidden');
        
        // Remove image element
        const img = questionImagePreview.querySelector('img');
        if (img) {
            img.remove();
        }
        
        // Add hidden input to mark image for deletion if editing
        if (questionForm.dataset.questionId) {
            const deleteImageInput = document.createElement('input');
            deleteImageInput.type = 'hidden';
            deleteImageInput.name = 'delete_image';
            deleteImageInput.value = '1';
            questionForm.appendChild(deleteImageInput);
        }
    }
    
    // Initialize the question form
    initQuestionForm();
    
    // Initialize correct option styles
    updateCorrectOptionStyles();
}); 